<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
$usuarioId = (int)$_SESSION['uid'];

require __DIR__ . '/conexion.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $mysqli->prepare('DELETE FROM eventos WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $usuarioId);
    $stmt->execute();
    $stmt->close();
}

header('Location: index.php');
exit;