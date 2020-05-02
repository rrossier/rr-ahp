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
echo '<h3>Minimum Example</h3>';

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom','profile'=>['experience'=>10,'education'=>5,'charisma'=>9,'age'=>50]]);
$dick = new Candidate(['name'=>'Dick','profile'=>['experience'=>30,'education'=>3,'charisma'=>5,'age'=>60]]);
$harry = new Candidate(['name'=>'Harry','profile'=>['experience'=>5,'education'=>7,'charisma'=>3,'age'=>30]]);

$experienceCriterion = new Criterion('Experience');
$educationCriterion = new Criterion('Education');
$charismaCriterion = new Criterion('Charisma');
$ageCriterion = new Criterion('Age');

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);

$ahp->displayResults('total');
$ahp->displayResults();
dump($ahp->getGoal()->getConsistencyRatio());

/*
 *
 *
 */
echo '<h3>Explicit Comparisons without Goal</h3>';

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom']);
$dick = new Candidate(['name'=>'Dick']);
$harry = new Candidate(['name'=>'Harry']);

$experienceCriterion = new Criterion('Experience');
$educationCriterion = new Criterion('Education');
$charismaCriterion = new Criterion('Charisma');
$ageCriterion = new Criterion('Age');

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

$ahp->displayResults('total');
$ahp->displayResults();
dump($ahp->getGoal()->getConsistencyRatio());


/*
 *
 *
 */
echo '<h3>Implicit Comparisons with Goal</h3>';
$ahp = new AHP();

// define the candidates
$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom','profile'=>['Experience'=>10,'Education'=>5,'Charisma'=>9,'Age'=>50]]);
$dick = new Candidate(['name'=>'Dick','profile'=>['Experience'=>30,'Education'=>3,'Charisma'=>5,'Age'=>60]]);
$harry = new Candidate(['name'=>'Harry','profile'=>['Experience'=>5,'Education'=>7,'Charisma'=>3,'Age'=>30]]);

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);

// goal criterion
$goal = new Criterion('Choose a leader');
$goal->setType('goal');
// explicitly added
$ahp->addCriterion($goal);
// define the criteria below goal
$experienceCriterion = new Criterion('Experience');
$educationCriterion = new Criterion('Education');
$charismaCriterion = new Criterion('Charisma');
$ageCriterion = new Criterion('Age');
// add the criteria to the goal criterion
$goal->addCriterion($experienceCriterion);
$goal->addCriterion($educationCriterion);
$goal->addCriterion($charismaCriterion);
$goal->addCriterion($ageCriterion);
// define the comparisons between the criteria
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$experienceCriterion,'candidate2'=>$educationCriterion,'scoreCandidate1'=>4]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$experienceCriterion,'candidate2'=>$charismaCriterion,'scoreCandidate1'=>3]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$experienceCriterion,'candidate2'=>$ageCriterion,'scoreCandidate1'=>7]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$educationCriterion,'candidate2'=>$charismaCriterion,'scoreCandidate1'=>1/3]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$educationCriterion,'candidate2'=>$ageCriterion,'scoreCandidate1'=>3]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$charismaCriterion,'candidate2'=>$ageCriterion,'scoreCandidate1'=>5]));
// generate for each criteria attached to the goal the candidates' comparisons
$ahp->generateCandidatesComparison(); // only works if goal has criteria with names matched in each candidate's profile

$ahp->displayResults('total');
$ahp->displayResults();
dump($ahp->getGoal()->getConsistencyRatio());

/*
 *
 *
 */
echo '<h3>Explicit Comparisons and Goal</h3>';

$ahp = new AHP();

$tom = new Candidate(['name'=>'Tom']);
$dick = new Candidate(['name'=>'Dick']);
$harry = new Candidate(['name'=>'Harry']);

$experienceCriterion = new Criterion('Experience');
$educationCriterion = new Criterion('Education');
$charismaCriterion = new Criterion('Charisma');
$ageCriterion = new Criterion('Age');
// goal criterion
$goalCriterion = new Criterion('Choose a leader');
$goalCriterion->setType('goal');
// explicitly added
$ahp->addCriterion($goalCriterion);

$ahp->addCandidate($tom);
$ahp->addCandidate($dick);
$ahp->addCandidate($harry);

$goalCriterion->addCriterion($experienceCriterion);
$goalCriterion->addCriterion($educationCriterion);
$goalCriterion->addCriterion($charismaCriterion);
$goalCriterion->addCriterion($ageCriterion);

// define the comparisons as before
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

// define the comparisons between the criteria
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceCriterion,
											'candidate2' => $educationCriterion,
											'scoreCandidate1' => 4
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceCriterion,
											'candidate2' => $charismaCriterion,
											'scoreCandidate1' => 3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $experienceCriterion,
											'candidate2' => $ageCriterion,
											'scoreCandidate1' => 7
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $educationCriterion,
											'candidate2' => $charismaCriterion,
											'scoreCandidate1' => 1/3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $educationCriterion,
											'candidate2' => $ageCriterion,
											'scoreCandidate1' => 3
											]));
$goalCriterion->addPairwiseComparison(new PairwiseComparison([
											'candidate1' => $ageCriterion,
											'candidate2' => $charismaCriterion,
											'scoreCandidate2' => 5
											]));

$ahp->displayResults('total');
$ahp->displayResults();
dump($ahp->getGoal()->getConsistencyRatio());
