<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Lattice\AHP\Criterion;
use Lattice\AHP\Candidate;
use Lattice\AHP\PairwiseComparison;
use Lattice\AHP\AHP;

/*
 *	
 *
 */

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom','profile'=>['experience'=>10,'education'=>5,'charisma'=>9,'age'=>50]]);
$dick = new Candidate(['name'=>'Dick','profile'=>['experience'=>30,'education'=>3,'charisma'=>5,'age'=>60]]);
$harry = new Candidate(['name'=>'Harry','profile'=>['experience'=>5,'education'=>7,'charisma'=>3,'age'=>30]]);

$experienceCriterion = new Criterion('experience');
$educationCriterion = new Criterion('education');
$charismaCriterion = new Criterion('charisma');
$ageCriterion = new Criterion('age');

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);
$ahp->generateCriteria();

echo '<h3>Minimum Example</h3>';
dump($ahp->getFinalPriorities('EVM'));
dump($ahp->getFinalPriorities('RGGM'));

/*
 *
 *
 */

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom']);
$dick = new Candidate(['name'=>'Dick']);
$harry = new Candidate(['name'=>'Harry']);

$experienceCriterion = new Criterion('experience');
$educationCriterion = new Criterion('education');
$charismaCriterion = new Criterion('charisma');
$ageCriterion = new Criterion('age');

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);

$ahp->addCriterion($experienceCriterion);
$ahp->addCriterion($educationCriterion);
$ahp->addCriterion($charismaCriterion);
$ahp->addCriterion($ageCriterion);

$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate2'=>4]));
$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>4]));
$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>9]));

$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate2'=>5]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>3]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate2'=>7]));

$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>4]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>9]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>5]));

$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$tom,'scoreCandidate1'=>3]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>9]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>5]));

echo '<h3>Explicit Comparisons without Goal</h3>';
dump($ahp->getFinalPriorities('EVM'));
dump($ahp->getFinalPriorities('RGGM'));

/*
 *
 *
 */

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom']);
$dick = new Candidate(['name'=>'Dick']);
$harry = new Candidate(['name'=>'Harry']);

$experienceCriterion = new Criterion('experience');
$educationCriterion = new Criterion('education');
$charismaCriterion = new Criterion('charisma');
$ageCriterion = new Criterion('age');
// goal criterion
$goalCriterion = new Criterion('goal');
$goalCriterion->setType('goal');

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);

$ahp->addCriterion($experienceCriterion);
$ahp->addCriterion($educationCriterion);
$ahp->addCriterion($charismaCriterion);
$ahp->addCriterion($ageCriterion);
$ahp->addCriterion($goalCriterion);

$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate2'=>4]));
$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>4]));
$experienceCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>9]));

$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate2'=>5]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>3]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate2'=>7]));

$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>4]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>9]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>5]));

$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$tom,'scoreCandidate1'=>3]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>9]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>5]));

//
$experienceNode = $experienceCriterion->deriveCandidate();
$educationNode = $educationCriterion->deriveCandidate();
$charismaNode = $charismaCriterion->deriveCandidate();
$ageNode = $ageCriterion->deriveCandidate();

$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceNode,
											'candidate2' => $educationNode,
											'scoreCandidate1' => 4
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceNode,
											'candidate2' => $charismaNode,
											'scoreCandidate1' => 3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceNode,
											'candidate2' => $ageNode,
											'scoreCandidate1' => 7
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $educationNode,
											'candidate2' => $charismaNode,
											'scoreCandidate2' => 3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $educationNode,
											'candidate2' => $ageNode,
											'scoreCandidate1' => 3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $ageNode,
											'candidate2' => $charismaNode,
											'scoreCandidate2' => 5
											]));

echo '<h3>Explicit Comparisons and Goal</h3>';
dump($ahp->getFinalPriorities('EVM'));
dump($ahp->getFinalPriorities('RGGM'));

/*
 * Additional useful functions
 * based on last example
 */

foreach ($ahp->getCriteria() as $criterion) {
	echo '<strong>'.$criterion->getName().'</strong>';
	$criterion->displayMatrix();
	echo '<hr/>';
}

