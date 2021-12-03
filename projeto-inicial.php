<?php

use Alura\Pdo\Domain\Model\Student;

require_once 'vendor/autoload.php';

$student = new Student(
    null,
    'Franklyn de Quadros',
    new \DateTimeImmutable('1993-11-21')
);

echo $student->age();
