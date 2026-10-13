<?php
// Plantilla para el repositorio público
$host = "localhost";
$user = "root";
$pass = "12345678";
$db   = "agenda";

$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset("utf8mb4");