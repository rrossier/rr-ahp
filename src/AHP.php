<?php

namespace Lattice\AHP;
use Lattice\AHP\Candidate;

class AHP{

	private $candidates;
	private $criteria;
	private $goalCriterion;
	private $pairwiseComparisons;

	private $candidatesPriorities;
	private $goalPriorities;
	private $finalPriorities;

	private $consolidatedMatrix;
	private $nbIterations = 20;

	public function __construct(){
		$this->candidates = [];
		$this->criteria = [];
		$this->pairwiseComparisons = [];
		$this->candidatesPriorities = [];
		$this->finalPriorities = [];
	}

	public function addCandidate(Candidate $candidate):self
	{
		if(!in_array($candidate,$this->candidates)){
			$this->candidates[$candidate->getName()] = $candidate;
		}
		return $this;
	}

	public function getCandidates()
	{
		return $this->candidates;
	}

	public function addCriterion(Criterion $criterion):self
	{
		if(!in_array($criterion,$this->criteria)){
			if($criterion->getType() == 'goal'){
				$this->goalCriterion = $criterion;
			}else{
				$this->criteria[] = $criterion;	
			}
			
		}
		return $this;
	}

	public function getCriteria()
	{
		return $this->criteria;
	}

	public function generateCriteria():self
	{
		$minScale = 1;
		$maxScale = 9;
		if(!isset($this->candidates)){
			throw new \Exception("No candidates given", 1);
		}
		$allProfiles = $criteriaNames = $allCriteria = [];
		foreach ($this->candidates as $candidate) {
			$profile = $candidate->getProfile();
			$allProfiles[$candidate->getName()] = $profile;
			foreach ($profile as $key => $value) {
				$allCriteria[$key][$candidate->getName()] = $value;
			}
		}
		$criteriaNames = array_keys($allCriteria);
		$allCandidatesNames = array_keys($allProfiles);
		// drop criteria not common to every candidate
		$checks = array_map(function($column) use ($allCandidatesNames) { return array_diff($allCandidatesNames, array_keys($column));}, $allCriteria);
		$corrects = array_keys(array_filter($checks, function($check){ return count($check)==0;}));
		$incorrects = array_keys(array_filter($checks, function($check){ return count($check)>0;}));
		$keptCriteria = array_intersect_key($allCriteria, array_flip($corrects));
		$droppedCriteria = array_intersect_key($allCriteria, array_flip($incorrects));
		if(count($droppedCriteria) > 0){
			$message = "Some Criteria: ".implode(', ', array_keys($droppedCriteria))." were dropped";
			throw new \Exception($message, 1);
		}
		// generate criterion
		foreach ($keptCriteria as $name => $criterionScores) {
			$criterion = new Criterion($name);
			$min = min($criterionScores);
			$max = max($criterionScores);
			$i = $j = 0;
			foreach ($criterionScores as $name1 => $score1) {
				$i++;
				$j = 0;
				foreach ($criterionScores as $name2 => $score2) {
					$j++;
					if($i < $j){
						$whichCandidate = ($score1 >= $score2) ? 'scoreCandidate1' : 'scoreCandidate2';
						$weight = round(($maxScale - $minScale) * (max($score1, $score2) - $min) / ($max - $min) + $minScale);
						$candidate1 = $this->candidates[$name1];
						$candidate2 = $this->candidates[$name2];
						$pCArray = ['candidate1' => $candidate1,'candidate2' => $candidate2,$whichCandidate => $weight];
						$pairwiseComparison = new PairwiseComparison($pCArray);
						$criterion->addPairwiseComparison($pairwiseComparison);
					}
				}
			}
			$this->addCriterion($criterion);
		}
		return $this;
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
		$candidatesPriorities = [];
		foreach ($this->criteria as $criterion) {
			if($criterion->getType() != 'goal')
				$candidatesPriorities[$criterion->getName()] = $criterion->getPriorities();
		}
		$this->candidatesPriorities = $candidatesPriorities;
		return $this->candidatesPriorities;
	}

	public function getGoal():Criterion
	{
		if(!isset($this->goalCriterion)){
			// nothing defined, we create equal weights
			$goalCriterion = new Criterion('goal');
			$goalCriterion->setType('goal');
			$goalCriterion->setWeights(array_fill(0, count($this->criteria), 1));
			// create pairwiseComparison for all possible couples
			for($i = 0; $i < count($this->criteria); $i++){
				for($j = 0; $j < $i; $j++){
						$candidate1 = $this->criteria[$i]->deriveCandidate();
						$candidate2 = $this->criteria[$j]->deriveCandidate();
						$pairwiseComparison = new PairwiseComparison(['candidate1'=>$candidate1,'candidate2'=>$candidate2,'scoreCandidate1'=>1]);
						$goalCriterion->addPairwiseComparison($pairwiseComparison);
				}
			}
			$this->goalCriterion = $goalCriterion;
		}
		return $this->goalCriterion;
	}

	public function getFinalPriorities($method = 'RGGM'){
		switch ($method) {
			case 'EVM':
				return $this->getFinalPrioritiesEVM();
				break;
			
			case 'RGGM':
			default:
				return $this->getFinalPrioritiesRGGM();
				break;
		}
	}

	public function getFinalPrioritiesEVM(){
		if(empty($this->candidatesPriorities)){
			$this->getPriorities();
		}
		// goal criterion
		$this->goalCriterion = $this->getGoal();
		$totalWeights = $this->goalCriterion->getTotalWeights();
		$indexMatrix = $this->goalCriterion->applyTransformation() ? $this->goalCriterion->getTransformationFunction()['original'] : 'values';
		$inverseFunction = $this->goalCriterion->applyTransformation() ? $this->goalCriterion->getTransformationFunction()['inverse'] : function($x){return $x;};
		// all criteria
		// calculate matrices : consolidated transformed and consolidated
		$nbCandidates = $this->criteria[0]->getNbCandidates();
		$headers = $this->criteria[0]->getMatrixHeaders();
		$consolidatedMatrix = array_fill(0, $nbCandidates, array_fill(0, $nbCandidates, 0));
		$consolidatedTransformedMatrix = array_fill(0, $nbCandidates, array_fill(0, $nbCandidates, 0));
		// transformed is the sum of each criterion's transformed matrix
		foreach ($this->criteria as $cntrCriterion => $criterion) {
			$criterionMatrix = $criterion->getMatrix();
			$criterionMatrices[$criterion->getName()] = $criterionMatrix;
			for($i = 0; $i < count($criterionMatrix[$indexMatrix]); $i++){
				$criterionWeight = $this->goalCriterion->getWeight($cntrCriterion);
				for($j = 0; $j < $i; $j++){
					// weighted geometric mean
					$consolidatedTransformedMatrix[$i][$j] += $criterionMatrix[$indexMatrix][$i][$j] * $criterionWeight/$totalWeights;
				}
			}
		}
		// consolidated is the inverse of the transformed
		for($j = 0; $j < $nbCandidates; $j++){
			for($i = 0; $i < $nbCandidates; $i++){
				if($j < $i){
					$consolidatedMatrix[$i][$j] = call_user_func($inverseFunction,$consolidatedTransformedMatrix[$i][$j]);
				}
				else if($j == $i){
					$consolidatedMatrix[$i][$j] = 1;
				}
				else{
					$consolidatedMatrix[$i][$j] = 1/$consolidatedMatrix[$j][$i];
				}
			}
		}
		$this->consolidatedMatrix = $consolidatedMatrix;
		$transposed = array_map(null, ...$this->consolidatedMatrix);
		// calculate eigenvalues based on number of iterations
		$raw = $scaled = $normalized = $eigenvalues = $errors = [];
		$errorThreshold = 1e-9;
		for($i = 0; $i < $this->nbIterations; $i++){
			$raw[$i] = $scaled[$i] = $normalized[$i] = $errors[$i] = [];
			if($i == 0){
				$raw[$i] = array_map(function($row) use ($nbCandidates) { return array_sum($row)/$nbCandidates;} , $transposed);
				$errors[$i] = array_fill(0, $nbCandidates, 1/$nbCandidates);
				$eigenvalues[$i] = 0;
			}
			else{
				$previousScaled = $scaled[$i-1];
				// raw is the matrix product between previously scaled and consolidated matrix
				$raw[$i] = array_map(function($column) use ($previousScaled) {
							return array_sum(
								array_map(function($elt, $previousScaled){
									return $elt * $previousScaled;
								}, $column, $previousScaled)
							); }, $transposed, $previousScaled);
			}
			$maxRaw = max($raw[$i]);
			// scaled raw vector
			$scaled[$i] = array_map(function($row) use ($maxRaw) { return $row/$maxRaw;}, $raw[$i]);
			$sumScaled = array_sum($scaled[$i]);
			// normalized scaled vector
			$normalized[$i] = array_map(function($row) use ($sumScaled) { return $row/$sumScaled;}, $scaled[$i]);
			if($i > 0){
				// calculate eigenvalues
				$sumCols = array_map('array_sum',$this->consolidatedMatrix);
				$eigenvalues[$i] = array_sum(array_map(function($a, $b) { return $a * $b; }, $normalized[$i], $sumCols ));
				// calculate errors
				$errors[$i] = array_map(function($a, $b) { return pow($a - $b, 2); }, $normalized[$i], $normalized[$i-1]);
			}
			if(array_sum($errors[$i]) < $errorThreshold ){
				break;
			}
		}
		$this->finalPriorities = array_combine($headers, $normalized[$i]);
		return $this->finalPriorities;
	}

	public function getFinalPrioritiesRGGM()
	{
		if(empty($this->candidatesPriorities)){
			$this->getPriorities();
		}
		// goal priorities
		$this->goalCriterion = $this->getGoal();
		$this->goalPriorities = $this->goalCriterion->getPriorities();
		$tmp = [];
		$tmp['colnames'] = $this->goalPriorities['headers'];
		// candidates priorities
		$candidatesPriorities = $this->candidatesPriorities;
		$tmp['rownames'] = $candidatesPriorities[$tmp['colnames'][0]]['headers'];
		// flatten the priorities
		$candidatesPriorities = array_replace($candidatesPriorities,array_map(function($criteria){ return array_combine($criteria['headers'], $criteria['values']); }, $candidatesPriorities) );
		//$candidatesPriorities = array_map(function($criteria) use($tmp) { return array_replace(array_flip($tmp['rownames']), $criteria); } , $candidatesPriorities);
		$tmp['values'] = [];
		foreach ($this->goalPriorities['values'] as $goalPrioritiesKey => $goalPrioritiesValue) {
			$goalPrioritiesName = $this->goalPriorities['headers'][$goalPrioritiesKey];
			// select all candidates' priorities per criteria
			$candidatesPrioritiesPerCriteria = $candidatesPriorities[$goalPrioritiesName];
			// calculate weighted candidates' priorities per criteria
			$tmp['values'][$goalPrioritiesKey] = array_map(function($item) use ($goalPrioritiesValue) { return $goalPrioritiesValue * $item ; }, $candidatesPrioritiesPerCriteria);
		}
		// transpose for summing
		$finalPrioritiesMatrix = array_map(null, ...$tmp['values']);
		// sum to get all criteria's weights per candidate
		$finalPriorities = array_map('array_sum',$finalPrioritiesMatrix);
		// a bit of tidying up
		$this->finalPriorities = array_combine($tmp['rownames'], $finalPriorities);
		return $this->finalPriorities;
	}


}