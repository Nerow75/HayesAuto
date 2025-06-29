<?php

namespace App\Controller;

use App\Model\Coffre;
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
        $this->csrf = new Csrf();
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function index()
    {
        $user = Session::get('user');
        $coffreModel = new Coffre();
        $coffre = $coffreModel->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'patron') {
            foreach ($_POST['quantites'] as $id => $quantite) {
                $quantite = max(0, (int)$quantite);

                $item = $coffreModel->findById($id);
                if ($item && $quantite != $item['quantite']) {
                    Logger::logCsv('coffre', [
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

        (new Coffre())->updateQuantite($id, $quantite);
        header('Location: /coffre');
        exit();
    }

    public function decrementItemsFromRepair(bool $isRevisionOnly, array $revisionTypes)
    {
        $user = Session::get('user');
        $coffreModel = new Coffre();

        if (!$isRevisionOnly) {
            if (isset($this->config['coffre_revision_map']['Kit de réparation'])) {
                $item = $this->config['coffre_revision_map']['Kit de réparation'];
                $coffreModel->decrementQuantite($item['objet'], $item['quantite']);

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
                $coffreModel->decrementQuantite($item['objet'], $item['quantite']);

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
