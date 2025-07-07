<?php

namespace App\Model;

use App\Core\Database;

class Coffre
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM coffre ORDER BY nom_objet ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT nom_objet, quantite FROM coffre WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateQuantite($id, $quantite)
    {
        $stmt = $this->pdo->prepare("UPDATE coffre SET quantite = ? WHERE id = ?");
        return $stmt->execute([$quantite, $id]);
    }

    public function decrementQuantite($nomTechnique, $quantite)
    {
        $stmt = $this->pdo->prepare("UPDATE coffre SET quantite = quantite - ? WHERE nom_technique = ? AND quantite >= ?");
        return $stmt->execute([$quantite, $nomTechnique, $quantite]);
    }
}
