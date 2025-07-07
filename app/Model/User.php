<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class User
{
    public static function getAll(): array
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->query("SELECT id, nom, role FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("SELECT id, nom, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function getByName(string $nom): ?array
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("SELECT id, nom, password, role FROM users WHERE nom = ?");
        $stmt->execute([$nom]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getInstance()->getPdo();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function update(int $id, ?string $nom, ?string $password): void
    {
        $pdo = Database::getInstance()->getPdo();

        if (!empty($nom)) {
            $stmt = $pdo->prepare("UPDATE users SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);
        }

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $id]);
        }
    }

    public static function create(string $nom, string $password, string $role): void
    {
        $pdo = Database::getInstance()->getPdo();
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $hashed, $role]);
    }
}
