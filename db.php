<?php
$host = 'localhost';
$port = '1521';
$sid = 'XE';
$username = 'TermProject';
$password = '1902';
$dsn = "oci:dbname=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SID=$sid)));charset=AL32UTF8";
try {
    // 오라클 데이터베이스에 연결
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo "PDO Exception" . $e->getMessage();
}
?>