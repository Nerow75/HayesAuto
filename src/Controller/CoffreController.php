<?php

namespace App\Controller;

use App\Model\Coffre;
use Twig\Environment;

class CoffreController extends BaseController
{
    private Environment $twig;
    private array $config;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function index(): void
    {
        $user = $this->requireAuth();

        $coffreModel = new Coffre();
        $coffre = $coffreModel->findAll();

        if ($this->request->method() === 'POST' && $user['role'] === 'patron') {
            foreach ($this->request->post('quantites', []) as $id => $quantite) {
                $quantite = max(0, (int)$quantite);

                $item = $coffreModel->findById($id);
                if ($item && $quantite != $item['quantite']) {
                    $this->log('coffre', [
                        'Date' => date('Y-m-d'),
                        'Heure' => date('H:i:s'),
                        'Employé' => $user['nom'],
                        'Objet' => $item['nom_objet'],
                        'Ancienne Quantité' => $item['quantite'],
                        'Nouvelle Quantité' => $quantite,
                        'Action' => 'modifiée'
                    ]);
                }
                $coffreModel->updateQuantite($id, $quantite);
            }
            $this->redirect('/coffre');
        }

        echo $this->twig->render('coffre.html.twig', [
            'user' => $user,
            'coffre' => $coffre
        ]);
    }

    public function update(): void
    {
        $user = $this->session->get('user');

        if (!$user || $user['role'] !== 'patron') {
            http_response_code(403);
            exit('Non autorisé');
        }

        if (!$this->isCsrfValid($this->getPost('csrf_token', ''))) {
            http_response_code(403);
            exit('Invalid CSRF token');
        }

        $id = (int)$this->getPost('id', 0);
        $quantite = (int)$this->getPost('quantite', 0);

        (new Coffre())->updateQuantite($id, $quantite);
        $this->redirect('/coffre');
    }

    public function decrementItemsFromRepair(bool $isRevisionOnly, array $revisionTypes): void
    {
        $user = $this->session->get('user');
        $coffreModel = new Coffre();

        if (!$isRevisionOnly) {
            if (isset($this->config['coffre_revision_map']['Kit de réparation'])) {
                $item = $this->config['coffre_revision_map']['Kit de réparation'];
                $coffreModel->decrementQuantite($item['objet'], $item['quantite']);

                $this->log('coffre', [
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
                $coffreModel->decrementQuantite($item['objet'], $item['quantite']);

                $this->log('coffre', [
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
