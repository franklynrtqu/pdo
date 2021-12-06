<?php

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Alura\Pdo\Infrastructure\Repository\PdoStudentRepository;

require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();

$student = new Student(
    5,
    "Thábata Abati Seara (Atualizado)",
    new DateTimeImmutable('1990-04-14')
);

$studentRepository = new PdoStudentRepository();

if ($studentRepository->save($student)) {
    echo "Aluno atualizado com sucesso!";
}