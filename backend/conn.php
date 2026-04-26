<?php

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'spendtrackfinance';

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>