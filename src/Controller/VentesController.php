<?php

namespace App\Controller;

use App\Core\Database;
use App\Core\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class VentesController
{
    private $twig;
    private $pdo;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function index()
    {
        Session::start();

        if (!Session::get('user')) {
            header("Location: index.php");
            exit;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $type = $_GET['type'] ?? 'vente';
        $partenariat = $_GET['partenariat'] ?? null;

        if ($type === 'contrat' && $partenariat !== null && !in_array($partenariat, $config['partenariats'])) {
            die('Partenariat inconnu.');
        }


        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $ventes = [];
        $totalVentes = 0;

        if ($type === 'contrat') {
            if ($partenariat === null) {
                echo $this->twig->render('partenariats.html.twig', [
                    'user' => Session::get('user'),
                    'partenariats' => $config['partenariats']
                ]);
                return;
            }

            if (!in_array($partenariat, $config['partenariats'])) {
                die('Partenariat inconnu.');
            }

            $ventes = $this->getVentesContrat($partenariat, $perPage, $offset);
            $totalVentes = $this->getTotalVentesContrat($partenariat);
        } else {
            $userId = Session::get('user')['id'];
            $ventes = $this->getVentes($userId, $perPage, $offset);
            $totalVentes = $this->getTotalVentes($userId);
        }



        $totalPages = ceil($totalVentes / $perPage);

        echo $this->twig->render('ventes.html.twig', [
            'user' => Session::get('user'),
            'ventes' => $ventes,
            'type' => $type,
            'partenariat' => $partenariat,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }

    private function getVentes($userId, $perPage, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ventes WHERE user_id = ? ORDER BY date_vente DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTotalVentes($userId)
    {
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventes WHERE user_id = ?");
        $countStmt->execute([$userId]);
        return $countStmt->fetchColumn();
    }

    private function getVentesContrat($partenariat, $perPage, $offset)
    {
        $stmt = $this->pdo->prepare("
            SELECT vc.*, u.nom as employee_name
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

    private function getTotalVentesContrat($partenariat)
    {
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM ventes_contrat WHERE partenariat = ?");
        $countStmt->execute([$partenariat]);
        return $countStmt->fetchColumn();
    }
}
