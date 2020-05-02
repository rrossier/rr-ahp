<?php

namespace Lattice\AHP;
use Lattice\AHP\Candidate;

class AHP{

	private $candidates;
	private $criteria;
	private $goalCriterion;

	private $localPriorities;
	private $totalPriorities;
	private $priorities;
	private $weights;

	public function __construct(){
		$this->candidates = [];
		$this->criteria = [];
		$this->localPriorities = [];
		$this->totalPriorities = [];
		$this->priorities = [];
		$this->weights = [];
	}

	public function addCandidate(Candidate $candidate):self
	{
		if(!in_array($candidate,$this->candidates, TRUE)){
			$this->candidates[$candidate->getName()] = $candidate;
		}
		return $this;
	}

	public function getCandidates()
	{
		return $this->candidates;
	}

	public function getCandidatesNames()
	{
		$names = [];
		foreach ($this->candidates as $candidate) {
			if(!in_array($candidate->getName(),$names, TRUE)){
				$names[] = $candidate->getName();
			}
		}
		sort($names);
		return $names;
	}

	public function addCriterion(Criterion $criterion):self
	{
		if(!in_array($criterion,$this->criteria, TRUE)){
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
		return $this->goalCriterion->getCriteria();
	}

	public function getPriorities()
	{
		if(empty($this->priorities))
			$this->calculatePriorities();

		return $this->priorities;
	}

	public function getWeights()
	{
		if(empty($this->weights))
			$this->calculatePriorities();

		return $this->weights;
	}

	public function generateChildrenCriteria():self
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
		$goalCriterion = $this->goalCriterion;
		// generate children criteria with the respective pairwiseComparaisons each time
		$shape = 2;
		foreach ($keptCriteria as $name => $criterionScores) {
			$criterion = new Criterion($name);
			$min = min($criterionScores) > 0 ? min($criterionScores)/$shape : $shape*min($criterionScores);
			$max = max($criterionScores) > 0 ? $shape*max($criterionScores) : max($criterionScores)/$shape;
			$i = $j = 0;
			// generate the pairwiseComparaisons for this sub criterion
			foreach ($criterionScores as $name1 => $score1) {
				$i++;
				$j = 0;
				foreach ($criterionScores as $name2 => $score2) {
					$j++;
					if($i < $j){
						$whichCandidate = ($score1 >= $score2) ? 'scoreCandidate1' : 'scoreCandidate2';
						// TODO: implements custom scaling and preference functions
						$weight = round(($maxScale - $minScale) * (max($score1, $score2) - $min) / ($max - $min) + $minScale);
						$candidate1 = $this->candidates[$name1];
						$candidate2 = $this->candidates[$name2];
						$pCArray = ['candidate1' => $candidate1, 'candidate2' => $candidate2, $whichCandidate => $weight];
						$pairwiseComparison = new PairwiseComparison($pCArray);
						$criterion->addPairwiseComparison($pairwiseComparison);
					}
				}
			}
			$goalCriterion->addCriterion($criterion);
		}
		return $this;
	}

	public function generateCandidatesComparison()
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
		// generate pairwiseComparaisons for each children criteria
		$shape = 2;
		foreach ($keptCriteria as $name => $candidatesScores) {
			$criterion = $this->goalCriterion->getCriterion($name);
			$i = $j = 0;
			// generate the pairwiseComparaisons for this sub criterion
			foreach ($candidatesScores as $name1 => $score1) {
				$i++;
				$j = 0;
				foreach ($candidatesScores as $name2 => $score2) {
					$j++;
					if($i < $j){
						$whichCandidate = ($score1 >= $score2) ? 'scoreCandidate1' : 'scoreCandidate2';
						// TODO: implements custom scaling and preference functions
						$weight = min(9, max(1/9, max($score2, $score1)/min($score1,$score2) ));
						$candidate1 = $this->candidates[$name1];
						$candidate2 = $this->candidates[$name2];
						$pCArray = ['candidate1' => $candidate1, 'candidate2' => $candidate2, $whichCandidate => $weight];
						$pairwiseComparison = new PairwiseComparison($pCArray);
						$criterion->addPairwiseComparison($pairwiseComparison);
					}
				}
			}
		}
		return $this;
	}

	public function getGoal():Criterion
	{
		if(!isset($this->goalCriterion)){
			// nothing defined, we create a goalCriterion that will hold the subcriteria
			$goalCriterion = new Criterion('goal');
			$goalCriterion->setType('goal');
			$this->goalCriterion = $goalCriterion;
			if(empty($this->criteria)){
				$this->generateChildrenCriteria();
			}
			else{
				foreach($this->criteria as $criterion){
					$this->goalCriterion->addCriterion($criterion);
				}
			}
			$this->goalCriterion->generateCriteriaPairwiseComparison();
		}
		return $this->goalCriterion;
	}

	private function calculatePriorities()
	{
		if(empty($this->localPriorities))
			$this->calculateLocalPriorities();

		if(empty($this->totalPriorities))
			$this->calculateTotalPriorities();
	}

	private function calculateTotalPriorities():self
	{
		$this->totalPriorities = ['headers'=>$this->getCandidatesNames(),'criteria'=>[],'values'=>[]];
		$this->flattenTotalPriorities($this->localPriorities[$this->getGoal()->getName()]);
		// insert into a Matrix object for handling
		$this->totalPriorities['values'] = new Matrix($this->totalPriorities['values']);
		
		// priorities per candidate
		$this->priorities = array_combine( $this->totalPriorities['headers'], 
											$this->totalPriorities['values']->sumPerColumn()->extract());
		
		// weights per criteria
		// only considering leaves
		$keys = $this->totalPriorities['values']->getKeys();
		$criteriaLeaves = array_intersect_key($this->totalPriorities['criteria'], array_flip($keys) );
		$this->weights = array_combine( $criteriaLeaves,
										$this->totalPriorities['values']->sumPerRow()->extract());
		return $this;
	}

	private function flattenTotalPriorities($priorities, $level = 0, $weights = [], $criterionName = null)
	{
		foreach ($priorities as $key => $value) {
			if($key === 0){
				// totals
				$totalsArray = $value;
				foreach ($totalsArray as $criterionName => $criterionPriority) {
					if(!in_array($criterionName, $this->totalPriorities['criteria'], TRUE)){
						$this->totalPriorities['criteria'][] = $criterionName;
					}
					// let's jump into the specific criterion array
					$criterionArray = $priorities[$criterionName];
					$weights[$level] = $criterionPriority;
					$this->flattenTotalPriorities($criterionArray, ++$level, $weights, $criterionName);
					--$level;
				}
			}
			if(!is_array($value)){
				// nCol is the candidate name
				$nCol = array_search($key, $this->totalPriorities['headers']);
				// nRow is the criteria name
				$nRow = array_search($criterionName, $this->totalPriorities['criteria']);
				// organize
				$weight = array_product($weights);
				$this->totalPriorities['values'][$nCol][$nRow] = $value * $weight;
			}
		}
		return $this->totalPriorities;
	}

	private function displayTotalResults()
	{
		if(empty($this->totalPriorities))
			$this->calculatePriorities();

		$tmp = [];
		$tmp[0] = array_keys($this->priorities);
		$tmp = array_merge($tmp, $this->totalPriorities['values']->transpose(FALSE)->getValues());
		$tmp[] = array_values($this->priorities);
		$tmp = new Matrix($tmp);
		$tmp = $tmp->transpose()->extract();
		$table = '<table border="1"><tr><th>Candidate</th><th>'.implode('</th><th>', array_keys($this->weights) ).'</th><th>Total</th></tr>';
		
		foreach ($tmp as $key => $col) {
			$table .= '<tr>';
			$pos = 0;
			$nCol = count($col)-1;
			foreach ($col as $key => $cell) {
				if($pos == 0){
					$table .= '<td><strong>'.$cell.'</strong></td>';
				}
				else if($pos == $nCol){
					$table .= '<td><strong>'.number_format($cell*100, 2).' %</strong></td>';
				}
				else{
					$table .= '<td>'.number_format($cell*100, 2).' %</td>';
				}
				$pos++;
			}
			$table .= '</tr>';
		}
		/**/
		$table .= '<tr><td></td><td><strong>'.implode('</strong></td><td><strong>', array_map(function($x){ return number_format($x*100,2).' %'; }, array_values($this->weights) ) ).'</strong></td><td><strong>'.number_format(array_sum($this->weights)*100,2).'%</strong></td></tr>';
		$table .= '</table>';
		echo $table;
	}

	public function displayResults($visualisation = 'local')
	{
		switch ($visualisation) {
			case 'total':
				$this->displayTotalResults();
				break;
			
			case 'local':
			default:
				$this->displayLocalResults();
				break;
		}
	}

	private function calculateLocalPriorities():self
	{
		$goalCriterion = $this->getGoal();
		$this->localPriorities = [$goalCriterion->getName() => $goalCriterion->getPriorities()];
		return $this;
	}

	private function flattenLocalPriorities($priorities, $level=0)
	{
		$padding = str_repeat('&nbsp;', 4);
		foreach ($priorities as $key => $value) {
			if($key === 0){
				// totals
				$totalsArray = $value;
				foreach ($totalsArray as $criterionName => $criterionPriority) {
					$this->table[0][] = str_repeat("|".$padding, $level)."|-- ".ucfirst($criterionName);
					$this->table[1][] = $criterionPriority;
					$this->table[2][] = 1;
					// let's jump into the specific criterion array
					$criterionArray = $priorities[$criterionName];
					$this->flattenLocalPriorities($criterionArray, ++$level);
					--$level;
				}
			}
			if(!is_array($value)){
				$this->table[0][] = str_repeat("|".$padding, $level)."|-- ".$key;
				$this->table[1][] = $value ;
				$this->table[2][] = 0;
			}
		}
		return $this->table;
	}

	private function displayLocalResults()
	{
		if(empty($this->localPriorities))
			$this->calculatePriorities();

		$this->table = [0=>[$this->getGoal()->getName()],1=>[1],2=>[1]];
		$this->flattenLocalPriorities($this->localPriorities[$this->getGoal()->getName()]);
		$table = '<table border="1"><tr><th>Name</th><th>Priority (local value)</th></tr>';
		$table .= implode('</tr><tr>',
						array_map(
							function($key, $value, $header){
								$str  = "<td>";
								$str .= ($header) ? "<strong>".$key."</strong>" : $key;
								$str .= "</td><td>";
								$str .= ($header) ? "<strong>".number_format($value*100,2)." %</strong>" : number_format($value*100,2)." %" ;
								$str .= "</td>";
								return $str;
							},
							$this->table[0], $this->table[1], $this->table[2]
						)
					);
		$table .= '</table>';
		echo $table;
	}


}