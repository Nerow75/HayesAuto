<?php

namespace App\Controller;

use App\Core\Session;
use App\Model\Vente;
use App\Model\Contrat;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class VentesController
{
    private $twig;
    private $venteModel;
    private $contratModel;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->venteModel = new Vente();
        $this->contratModel = new Contrat();
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
        return $this->venteModel->findByUserPaginated($userId, $perPage, $offset);
    }

    private function getTotalVentes($userId)
    {
        return $this->venteModel->countByUser($userId);
    }

    private function getVentesContrat($partenariat, $perPage, $offset)
    {
        return $this->contratModel->findByPartenariatPaginated($partenariat, $perPage, $offset);
    }

    private function getTotalVentesContrat($partenariat)
    {
        return $this->contratModel->countByPartenariat($partenariat);
    }
}
