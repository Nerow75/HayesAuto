<?php

namespace App\Model;

use App\Core\Database;
use PDO;

class Contrat
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ventes_contrat WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        return $this->pdo->query("SELECT * FROM ventes_contrat")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO ventes_contrat (user_id, partenariat, date_vente, heure_vente, client, plaques, tarif, modele_vehicule, revision_items) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['partenariat'],
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
            UPDATE ventes_contrat SET
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
        $stmt = $this->pdo->prepare("DELETE FROM contrats WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByPartenariatPaginated($partenariat, $perPage, $offset)
    {
        $stmt = $this->pdo->prepare("
        SELECT vc.*, u.nom as employe_nom
        FROM ventes_contrat vc
        LEFT JOIN users u ON vc.user_id = u.id
        WHERE vc.partenariat = ?
        ORDER BY vc.date_vente DESC, vc.heure_vente DESC
        LIMIT ? OFFSET ?
    ");
        $stmt->bindValue(1, $partenariat);
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByPartenariat($partenariat)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventes_contrat WHERE partenariat = ?");
        $stmt->execute([$partenariat]);
        return $stmt->fetchColumn();
    }
}
