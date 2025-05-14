<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM ventes WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ventes.php");
exit;
