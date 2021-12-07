<?php

$caminhoBanco = __DIR__ . '/banco.sqlite';
$pdo = new PDO('sqlite:' . $caminhoBanco);

echo  'Conectei';

/* Inserção sem o cliente SQL, para testes.
try {
    $pdo->exec("INSERT INTO phones (area_code, number, student_id) VALUES ('24', '999999999', 2), ('49', '989898989', 2)");
    exit();
} catch (PDOException $e) {
    var_dump($e->errorInfo);
    exit();
}
*/


$createTableSql = '
    CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY,
        name TEXT,
        birth_date TEXT  
    );
    
    CREATE TABLE IF NOT EXISTS phones (
        id INTEGER PRIMARY KEY,
        area_code TEXT,
        number TEXT,
        student_id INTEGER,
        FOREIGN KEY(student_id) REFERENCES students(id)
    );
';

$pdo->exec($createTableSql);

