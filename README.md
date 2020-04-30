# Analytic hierarchy process

PHP implementation of the Analytic Hierarchy Process (AHP) by [Thomas L. Saaty][5].

## Description

More on [Wikipedia][1]

## Installation via Composer

```
composer require lattice/ahp
```

## Usage

### Basic example
Reproducting the `Choose a Leader` example from [Wikipedia][2]
```
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
```

More in the following files:

* [examples.php](examples.php) explaining basic examples
* [examples2.php](examples2.php) showing the full example of [Choosing a car for the Jones family][3]
* [examples3.php](examples3.php) documenting the class __Matrix__

## Todo

* implements a load function to define model in a file
* implements custom preference functions

## License

This project is licensed under the MIT License - see the [LICENSE.md](https://opensource.org/licenses/MIT) file for details

## Acknowledgments

* Inspiration for formatting results and ideas for future evolutions thanks to [R package AHP][4]


[1]: https://en.wikipedia.org/wiki/Analytic_hierarchy_process
[2]: https://en.wikipedia.org/wiki/Analytic_hierarchy_process_%E2%80%93_leader_example
[3]: https://en.wikipedia.org/wiki/Analytic_hierarchy_process_%E2%80%93_car_example
[4]: https://cran.r-project.org/web/packages/ahp/
[5]: https://en.wikipedia.org/wiki/Thomas_L._Saaty