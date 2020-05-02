<?php

namespace Lattice\AHP;

class Criterion extends Node{
	
	private $childrenCriteria;
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
		$this->type = 'criteria';
		$this->childrenCriteria = [];
		$this->pairwiseComparisons = [];
		$this->matrix = [];
		$this->priorities = [];
	}

	public function isGoal(){
		return $this->type == 'goal';
	}

	public function addCriterion(Criterion $criterion):self
	{
		if(!in_array($criterion,$this->childrenCriteria, TRUE)){
				$this->childrenCriteria[] = $criterion;	
		}
		return $this;
	}

	public function getCriteria()
	{
		return $this->childrenCriteria;
	}

	public function getCriterion($name)
	{
		foreach ($this->childrenCriteria as $criterion) {
			if($criterion->getName() == $name){
				return $criterion;
			}
		}
		return NULL;
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

	public function addPairwiseComparison(PairwiseComparison $pairwiseComparison):self
	{
		if(!in_array($pairwiseComparison, $this->pairwiseComparisons, TRUE)){
			$this->pairwiseComparisons[] = $pairwiseComparison;
			$pairwiseComparison->setCriterion($this);
		}

		return $this;
	}

	public function generateCriteriaPairwiseComparison(){
		if(empty($this->childrenCriteria))
			throw new \Exception("Cannot generate pairwise comparisons without any criteria", 1);
			
		$i = $j = 0;
		foreach ($this->childrenCriteria as $name1 => $criterion1) {
			$i++;
			$j = 0;
			foreach ($this->childrenCriteria as $name2 => $criterion2) {
				$j++;
				if($i < $j){
					$pCArray = ['candidate1' => $criterion1, 'candidate2' => $criterion2, 'scoreCandidate1' => 1];
					$pairwiseComparison = new PairwiseComparison($pCArray);
					$this->addPairwiseComparison($pairwiseComparison);
				}
			}
		}
		return $this;
	}

	public function getMatrixHeaders()
	{
		$names = [];
		foreach ($this->pairwiseComparisons as $pC) {
			if(!in_array($pC->getCandidate1()->getName(),$names, TRUE)){
				$names[] = $pC->getCandidate1()->getName();
			}
			if(!in_array($pC->getCandidate2()->getName(),$names, TRUE)){
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
			$this->matrix['values'] = new Matrix($tmp);
			if($this->applyTransformation()){
				foreach ($this->transformationFunctions as $function) {
					$this->matrix[$function['original']] = $this->matrix['values']->applyFunction($function['original'], FALSE);
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
		$this->matrix['values']->display();
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
		// inverse function
		$inverseFunction = $this->applyTransformation ? $this->getTransformationFunction()['inverse'] : function($x){return $x;};
		$this->priorities['values'] = $this->matrix[$indexMatrix]->applyFunction($inverseFunction, FALSE)->calculateEigenvector();
		return $this;

		// transpose for summing
		$tmp = array_map(null, ...$this->matrix[$indexMatrix]);
		$nbElements = $this->nbCandidates;
		$sum = array_map('array_sum', $tmp);
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

		$prioritiesVector = array_combine($this->priorities['headers'], $this->priorities['values']->getValues()[0]);

		if(!empty($this->childrenCriteria)){
			$priorities[0] = $prioritiesVector;
			foreach($this->childrenCriteria as $childrenCriterion){
				$priorities[$childrenCriterion->getName()] = $childrenCriterion->getPriorities();
			}
			return $priorities;
		}
		return $prioritiesVector;
	}

	public function getLambda()
	{
		if(empty($this->matrix)){
			$this->buildMatrix();
		}
		$indexMatrix = $this->applyTransformation ? $this->getTransformationFunction()['original'] : 'values';
		// inverse function
		$inverseFunction = $this->applyTransformation ? $this->getTransformationFunction()['inverse'] : function($x){return $x;};
		$lambda = $this->matrix[$indexMatrix]->applyFunction($inverseFunction, FALSE)->calculateEigenvalue();
		
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
}