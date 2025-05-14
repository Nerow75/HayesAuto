<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hayesauto;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
