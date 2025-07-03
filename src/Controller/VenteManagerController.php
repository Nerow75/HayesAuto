<?php

namespace App\Controller;

use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use App\Model\Vente;
use App\Model\Contrat;
use App\Model\Coffre;
use App\Model\Vehicule;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class VenteManagerController
{
    private $twig;
    private $csrf;
    private $venteModel;
    private $venteContratModel;
    private $coffreModel;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->venteModel = new Vente();
        $this->venteContratModel = new Contrat();
        $this->coffreModel = new Coffre();
        $this->csrf = new Csrf();
    }

    public function handleRequest()
    {
        Session::start();

        if (!Session::get('user')) {
            header("Location: /");
            exit;
        }

        $action = $_GET['action'] ?? 'add';
        $type = $_GET['type'] ?? 'classique';
        $id = $_GET['id'] ?? null;
        $partenariat = $_GET['partenariat'] ?? null;

        switch ($action) {
            case 'edit':
                $this->edit($type, $id, $partenariat);
                break;
            case 'delete':
                $this->delete($type, $id, $partenariat);
                break;
            default:
                $this->add($type, $partenariat);
                break;
        }
    }

    private function add($type, $partenariat)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $vehicules = Vehicule::getVehiculesFromCSV('assets/data/vehicules.csv');
        $revisionPrices = $config['revision_prices'];
        $contractPrices = $config['contract_prices'];

        $form = [
            'date_vente' => '',
            'heure_vente' => '',
            'client' => '',
            'plaques' => '',
            'modele_vehicule' => '',
            'tarif' => '',
            'revision_items' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules);
        }

        echo $this->twig->render('add_edit_vente.html.twig', [
            'form' => $form,
            'type' => $type,
            'partenariat' => $partenariat,
            'vehicules' => $vehicules,
            'revision_prices' => $revisionPrices,
            'contract_prices' => $contractPrices,
            'csrf_token' => $this->csrf->generateToken()
        ]);
    }

    private function edit($type, $id, $partenariat)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $vehicules = Vehicule::getVehiculesFromCSV('assets/data/vehicules.csv');
        $revisionPrices = $config['revision_prices'];
        $contractPrices = $config['contract_prices'];

        $form = $this->getVenteById($type, $id);

        if (isset($form['revision_items']) && !is_array($form['revision_items'])) {
            $form['revision_items'] = array_map('trim', explode(',', $form['revision_items']));
        } elseif (!isset($form['revision_items']) || !$form['revision_items']) {
            $form['revision_items'] = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules, $id);
        }

        echo $this->twig->render('add_edit_vente.html.twig', [
            'form' => $form,
            'type' => $type,
            'id' => $id,
            'partenariat' => $partenariat,
            'vehicules' => $vehicules,
            'revision_prices' => $revisionPrices,
            'contract_prices' => $contractPrices,
            'csrf_token' => $this->csrf->generateToken()
        ]);
    }


    private function delete($type, $id, $partenariat)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $vente = $this->getVenteById($type, $id);
            if (!$vente) {
                $_SESSION['toast_error'] = "Vente introuvable.";
                header("Location: " . ($type === 'contrat' ? "/ventes?type=contrat&partenariat=" . urlencode($partenariat) : "/ventes"));
                exit;
            }

            echo $this->twig->render('delete.html.twig', [
                'vente' => $vente,
                'type' => $type,
                'partenariat' => $partenariat,
                'csrf_token' => $this->csrf->generateToken()
            ]);
            exit;
        }

        $vente = $this->getVenteById($type, $id);
        if ($vente) {
            $this->deleteVente($type, $id);

            // Log la suppression
            if ($type === 'contrat') {
                Logger::logCsv('contrat', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $vente['client'],
                    'Plaques' => $vente['plaques'],
                    'Modèle' => $vente['modele_vehicule'],
                    'Tarif' => $vente['tarif'],
                    'Action' => 'supprimée'
                ], $partenariat);
            } else {
                Logger::logCsv('vente', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $vente['client'],
                    'Plaques' => $vente['plaques'],
                    'Modèle' => $vente['modele_vehicule'],
                    'Tarif' => $vente['tarif'],
                    'Action' => 'supprimée'
                ]);
            }

            $_SESSION['toast_success'] = "Vente supprimée avec succès.";
        } else {
            $_SESSION['toast_error'] = "Vente introuvable.";
        }

        header("Location: " . ($type === 'contrat' ? "/ventes?type=contrat&partenariat=" . urlencode($partenariat) : "/ventes"));
        exit;
    }

    private function getVenteById($type, $id)
    {
        if ($type === 'contrat') {
            return $this->venteContratModel->find($id);
        } else {
            return $this->venteModel->find($id);
        }
    }

    private function processForm(&$form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules, $id = null)
    {
        $config = require __DIR__ . '/../../config/config.php';

        // Récupération des champs du formulaire
        $form['date_vente'] = $_POST['date_vente'] ?? date('Y-m-d');
        $form['heure_vente'] = $_POST['heure_vente'] ?? date('H:i');
        $form['client'] = trim($_POST['client'] ?? '');
        $form['plaques'] = trim($_POST['plaques'] ?? '');
        $form['modele_vehicule'] = $_POST['modele_vehicule'] ?? '';
        $form['only_revision'] = isset($_POST['only_revision']);
        $form['revision_items'] = $_POST['revision_items'] ?? [];
        $revision_items_str = implode(',', $form['revision_items']);

        // Calcul du tarif
        // Pour tous les types
        $tarif = 0;
        if (!$form['only_revision']) {
            $tarif += Vehicule::getPrixVehicule($form['modele_vehicule'], $vehicules);
        }
        foreach ($form['revision_items'] as $item) {
            if (isset($revisionPrices[$item])) {
                $tarif += (float)$revisionPrices[$item];
            }
        }
        $form['tarif'] = $tarif;

        // Validation simple
        if (empty($form['client']) || empty($form['plaques']) || empty($form['modele_vehicule'])) {
            $_SESSION['toast_error'] = "Merci de remplir correctement tous les champs.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }

        $data = [
            'date_vente' => $form['date_vente'],
            'heure_vente' => $form['heure_vente'],
            'client' => $form['client'],
            'plaques' => $form['plaques'],
            'tarif' => $form['tarif'],
            'modele_vehicule' => $form['modele_vehicule'],
            'revision_items' => $revision_items_str,
            'user_id' => $_SESSION['user']['id'] // utile pour insert
        ];

        // Ajout ou modification de la vente
        if ($type === 'contrat') {
            $data['partenariat'] = $partenariat;
            if ($id) {
                $this->venteContratModel->update($id, $data);
                Logger::logCsv('contrat', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $form['client'],
                    'Plaques' => $form['plaques'],
                    'Modèle' => $form['modele_vehicule'],
                    'Tarif' => $form['tarif'],
                    'Révisions' => $revision_items_str,
                    'Action' => 'modifiée'
                ], $partenariat);
                $_SESSION['toast_success'] = "Vente modifiée avec succès !";
            } else {
                $this->venteContratModel->create($data);
                Logger::logCsv('contrat', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $form['client'],
                    'Plaques' => $form['plaques'],
                    'Modèle' => $form['modele_vehicule'],
                    'Tarif' => $form['tarif'],
                    'Révisions' => $revision_items_str,
                    'Action' => 'ajoutée'
                ], $partenariat);
                $_SESSION['toast_success'] = "Vente ajoutée avec succès !";
            }
        } else {
            if ($id) {
                $this->venteModel->update($id, $data);
                Logger::logCsv('vente', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $form['client'],
                    'Plaques' => $form['plaques'],
                    'Modèle' => $form['modele_vehicule'],
                    'Tarif' => $form['tarif'],
                    'Révisions' => $revision_items_str,
                    'Action' => 'modifiée'
                ]);
                $_SESSION['toast_success'] = "Vente modifiée avec succès !";
            } else {
                $this->venteModel->create($data);
                Logger::logCsv('vente', [
                    'Date' => date('Y-m-d'),
                    'Heure' => date('H:i:s'),
                    'Employé' => $_SESSION['user']['nom'],
                    'Client' => $form['client'],
                    'Plaques' => $form['plaques'],
                    'Modèle' => $form['modele_vehicule'],
                    'Tarif' => $form['tarif'],
                    'Révisions' => $revision_items_str,
                    'Action' => 'ajoutée'
                ]);
                $_SESSION['toast_success'] = "Vente ajoutée avec succès !";
            }
        }

        // --- Gestion du stock du coffre ---
        $coffreMap = $config['coffre_revision_map'];

        // Retirer le kit de réparation si ce n'est pas uniquement révision
        if (!$form['only_revision'] && isset($coffreMap['Kit de réparation'])) {
            $kit = $coffreMap['Kit de réparation'];
            $this->coffreModel->decrementQuantite($kit['objet'], $kit['quantite']);
        }

        // Toujours retirer les objets de révision cochés
        foreach ($form['revision_items'] as $itemLabel) {
            if (isset($coffreMap[$itemLabel])) {
                $objet = $coffreMap[$itemLabel]['objet'];
                $quantite = $coffreMap[$itemLabel]['quantite'];
                $this->coffreModel->decrementQuantite($objet, $quantite);
            }
        }

        // Redirection
        header("Location: " . ($type === 'contrat' ? "/ventes?type=contrat&partenariat=" . urlencode($partenariat) : "/ventes"));
        exit;
    }

    private function deleteVente($type, $id)
    {
        if ($type === 'contrat') {
            $this->venteContratModel->delete($id);
        } else {
            $this->venteModel->delete($id);
        }
    }
}
