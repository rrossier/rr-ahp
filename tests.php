<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Lattice\AHP\Criterion;
use Lattice\AHP\Candidate;
use Lattice\AHP\PairwiseComparison;
use Lattice\AHP\AHP;

$tom = new Candidate(['name'=>'Tom','profile'=>['experience'=>10,'education'=>5,'charisma'=>9,'age'=>50]]);
$dick = new Candidate(['name'=>'Dick','profile'=>['experience'=>30,'education'=>3,'charisma'=>5,'age'=>60]]);
$harry = new Candidate(['name'=>'Harry','profile'=>['experience'=>5,'education'=>7,'charisma'=>3,'age'=>30]]);

$experienceCriterion = new Criterion('experience');
$educationCriterion = new Criterion('education');
$charismaCriterion = new Criterion('charisma');
$ageCriterion = new Criterion('age');
$goalCriterion = new Criterion('goal');
$goalCriterion->setType('goal');

$ahp = new AHP();

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

$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>3]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate2'=>5]));
$educationCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate2'=>7]));

$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate1'=>5]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>9]));
$charismaCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>4]));

$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$dick,'scoreCandidate2'=>3]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$tom,'candidate2'=>$harry,'scoreCandidate1'=>5]));
$ageCriterion->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$dick,'candidate2'=>$harry,'scoreCandidate1'=>9]));

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

dump($ahp->getFinalPriorities());
