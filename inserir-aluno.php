<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Alura\Pdo\Infrastructure\Repository\PdoStudentRepository;

require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();

$student = new Student(null,
    "Thábata Abati Seara",
    new \DateTimeImmutable('1990-04-14')
);

$studentRepository = new PdoStudentRepository();

if ($studentRepository->save($student)) {
    echo "Aluno inserido com sucesso!";
}

