<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Lattice\AHP\Criterion;
use Lattice\AHP\Candidate;
use Lattice\AHP\PairwiseComparison;
use Lattice\AHP\AHP;

$ahp = new AHP();

$cost = new Criterion('costs');
$safety = new Criterion('safety');
$style = new Criterion('style');
$capacity = new Criterion('capacity');
// goal
$goal = new Criterion('Buy Car');
$goal->setType('goal');
// add the criteria to the goal criterion
$goal->addCriterion($cost);
$goal->addCriterion($safety);
$goal->addCriterion($style);
$goal->addCriterion($capacity);
// add the goal to the analysis
$ahp->addCriterion($goal);

// children for $cost
$purchasePrice = new Criterion('purchasePrice');
$fuelCosts = new Criterion('fuelCosts');
$maintenanceCosts = new Criterion('maintenanceCosts');
$resaleValue = new Criterion('resaleValue');
$cost->addCriterion($purchasePrice);
$cost->addCriterion($fuelCosts);
$cost->addCriterion($maintenanceCosts);
$cost->addCriterion($resaleValue);

// children for $capacity
$cargoCapacity = new Criterion('cargoCapacity');
$passengerCapacity = new Criterion('passengerCapacity');
$capacity->addCriterion($cargoCapacity);
$capacity->addCriterion($passengerCapacity);

// define the candidates
$sedan = new Candidate(['name'=>'Accord Sedan']);
$hybrid = new Candidate(['name'=>'Accord Hybrid']);
$pilot = new Candidate(['name'=>'Pilot']);
$crv = new Candidate(['name'=>'CR-V']);
$element = new Candidate(['name'=>'Element']);
$odyssey = new Candidate(['name'=>'Odyssey']);

$ahp->addCandidate($sedan);
$ahp->addCandidate($hybrid);
$ahp->addCandidate($pilot);
$ahp->addCandidate($crv);
$ahp->addCandidate($element);
$ahp->addCandidate($odyssey);


/*
 * Goal
 *
 */
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$cost,'candidate2'=>$safety,'scoreCandidate1'=>3]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$cost,'candidate2'=>$style,'scoreCandidate1'=>7]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$cost,'candidate2'=>$capacity,'scoreCandidate1'=>3]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$safety,'candidate2'=>$style,'scoreCandidate1'=>9]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$safety,'candidate2'=>$capacity,'scoreCandidate1'=>1]));
$goal->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$style,'candidate2'=>$capacity,'scoreCandidate1'=>1/7]));

/*
 * costs
 *
 */
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$purchasePrice, 'candidate2'=>$fuelCosts, 'scoreCandidate1'=>2]));
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$purchasePrice, 'candidate2'=>$maintenanceCosts, 'scoreCandidate1'=>5]));
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$purchasePrice, 'candidate2'=>$resaleValue, 'scoreCandidate1'=>3]));
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$fuelCosts, 'candidate2'=>$maintenanceCosts, 'scoreCandidate1'=>2]));
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$fuelCosts, 'candidate2'=>$resaleValue, 'scoreCandidate1'=>2]));
$cost->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$maintenanceCosts, 'candidate2'=>$resaleValue, 'scoreCandidate1'=>1/2]));

// purchasePrice
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>1]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>1/2]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>5]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>1]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>1/9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>1/9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>1/7]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>1/9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>1/9]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1/7]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>1/2]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>5]));
$purchasePrice->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>6]));

// fuelCosts
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>31/35]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>31/22]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>31/27]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>31/25]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>31/26]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>35/22]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>35/27]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>35/25]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>35/26]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>22/27]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>22/25]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>22/26]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>27/25]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>27/26]));
$fuelCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>25/26]));

// maintenanceCosts
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>1.5]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>5]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>4]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>5]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>1]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>1.2]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>1]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>3]));
$maintenanceCosts->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>2]));

// resaleValue
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>3]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>4]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>1/2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>1/5]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>1/1]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>1/1]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>1/6]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>1/2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1/2]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>4]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>4]));
$resaleValue->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1]));


/*
 * safety
 *
 */
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>1]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>5]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>7]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>9]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/3]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>5]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>7]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>9]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>1/3]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>2]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>9]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1/8]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>2]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>1/8]));
$safety->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/9]));


/*
 * style
 *
 */
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>1]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>7]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>5]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>9]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>6]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>7]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>5]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>9]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>6]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>1/6]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>3]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1/3]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>7]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>5]));
$style->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/5]));


/*
 * capacity
 *
 */
$capacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$cargoCapacity,'candidate2'=>$passengerCapacity,'scoreCandidate1'=>1/5]));

// cargoCapacity
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>1]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/3]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>1/2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>1/3]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>1]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>1]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>1]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>2]));
$cargoCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>2]));

// passengerCapacity
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$hybrid,'scoreCandidate1'=>1]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$pilot,'scoreCandidate1'=>1/2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$crv,'scoreCandidate1'=>1]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$element,'scoreCandidate1'=>3]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$pilot,'scoreCandidate1'=>1/2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$crv,'scoreCandidate1'=>1]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$element,'scoreCandidate1'=>3]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$hybrid,'candidate2'=>$odyssey,'scoreCandidate1'=>1/2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$crv,'scoreCandidate1'=>2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$element,'scoreCandidate1'=>6]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$pilot,'candidate2'=>$odyssey,'scoreCandidate1'=>1]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$element,'scoreCandidate1'=>3]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$crv,'candidate2'=>$odyssey,'scoreCandidate1'=>1/2]));
$passengerCapacity->addPairwiseComparison(new PairwiseComparison(['candidate1'=>$sedan,'candidate2'=>$odyssey,'scoreCandidate1'=>1/6]));


/*
 *
 *
 */

$ahp->displayResults('total');
$ahp->displayResults();












