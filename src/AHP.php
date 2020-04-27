<?php

namespace Lattice\AHP;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class AHP{

	private $instance;

	private $candidates;
	private $criteria;
	private $pairwiseComparisons;

	private $candidatesPriorities;
	private $goalPriorities;
	private $finalPriorities;

	public function __construct(){
		$this->candidates = new ArrayCollection();
		$this->criteria = new ArrayCollection();
		$this->pairwiseComparisons = new ArrayCollection();
		$this->candidatesPriorities = new ArrayCollection();
		$this->finalPriorities = new ArrayCollection();
	}

	public function addCandidate(Candidate $candidate):self
	{
		if(!$this->candidates->contains($candidate)){
			$this->candidates->add($candidate);
		}
		return $this;
	}

	public function getCandidates()
	{
		return $this->candidates;
	}

	public function addCriterion(Criterion $criterion):self
	{
		if(!$this->criteria->contains($criterion)){
			$this->criteria->add($criterion);
		}
		return $this;
	}

	public function getCriterions()
	{
		return $this->criteria;
	}

	public function loadPairwiseComparisons(ArrayCollection $pairwiseComparisons):self
	{
		foreach ($pairwiseComparisons as $pairwiseComparison) {

		}

		return $this;
	}

	public function getPairwiseComparisons()
	{
		return $this->pairwiseComparisons;
	}

	public function calculatePriorities(Criterion $criterion = null):self
	{
		if(!is_null($criterion)){
			$criterion->calculatePriorities();
		}
		else{
			foreach ($this->criteria as $criterion) {
				$criterion->calculatePriorities();
			}
		}
		return $this;
	}

	public function getPriorities(){
		// reset
		$candidatesPriorities = new ArrayCollection();
		foreach ($this->criteria as $criterion) {
			if($criterion->getType() != 'goal')
				$candidatesPriorities[$criterion->getName()] = $criterion->getPriorities();
		}
		$this->candidatesPriorities = $candidatesPriorities;
		return $this->candidatesPriorities;
	}

	public function getGoal():Criterion
	{
		$filter = Criteria::create()->where(Criteria::expr()->eq("type", "goal"));
		$goalCriterion = $this->criteria->matching($filter)->first();
		if(is_null($goalCriterion)){
			// nothing defined, we create equal weights
			$goalCriterion = new Criterion('goal');
			$goalCriterion->setType('goal');
			// create pairwiseComparison for all possible couples
		}
		return $goalCriterion;
	}

	public function getFinalPriorities()
	{
		if($this->candidatesPriorities->isEmpty()){
			$this->getPriorities();
		}
		// goal priorities
		$goalCriterion = $this->getGoal();
		$this->goalPriorities = $goalCriterion->getPriorities();
		$tmp = [];
		$tmp['colnames'] = $this->goalPriorities['headers'];
		$tmp['rownames'] = $this->candidatesPriorities[$tmp['colnames'][0]]['headers'];
		$tmp['values'] = [];
		foreach ($this->goalPriorities['values'] as $goalPrioritiesKey => $goalPrioritiesValue) {
			$goalPrioritiesName = $this->goalPriorities['headers'][$goalPrioritiesKey];
			$candidatesPrioritiesPerCriteria = $this->candidatesPriorities[$goalPrioritiesName]['values'];
			$tmp['values'][$goalPrioritiesKey] = array_map(function($item) use ($goalPrioritiesValue) { return $goalPrioritiesValue * $item ; }, $candidatesPrioritiesPerCriteria);
		}
		// transpose for summing
		$finalPrioritiesMatrix = array_map(null, ...$tmp['values']);
		$finalPriorities = array_map('array_sum',$finalPrioritiesMatrix);
		// a bit of tidying up
		$this->finalPriorities = array_combine($tmp['rownames'], $finalPriorities);
		dump($this->finalPriorities);
	}


}