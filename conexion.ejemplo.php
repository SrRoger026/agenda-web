<?php
// Plantilla para el repositorio público
$host = "localhost";
$user = "agendaweb-php";
$pass = "CONTRASEÑA_AQUI";
$db   = "agendaweb_php_db";

$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset("utf8mb4");