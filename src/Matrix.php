<?php

namespace Lattice\AHP;

class Matrix{
	
	private $values;
	private $nRows;
	private $nCols;
	private $eigenvector;
	private $eigenvalue;

	private $estimationThreshold = 1e-12;
	private $eigenvectorError;

	public function __construct($values = null, $type = 'vertical')
	{
		if(!is_null($values)){
			// if $values is one dimensional, check $type
			if(!is_array($values[0])){
				if($type === 'vertical'){
					$values = [ $values ];
				}
				elseif($type === 'horizontal'){
					$values = array_chunk($values, 1);
				}
				else{
					throw new \Exception("Unknown type, either horizontal or vertical (default).", 1);
				}
			}
			$this->values = $values;
			$this->calculateDimensions();
		}
	}

	public function getNbCols()
	{
		return $this->nCols;
	}

	public function getNbRows()
	{
		return $this->nRows;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function getKeys()
	{
		$keys = call_user_func_array('array_intersect', array_map('array_keys', $this->values) );
		return $keys;
	}

	public function calculateDimensions():self
	{
		$this->nCols = count($this->values);
		$this->nRows = max(array_map('count', $this->values));

		return $this;
	}

	public function dim($nRows, $nCols, $fill = 0):self
	{
		$this->values = array_fill(0, $nCols, array_fill(0, $nRows, $fill));
		$this->calculateDimensions();

		return $this;
	}

	/**
	 * Transpose a matrix
	 * 
	 * @param Boolean $overwrite
	 * 
	 * @return Matrix
	 */
	public function transpose($overwrite = TRUE):Matrix
	{
		if($overwrite){
			// check dimensions
			$this->calculateDimensions();
			if($this->nCols == 1){
				/*
				 * vertical vector: [ [17, 3, 42] ]
				 * result should be horizontal vector: [ [17], [3], [42] ]
				 */
				$this->values = array_chunk($this->values[0], 1);
			}
			else{
				if($this->nRows == 1){
					/*
					 * horizontal vector: [ [17], [3], [42] ]
					 * result should be vertical vector: [ [17, 3, 42] ]
					 */
					$this->values = [ array_map('array_shift', $this->values) ];
				}
				else{
					/*
					 * 2-d matrix
					 * [ [17, 3, 42, 5], [19, 4, 8, 25], [7, 31, 11, 22] ]
					 *  =>
					 * [ [17, 19, 7], [3, 4, 31], [42, 8, 11], [5, 25, 22] ]
					 */
					$this->values = array_map(null, ...$this->values);
				}
			}
			// recalculate dimensions since they probably have changed
			$this->calculateDimensions();
			return $this;
		}
		else{
			$B = clone $this;
			return $B->transpose(!$overwrite);
		}
	}

	/**
	 * Sums each row and returns the vector of sums
	 *
	 * @return Matrix : vertical vector
	 */
	public function sumPerRow(): Matrix
	{
		$tmp = $this->transpose(FALSE);
		$sum = [ array_map('array_sum', $tmp->getValues()) ];
		return new Matrix($sum);
	}

	/**
	 * Sums each column and returns the vector of sums
	 *
	 * @return Matrix : horizontal vector
	 */
	public function sumPerColumn(): Matrix
	{
		$sum = [ array_map('array_sum', $this->values) ];
		$tmp = new Matrix($sum);
		return $tmp->transpose();
	}

	/**
	 * Performs the matrix multiplication on two Matrix objects
	 *
	 * @param Matrix $B
	 *
	 * @return Matrix C = AB with A being the current Matrix
	 *
	 * @throws exception
	 */
	public function matrixProduct(Matrix $B):Matrix
	{
		$AnRows = $this->nRows;
		$AnCols = $this->nCols;
		$Avalues = $this->values;
		$BnRows = $B->getNbRows();
		$BnCols = $B->getNbCols();
		$Bvalues = $B->getValues();
		if($AnCols == $BnRows){
			$tmp = [];
			for($j = 0; $j < $BnCols; $j++){
				$tmp[$j] = [];
				for($i = 0; $i < $AnRows; $i++){
					$tmp[$j][$i] = 0;
					for($k = 0; $k < $AnCols; $k++){
						$tmp[$j][$i] += $Avalues[$k][$i] * $Bvalues[$j][$k] ;
					}
				}
			}
			$C = new Matrix($tmp);
			return $C;
		}
		else{
			throw new \Exception("Dimensions mismatch", 1);
		}
	}

	/**
	 * Sums two Matrix objects
	 * 
	 * @param Matrix $B
	 *
	 * @return Matrix C = A + B with A being the current Matrix
	 *
	 * @throws exception
	 */
	public function matrixSum(Matrix $B):Matrix
	{
		$AnRows = $this->nRows;
		$AnCols = $this->nCols;
		$BnRows = $B->getNbRows();
		$BnCols = $B->getNbCols();
		if($AnCols == $BnCols & $AnRows == $BnRows){
			$Avalues = $this->values;
			$Bvalues = $B->getValues();
			$tmp = [];
			for($j = 0; $j < $this->nCols; $j++){
				$tmp[$j] = [];
				for($i = 0; $i < $this->nRows; $i++){
					$tmp[$j][$i] = $Avalues[$j][$i] + $Bvalues[$j][$i];
				}
			}
			$C = new Matrix($tmp);
			return $C;
		}
		else{
			throw new \Exception("Dimensions mismatch", 1);
		}
	}

	/**
	 * Applies function $function on each element
	 * If overwrite then replace elements in A otherwise keep intact
	 * 
	 * @param Closure $function
	 * @param Boolean $overwrite
	 * @param Array $userdata
	 * 
	 * @return Matrix
	 */
	public function applyFunction($function, $overwrite = TRUE, ...$userdata):Matrix
	{
		if($overwrite){
			if($function instanceOf \Closure){
				array_walk_recursive($this->values, $function, ...$userdata);
			}
			else{
				// create the callable directly from the function
				// function(&$value){ $value = exp($value); }
				$callable = function(&$value) use($function) { $value = $function($value); } ;
				array_walk_recursive($this->values, $callable);
			}
			return $this;

		}else{
			$B = clone $this;
			$B->applyFunction($function, !$overwrite, $userdata);

			return $B;
		}
	}

	/**
	 * Multiplies each element by lambda
	 * If overwrite then replace elements in A otherwise keep intact
	 *
	 * @param Float $lambda
	 * @param Boolean $overwrite
	 *
	 * @return B = lambda * A with A being the current Matrix
	 */
	public function scalarProduct($lambda, $overwrite = FALSE):Matrix
	{
		return $this->applyFunction(function(&$value) use ($lambda) { $value *= $lambda; }, $overwrite, $lambda);
	}

	/**
	 * Add lambda to each element
	 * If overwrite then replace elements in A otherwise keep intact
	 *
	 * @param Float $lambda
	 * @param Boolean $overwrite
	 *
	 *	@return Matrix B = lambda + A with A being the current Matrix
	 */
	public function scalarSum($lambda, $overwrite = FALSE):Matrix
	{
		return $this->applyFunction(function(&$value) use ($lambda) { $value += $lambda; }, $overwrite, $lambda);
	}

	/**
	 * Calculate the squared differences between two identical matrices
	 *
	 * @param Matrix B
	 *
	 * @return Matrix C where C_i_j = ( A_i_j - B_i_j )^2
	 */
	public function squaredDifferences($B):Matrix
	{
		$Bbis = $B->scalarProduct(-1);
		$C = $this->matrixSum($Bbis);
		$C->applyFunction(function(&$value) { $value = pow($value, 2); }, TRUE);

		return $C;
	}

	/**
	 * Calculate the product of all elements
	 *
	 * @return float the product between all elements
	 */
	public function product()
	{
		$carry = 1;
		$this->applyFunction(function($value) use (&$carry){ $carry *= $value;}, FALSE, $carry);
		return $carry;
	}

	/**
	 * Calculate the sum of all elements
	 *
	 * @return float the sum of all elements
	 */
	public function sum()
	{
		$carry = 0;
		$this->applyFunction(function($value) use (&$carry){ $carry += $value;}, FALSE, $carry);
		return $carry;
	}

	/**
	 * Divide each element by the max of all elements
	 *
	 * @param $overwrite Boolean
	 *
	 * @return Matrix
	 */
	public function scale($overwrite = FALSE):Matrix
	{
		$max = null;
		$this->applyFunction(function($value) use (&$max){ $max = max($max,$value) ;}, FALSE, $max);
		$B = $this->scalarProduct(1/$max, $overwrite);

		return ($overwrite ? $this : $B);
	}

	/**
	 * Divide each element by the sum of all elements
	 *
	 * @param $overwrite Boolean
	 *
	 * @return Matrix
	 */
	public function normalize($overwrite = FALSE):Matrix
	{
		$sum = $this->sum();
		$B = $this->scalarProduct(1/$sum, $overwrite);

		return ($overwrite ? $this : $B);
	}

	/**
	 * Calculate the Eigenvector of a Matrix
	 *
	 * @return Matrix : vertical vector
	 */
	public function calculateEigenvector($nbIterations = 20):Matrix
	{
		$raw = $scaled = $normalized = $errors = [];
		for($i = 0; $i < $nbIterations; $i++){
			$raw[$i] = ($i==0) ? $this->sumPerRow()->scalarProduct(1/$this->nCols, TRUE) : $this->matrixProduct($scaled[$i-1]);
			$scaled[$i] = $raw[$i]->scale();
			$normalized[$i] = $scaled[$i]->normalize();
			$errors[$i] = ($i > 0) ? $normalized[$i]->squaredDifferences($normalized[$i-1]) : new Matrix([1]);
			$error = $errors[$i]->sum();

			if($error < $this->estimationThreshold){
				$this->eigenvectorError = $error;
				break;
			}
		}
		$position = min($i, $nbIterations-1);
		$this->eigenvector = $normalized[$position];

		return $this->eigenvector;
	}

	/**
	 * Calculate the Eigenvalue of a Matrix
	 *
	 * @return float
	 */
	public function calculateEigenvalue()
	{
		if(!isset($this->eigenvector))
			$this->calculateEigenvector();

		$this->eigenvalue = $this->sumPerColumn()->matrixProduct($this->eigenvector)->getValues()[0][0];

		return $this->eigenvalue;
	}

	/**
	 * Display a html table representing the Matrix
	 *
	 * @return void
	 */
	public function display()
	{
		$values = $this->transpose(FALSE)->getValues();
		$str = '<table border="1">';
		foreach ($values as $key => $col) {
			$str .= '<tr>';
			foreach ($col as $key => $cell) {
				$str .= '<td>'.$cell.'</td>';
			}
			$str .= '</tr>';
		}
		$str .= '</table>';
		echo($str);
	}

	/**
	 * Derive the identity matrix based on the current matrix size
	 *
	 * @return Matrix
	 */
	public function deriveIdentityMatrix():Matrix
	{
		if($this->nCols !== $this->nRows){
			throw new \Exception("Not a square matrix", 1);
		}
		$tmp = [];
		for($i = 0; $i < $this->nRows; $i++){
			for($j = 0; $j < $this->nCols; $j++){
				$tmp[$i][$j] = +($i == $j);
			}
		}
		$identity = new Matrix($tmp);

		return $identity;
	}

	/**
	 * Check if the calculation of eigenvector and eigenvalue yield an adequate result
	 * (A - eigenvalue*I)eigenvector = [0]
	 *
	 * @return Boolean
	 */
	public function checkEigenvector()
	{
		if(!isset($this->eigenvalue))
			$this->calculateEigenvalue();

		$identityMatrix = $this->deriveIdentityMatrix();
		$B = $this->matrixSum($identityMatrix->scalarProduct(-$this->eigenvalue))->matrixProduct($this->eigenvector);

		return ($B->sum() < $this->estimationThreshold);
	}

	/**
	 * Extract the values to return an array
	 *
	 *
	 * @return Array
	 */
	public function extract()
	{
		if($this->nCols == 1){
			// vertical vector
			return $this->values[0];
		}
		else if($this->nRows == 1){
			// horizontal vector
			return $this->transpose(FALSE)->extract();
		}
		else{
			return $this->values;
		}
	}
}