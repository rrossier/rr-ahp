<?php

namespace Lattice\AHP;

class PairwiseComparison{
	
	private $candidate1;
	private $candidate2;
	private $criterion = null;
	private $scoreCandidate1 = 1;
	private $scoreCandidate2 = 1;

	public function __construct($array = null){
		if(!is_null($array)){
			foreach ($array as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	public function setCandidate1(Candidate $candidate):self
	{
		$this->candidate1 = $candidate;
		return $this;
	}

	public function getCandidate1(){
		return $this->candidate1;
	}

	public function setCandidate2(Candidate $candidate):self
	{
		$this->candidate2 = $candidate;
		return $this;
	}

	public function getCandidate2(){
		return $this->candidate2;
	}

	public function setCriterion(Criterion $criterion):self
	{
		$this->criterion = $criterion;
		$criterion->addPairwiseComparison($this);
		return $this;
	}

	public function getCriterion(){
		return $this->criterion;
	}

	public function setScoreCandidate1($score):self
	{
		$this->scoreCandidate1 = $score;
	}

	public function getScoreCandidate1(){
		return $this->scoreCandidate1;
	}

	public function setScoreCandidate2($score):self
	{
		$this->scoreCandidate2 = $score;
	}

	public function getScoreCandidate2(){
		return $this->scoreCandidate2;
	}

	public function getCandidatesNames(){
		return [$this->candidate1->getName(),$this->candidate2->getName()];
	}
}