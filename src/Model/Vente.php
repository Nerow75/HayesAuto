<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class Vente
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ventes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        return $this->pdo->query("SELECT * FROM ventes")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO ventes (
                user_id,
                date_vente,
                heure_vente,
                client,
                plaques,
                tarif,
                modele_vehicule,
                revision_items
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['date_vente'],
            $data['heure_vente'],
            $data['client'],
            $data['plaques'],
            $data['tarif'],
            $data['modele_vehicule'],
            $data['revision_items']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE ventes SET
                date_vente = ?,
                heure_vente = ?,
                client = ?,
                plaques = ?,
                tarif = ?,
                modele_vehicule = ?,
                revision_items = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['date_vente'],
            $data['heure_vente'],
            $data['client'],
            $data['plaques'],
            $data['tarif'],
            $data['modele_vehicule'],
            $data['revision_items'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM ventes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByUserPaginated($userId, $perPage, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ventes WHERE user_id = ? ORDER BY date_vente DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByUser($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventes WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
