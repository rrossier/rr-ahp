<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Lattice\AHP\Matrix;

/*
 * Multidimensional and single dimensional (vector) matrices
 * Basic operations
 *
 */
echo '<h3>Basic operations</h3>';
$A = new Matrix([ [1, 4], [2, 3], [0, -1] ]);
$B = new Matrix([ [5, 2, 3], [1, 3, 4] ]);
echo '$A->display()';
$A->display();
echo '$B->display()';
$B->display();
echo '$A->sum()';
dump($A->sum());
echo '$B->product()';
dump($B->product());
echo '$A->matrixProduct($B)';
dump($A->matrixProduct($B));
echo '$B->matrixProduct($A)';
dump($B->matrixProduct($A));

$C = new Matrix([ [2, 1, -1] ]);
echo '$C->display()';
$C->display();
echo '$C->sumPerColumn()';
dump($C->sumPerColumn());
echo '$C->transpose()';
dump($C->transpose());
echo '$C->sumPerRow()';
dump($C->sumPerRow());
echo '$C->matrixProduct($C->transpose(FALSE))';
dump($C->matrixProduct($C->transpose(FALSE)));
echo '$C->transpose(FALSE)->matrixProduct($C)';
dump($C->transpose(FALSE)->matrixProduct($C));

$D = new Matrix([ [17, 3, 42, 5], [19, 4, 8, 25], [7, 31, 11, 22] ]);
echo '$D->display()';
$D->display();
echo '$D->scalarProduct(2)';
dump($D->scalarProduct(2));
echo '$E = $D->scalarSum(-5)';
$E = $D->scalarSum(-5);
echo '$E->display()';
$E->display();
echo '$D->squaredDifferences($E)';
dump($D->squaredDifferences($E));
echo '$D->scale()';
dump($D->scale());
echo '$D->scale()->normalize()';
dump($D->scale()->normalize());
echo '$D->scale()->normalize()->sum()';
dump($D->scale()->normalize()->sum());
echo '$D->applyFunction(\'exp\', FALSE)';
dump($D->applyFunction('exp', FALSE));
echo '$D->display()';
$D->display();

/*
 * Square matrix
 * Eigenvector and eigenvalue calculations
 *
 */
echo '<h3>Square Matrix</h3>';
$S = new Matrix([ [1, 0.38, 1.06], [2.61, 1, 2.45], [0.95, 0.41, 1] ]);
echo '$S->display()';
$S->display();
echo '$eigenvector = $S->calculateEigenvector()<br/>';
$eigenvector = $S->calculateEigenvector();
echo '$eigenvalue = $S->calculateEigenvalue()<br/>';
$eigenvalue = $S->calculateEigenvalue();
echo '$S->checkEigenvector()';
dump($S->checkEigenvector());
echo 'dump($S)';
dump($S);


