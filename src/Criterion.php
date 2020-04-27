<?php

namespace Lattice\AHP;
use Doctrine\Common\Collections\ArrayCollection;

class Criterion{
	
	private $name;
	private $type = 'node';
	private $pairwiseComparisons;
	private $nbCandidates;
	private $matrix;
	private $priorities;
	private $applyTransformation = TRUE;
	private $transformationFunctions = [['original'=>'log','inverse'=>'exp']];
	private $nbIterations = 20;

	public function __construct($name){
		$this->name = $name;
		$this->pairwiseComparisons = new ArrayCollection();
		$this->matrix = new ArrayCollection();
		$this->priorities = new ArrayCollection();
	}

	public function setType($type):self
	{
		$this->type = $type;

		return $this;
	}

	public function getType(){
		return $this->type;
	}

	public function getName(){
		return $this->name;
	}

	public function addPairwiseComparison(PairwiseComparison $pairwiseComparison):self
	{
		if(!$this->pairwiseComparisons->contains($pairwiseComparison)){
			$this->pairwiseComparisons->add($pairwiseComparison);
			$pairwiseComparison->setCriterion($this);
		}

		return $this;
	}

	public function getMatrixHeaders(){
		$names = new ArrayCollection();
		foreach ($this->pairwiseComparisons as $pC) {
			if(!$names->contains($pC->getCandidate1()->getName())){
				$names[] = $pC->getCandidate1()->getName();
			}
			if(!$names->contains($pC->getCandidate2()->getName())){
				$names[] = $pC->getCandidate2()->getName();
			}
		}
		return $names;
	}

	public function buildMatrix():self
	{
		if(!$this->pairwiseComparisons->isEmpty()){
			// reset
			$this->matrix = [];
			$this->matrix['headers'] = $this->getMatrixHeaders()->toArray();
			$this->nbCandidates = count($this->matrix['headers']);
			$tmp = array_fill(0, $this->nbCandidates, array_fill(0, $this->nbCandidates, 1));
			foreach ($this->pairwiseComparisons as $pairwiseComparison) {
				$positionCandidate1 = array_search($pairwiseComparison->getCandidate1()->getName(), $this->matrix['headers']);
				$positionCandidate2 = array_search($pairwiseComparison->getCandidate2()->getName(), $this->matrix['headers']);
				$tmp[$positionCandidate1][$positionCandidate2] = $pairwiseComparison->getScoreCandidate2()/$pairwiseComparison->getScoreCandidate1();
				$tmp[$positionCandidate2][$positionCandidate1] = $pairwiseComparison->getScoreCandidate1()/$pairwiseComparison->getScoreCandidate2();
			}
			$this->matrix['values'] = $tmp;
			if($this->applyTransformation){
				foreach ($this->transformationFunctions as $function) {
					$tfValues = array_map(function($tab)use($function) { return array_map($function['original'],$tab); }, $this->matrix['values']);
					$this->matrix[$function['original']] = $tfValues;
				}
			}
		}
		else{
			throw new Exception("PairwiseComparisons empty", 1);
		}
		return $this;
	}

	public function getMatrix(){
		if($this->matrix->isEmpty()){
			$this->buildMatrix();
		}
		return $this->matrix;
	}

	public function displayMatrix(){
		if($this->matrix->isEmpty()){
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
		if($this->matrix->isEmpty()){
			$this->buildMatrix();
		}
		// reset
		$this->priorities = [];
		$this->priorities['headers'] = $this->matrix['headers'];
		// which matrix to use
		$indexMatrix = $this->applyTransformation ? $this->transformationFunctions[0]['original'] : 'values';
		// transpose for summing
		$tmp = array_map(null, ...$this->matrix[$indexMatrix]);
		$nbElements = $this->nbCandidates;
		$sum = array_map('array_sum', $tmp);
		// inverse function
		$inverseFunction = $this->applyTransformation ? $this->transformationFunctions[0]['inverse'] : function($x){return $x;};
		// vector
		$vector = array_map($inverseFunction, array_map(function($value)use($nbElements) { return $value / $nbElements ;}, $sum ) );
		//
		$sumVector = array_sum($vector);
		//
		$this->priorities['values'] = array_map(function($value)use($sumVector) { return $value / $sumVector; } , $vector);
		return $this;
	}

	public function getPriorities(){
		if($this->priorities->isEmpty()){
			$this->calculatePriorities();
		}
		return $this->priorities;
	}

	public function deriveCandidate():Candidate
	{
		$candidate = new Candidate(['name'=>$this->name,'type'=>'criterion']);
		return $candidate;
	}
}