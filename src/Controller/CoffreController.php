<?php

namespace App\Controller;

use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use Twig\Environment;

class CoffreController
{
    private $twig;
    private $pdo;
    private $csrf;
    private $config;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->pdo = Database::getInstance()->getPdo();
        $this->csrf = new Csrf();
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function index()
    {
        $user = Session::get('user');
        $pdo = $this->pdo;
        $coffre = $pdo->query("SELECT * FROM coffre ORDER BY nom_objet ASC")->fetchAll(\PDO::FETCH_ASSOC);

        // Seul le patron peut modifier les quantités
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'patron') {
            foreach ($_POST['quantites'] as $id => $quantite) {
                $quantite = max(0, (int)$quantite);
                // Récupère l'ancien stock et le nom de l'objet
                $stmt = $pdo->prepare("SELECT nom_objet, quantite as old_quantite FROM coffre WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row && $quantite != $row['old_quantite']) {
                    Logger::logCsv('coffre', [
                        'Date' => date('Y-m-d'),
                        'Heure' => date('H:i:s'),
                        'Employé' => $user['nom'],
                        'Objet' => $row['nom_objet'],
                        'Ancienne Quantité' => $row['old_quantite'],
                        'Nouvelle Quantité' => $quantite,
                        'Action' => 'modifiée'
                    ]);
                }
                $pdo->prepare("UPDATE coffre SET quantite = ? WHERE id = ?")->execute([$quantite, $id]);
            }
            header("Location: /coffre");
            exit;
        }

        echo $this->twig->render('coffre.html.twig', [
            'user' => $user,
            'coffre' => $coffre
        ]);
    }

    public function update()
    {
        if (Session::get('user_role') !== 'patron') {
            http_response_code(403);
            exit('Non autorisé');
        }

        if (!$this->csrf->validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Invalid CSRF token');
        }

        $id = (int)($_POST['id'] ?? 0);
        $quantite = (int)($_POST['quantite'] ?? 0);

        $stmt = $this->pdo->prepare("UPDATE coffre SET quantite = ? WHERE id = ?");
        $stmt->execute([$quantite, $id]);

        header('Location: /coffre');
        exit();
    }

    public function decrementItemsFromRepair(bool $isRevisionOnly, array $revisionTypes)
    {
        $user = Session::get('user');
        // Décrémente le kit de réparation si ce n'est pas uniquement révision
        if (!$isRevisionOnly) {
            if (isset($this->config['coffre_revision_map']['Kit de réparation'])) {
                $item = $this->config['coffre_revision_map']['Kit de réparation'];
                $stmt = $this->pdo->prepare("UPDATE coffre SET quantite = quantite - ? WHERE nom_technique = ? AND quantite >= ?");
                $stmt->execute([$item['quantite'], $item['objet'], $item['quantite']]);
                Logger::logCsv('coffre', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $user['nom'],
                    'Objet' => $item['objet'],
                    'Ancienne Quantité' => $item['quantite'],
                    'Nouvelle Quantité' => 0,
                    'Action' => 'sortie'
                ]);
            }
        }
        foreach ($revisionTypes as $label) {
            if (isset($this->config['coffre_revision_map'][$label])) {
                $item = $this->config['coffre_revision_map'][$label];
                $stmt = $this->pdo->prepare("UPDATE coffre SET quantite = quantite - ? WHERE nom_technique = ? AND quantite >= ?");
                $stmt->execute([$item['quantite'], $item['objet'], $item['quantite']]);

                Logger::logCsv('coffre', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $user['nom'],
                    'Objet' => $item['objet'],
                    'Ancienne Quantité' => $item['quantite'],
                    'Nouvelle Quantité' => 0,
                    'Action' => 'sortie'
                ]);
            }
        }
    }
}
