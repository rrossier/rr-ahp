<?php

namespace Lattice\AHP;

class Criterion{
	
	private $name;
	private $type = 'node';
	private $pairwiseComparisons;
	private $nbCandidates;
	private $matrix;
	private $priorities;
	private $consistencyRatio;
	private $applyTransformation = TRUE;
	private $transformationFunctions = [['original'=>'log','inverse'=>'exp']];
	private $weights;

	public function __construct($name)
	{
		$this->name = $name;
		$this->pairwiseComparisons = [];
		$this->matrix = [];
		$this->priorities = [];
	}

	public function setType($type):self
	{
		$this->type = $type;

		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function isGoal(){
		return $this->type == 'goal';
	}

	public function getName()
	{
		return $this->name;
	}

	public function getNbCandidates()
	{
		if(!isset($this->nbCandidates)){
			$this->nbCandidates = count($this->getMatrixHeaders());
		}
		return $this->nbCandidates;
	}

	public function applyTransformation()
	{
		return $this->applyTransformation;
	}

	public function getTransformationFunction($i = 0)
	{
		return $this->transformationFunctions[$i];
	}

	public function setWeights($weights):self
	{
		$this->weights = $weights;

		return $this;
	}

	public function getWeights()
	{
		if(!isset($this->weights)){
			$this->weights = array_fill(0, $this->getNbCandidates(), 1);
		}
		return $this->weights;
	}

	public function getWeight($i = 0)
	{
		return $this->weights[$i];
	}

	public function getTotalWeights()
	{
		return array_sum($this->getWeights());
	}

	public function addPairwiseComparison(PairwiseComparison $pairwiseComparison):self
	{
		if(!in_array($pairwiseComparison, $this->pairwiseComparisons)){
			$this->pairwiseComparisons[] = $pairwiseComparison;
			$pairwiseComparison->setCriterion($this);
		}

		return $this;
	}

	public function getMatrixHeaders()
	{
		$names = [];
		foreach ($this->pairwiseComparisons as $pC) {
			if(!in_array($pC->getCandidate1()->getName(),$names)){
				$names[] = $pC->getCandidate1()->getName();
			}
			if(!in_array($pC->getCandidate2()->getName(),$names)){
				$names[] = $pC->getCandidate2()->getName();
			}
		}
		sort($names);
		return $names;
	}

	public function buildMatrix():self
	{
		if(!empty($this->pairwiseComparisons)){
			// reset
			$this->matrix = [];
			$this->matrix['headers'] = $this->getMatrixHeaders();
			$this->nbCandidates = $this->getNbCandidates();
			$tmp = array_fill(0, $this->nbCandidates, array_fill(0, $this->nbCandidates, 1));
			foreach ($this->pairwiseComparisons as $pairwiseComparison) {
				// make sure that we always organise the candidates in the same order
				$positionCandidate1 = array_search($pairwiseComparison->getCandidate1()->getName(), $this->matrix['headers']);
				$positionCandidate2 = array_search($pairwiseComparison->getCandidate2()->getName(), $this->matrix['headers']);
				$tmp[$positionCandidate1][$positionCandidate2] = $pairwiseComparison->getScoreCandidate2()/$pairwiseComparison->getScoreCandidate1();
				$tmp[$positionCandidate2][$positionCandidate1] = $pairwiseComparison->getScoreCandidate1()/$pairwiseComparison->getScoreCandidate2();
			}
			$this->matrix['values'] = $tmp;
			if($this->applyTransformation()){
				foreach ($this->transformationFunctions as $function) {
					$tfValues = array_map(function($tab)use($function) { return array_map($function['original'],$tab); }, $this->matrix['values']);
					$this->matrix[$function['original']] = $tfValues;
				}
			}
		}
		else{
			throw new \Exception("PairwiseComparisons empty", 1);
		}
		return $this;
	}

	public function getMatrix()
	{
		if(empty($this->matrix)){
			$this->buildMatrix();
		}
		return $this->matrix;
	}

	public function getTransformedMatrix()
	{
		$matrix = $this->getMatrix();
		$indexMatrix = $this->applyTransformation ? $this->getTransformationFunction()['original'] : 'values';
		return $matrix[$indexMatrix];
	}

	public function displayMatrix()
	{
		if(empty($this->matrix)){
			$this->buildMatrix();
		}
		// transpose the array
		$tmp = array_map(null, ...$this->matrix['values']);
		$str = "<table style='border:1px black;'><tr><th>".
				implode("</th><th>", $this->matrix['headers'])."</th></tr><tr><td>".
				implode("</tr><tr><td>",array_map(function($row) { return implode("</td><td>", array_map(function($item) { return number_format($item,2) ; },$row) );}, $tmp)).
				"</td></tr></table>";
		echo($str);
	}

	public function calculatePriorities():self
	{
		if(empty($this->matrix)){
			$this->buildMatrix();
		}
		// reset
		$this->priorities = [];
		$this->priorities['headers'] = $this->matrix['headers'];
		// which matrix to use
		$indexMatrix = $this->applyTransformation ? $this->getTransformationFunction()['original'] : 'values';
		// transpose for summing
		$tmp = array_map(null, ...$this->matrix[$indexMatrix]);
		$nbElements = $this->nbCandidates;
		$sum = array_map('array_sum', $tmp);
		// inverse function
		$inverseFunction = $this->applyTransformation ? $this->getTransformationFunction()['inverse'] : function($x){return $x;};
		// vector
		$vector = array_map($inverseFunction, array_map(function($value)use($nbElements) { return $value / $nbElements ;}, $sum ) );
		//
		$sumVector = array_sum($vector);
		//
		$this->priorities['values'] = array_map(function($value)use($sumVector) { return $value / $sumVector; } , $vector);
		return $this;
	}

	public function getPriorities()
	{
		if(empty($this->priorities)){
			$this->calculatePriorities();
		}
		return $this->priorities;
	}

	public function getLambda()
	{
		if(empty($this->matrix)){
			$this->buildMatrix();
		}
		$colSums = array_map('array_sum', $this->matrix['values']);
		$priorities = $this->getPriorities()['values'];
		$lambda = 0;
		for($i = 0; $i < count($colSums); $i++){
			$lambda += $colSums[$i] * $priorities[$i];
		}
		return $lambda;
	}

	public function getConsistencyRatio()
	{
		//Alonso/Lamata linear fit : Consistency in the analytic hierarchy process: a new approach
		$al1 = 2.7699;
		$al2 = 4.3513;
		$lambda = $this->getLambda();
		$n = $this->nbCandidates;
		$consistencyRatio = ($lambda - $n)/($al1 * $n - $al2 - $n );
		$this->consistencyRatio = $consistencyRatio;
		return $this->consistencyRatio;
	}

	public function deriveCandidate():Candidate
	{
		$candidate = new Candidate(['name'=>$this->name,'type'=>'criterion']);
		return $candidate;
	}
}