<?php

namespace App\Controller;

use App\Model\Vente;
use App\Model\Contrat;
use Twig\Environment;

class VentesController extends BaseController
{
    private Environment $twig;
    private Vente $venteModel;
    private Contrat $contratModel;
    private array $config;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->venteModel = new Vente();
        $this->contratModel = new Contrat();
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function index(): void
    {
        $user = $this->requireAuth();

        $type = $this->request->get('type', 'vente');
        $partenariat = $this->request->get('partenariat');
        $page = max(1, (int)$this->request->get('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        if ($type === 'contrat') {
            if ($partenariat === null) {
                echo $this->twig->render('partenariats.html.twig', [
                    'user' => $user,
                    'partenariats' => $this->config['partenariats']
                ]);
                return;
            }

            if (!in_array($partenariat, $this->config['partenariats'], true)) {
                http_response_code(400);
                exit('Partenariat inconnu.');
            }

            $ventes = $this->contratModel->findByPartenariatPaginated($partenariat, $perPage, $offset);
            $totalVentes = $this->contratModel->countByPartenariat($partenariat);
        } else {
            $ventes = $this->venteModel->findByUserPaginated($user['id'], $perPage, $offset);
            $totalVentes = $this->venteModel->countByUser($user['id']);
        }

        $totalPages = ceil($totalVentes / $perPage);

        echo $this->twig->render('ventes.html.twig', [
            'user' => $user,
            'ventes' => $ventes,
            'type' => $type,
            'partenariat' => $partenariat,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]);
    }
}
