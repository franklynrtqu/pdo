<?php

namespace Alura\Pdo\Infrastructure\Repository;

use Alura\Pdo\Domain\Model\Phone;
use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Domain\Repository\StudentRepository;
use http\Exception\RuntimeException;

class PdoStudentRepository implements StudentRepository
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allStudents(): array
    {
        $statement = $this->connection->query("SELECT * FROM students;");
        return $this->hydraStudentList($statement);
    }

    public function studentsBirthAt(\DateTimeInterface $birthDate): array
    {
        $statement = $this->connection->prepare("SELECT * FROM students WHERE birth_date = ?;");
        $statement->bindValue(1, $birthDate);
        $studentDataList = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $studentList = [];

        foreach ($studentDataList as $studentData) {
            $studentList[] = new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date'])
            );
        }

        return $studentList;
    }

    private function hydraStudentList(\PDOStatement $stmt): array
    {
        $studentDataList = $stmt->fetchAll();
        $studentList = [];

        foreach ($studentDataList as $studentData) {
            $studentList[] = new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date'])
            );
        }

        return $studentList;
    }

    /*
    // Esse método estava sendo usado para retornar os números de telefone associados a um aluno,
    // mas ele causa o problema conhecido como N+1, onde para mil consultas de aluno geraria outras
    // mil consultas para retornar os números de telefones associados a cada aluno.
    // Mantive o código apenas para ter uma referência futura.

    private function fillPhonesOf(Student $student): void
    {
        $sqlQuery = "SELECT id, area_code, number FROM phones WHERE student_id = ?";
        $stmt = $this->connection->prepare($sqlQuery);
        $stmt->bindValue(1, $student->id(), \PDO::PARAM_INT);
        $stmt->execute();

        $phoneDataList = $stmt->fetchAll();
        foreach ($phoneDataList as $phoneData) {
            $phone = new Phone(
                $phoneData['id'],
                $phoneData['area_code'],
                $phoneData['number']
            );

            $student->addPhone($phone);
        }
    }
    */

    public function save(Student $student): bool
    {
        if($student->id() === null) {
            return $this->insert($student);
        }

        return $this->update($student);
    }

    private function insert(Student $student): bool
    {
        $insertQuery = "INSERT INTO students (name, birth_date) VALUES (:name, :birth_date);";
        $stmt = $this->connection->prepare($insertQuery);

        $success = $stmt->execute([
            ':name' => $student->name(),
            ':birth_date' => $student->birthDate()->format('Y-m-d')
        ]);

        if ($success) {
            $student->defineId($this->connection->lastInsertId());
        }

        return $success;
    }

    private function update(Student $student): bool
    {
        $updateQuery = "UPDATE students SET name = :name, birth_date = :birth_date WHERE id = :id;";
        $stmt = $this->connection->prepare($updateQuery);
        $stmt->bindValue(':name', $student->name());
        $stmt->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));
        $stmt->bindValue(':id', $student->id(), \PDO::PARAM_INT);

        var_dump($updateQuery);

        return $stmt->execute();
    }

    public function remove(Student $student): bool
    {
        $prepareStatment = $this->connection->prepare('DELETE FROM students WHERE id = ?;');
        $prepareStatment->bindValue(1, $student->id(), \PDO::PARAM_INT);

        return $prepareStatment->execute();
    }

    public function studentWithPhones(): array
    {
        $sqlQuery = "SELECT students.id,
                            students.name,
                            students.birth_date,
                            phones.id AS phone_id,
                            phones.area_code,
                            phones.number
                       FROM students
                       JOIN phones ON students.id = phones.student_id;";
        $stmt = $this->connection->query($sqlQuery);
        $result = $stmt->fetchAll();
        $studentList = [];

        foreach ($result as $row) {
            if (!array_key_exists($row['id'], $studentList)) {
                $studentList[$row['id']] = new Student(
                    $row['id'],
                    $row['name'],
                    new \DateTimeImmutable($row['birth_date'])
                );
            }
            $phone = new Phone($row['phone_id'], $row['area_code'], $row['number']);
            $studentList[$row['id']]->addPhone($phone);
        }

        return $studentList;
    }
}