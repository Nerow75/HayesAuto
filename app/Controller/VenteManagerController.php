<?php

namespace App\Controller;

use App\Model\Vente;
use App\Model\Contrat;
use App\Model\Coffre;
use App\Model\Vehicule;
use Twig\Environment;

class VenteManagerController extends BaseController
{
    private Environment $twig;
    private Vente $venteModel;
    private Contrat $venteContratModel;
    private Coffre $coffreModel;
    private array $config;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->venteModel = new Vente();
        $this->venteContratModel = new Contrat();
        $this->coffreModel = new Coffre();
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function handleRequest(): void
    {
        if (!$this->session->get('user')) {
            $this->redirect('/');
        }

        $action = $this->request->get('action', 'add');
        $type = $this->request->get('type', 'classique');
        $id = $this->request->get('id');
        $partenariat = $this->request->get('partenariat');

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

    private function add($type, $partenariat): void
    {
        $vehicules = Vehicule::getVehiculesFromCSV('assets/data/vehicules.csv');
        $revisionPrices = $this->config['revision_prices'];
        $contractPrices = $this->config['contract_prices'];

        $form = [
            'date_vente' => '',
            'heure_vente' => '',
            'client' => '',
            'plaques' => '',
            'modele_vehicule' => '',
            'tarif' => '',
            'revision_items' => []
        ];

        if ($this->request->method() === 'POST') {
            $this->processForm($form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules);
        }

        echo $this->twig->render('add_edit_vente.html.twig', [
            'form' => $form,
            'type' => $type,
            'partenariat' => $partenariat,
            'vehicules' => $vehicules,
            'revision_prices' => $revisionPrices,
            'contract_prices' => $contractPrices,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    private function edit($type, $id, $partenariat): void
    {
        $vehicules = Vehicule::getVehiculesFromCSV('assets/data/vehicules.csv');
        $revisionPrices = $this->config['revision_prices'];
        $contractPrices = $this->config['contract_prices'];

        $form = $this->getVenteById($type, $id);

        $form['revision_items'] = isset($form['revision_items']) && is_string($form['revision_items'])
            ? array_map('trim', explode(',', $form['revision_items']))
            : [];

        if ($this->request->method() === 'POST') {
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
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    private function delete($type, $id, $partenariat): void
    {
        if ($this->request->method() !== 'POST') {
            $vente = $this->getVenteById($type, $id);
            if (!$vente) {
                $this->session->set('toast_error', "Vente introuvable.");
                $this->redirect($type === 'contrat' ? "/ventes?type=contrat&partenariat=$partenariat" : "/ventes");
            }

            echo $this->twig->render('delete.html.twig', [
                'vente' => $vente,
                'type' => $type,
                'partenariat' => $partenariat,
                'csrf_token' => $this->generateCsrfToken()
            ]);
            exit;
        }

        $vente = $this->getVenteById($type, $id);
        if ($vente) {
            $this->deleteVente($type, $id);
            $logData = [
                'Date' => date('Y-m-d'),
                'Heure' => date('H:i:s'),
                'Employé' => $this->session->get('user')['nom'],
                'Client' => $vente['client'],
                'Plaques' => $vente['plaques'],
                'Modèle' => $vente['modele_vehicule'],
                'Tarif' => $vente['tarif'],
                'Action' => 'supprimée'
            ];
            $this->log($type === 'contrat' ? 'contrat' : 'vente', $logData, $partenariat);
            $this->session->set('toast_success', "Vente supprimée avec succès.");
        } else {
            $this->session->set('toast_error', "Vente introuvable.");
        }

        $this->redirect($type === 'contrat' ? "/ventes?type=contrat&partenariat=$partenariat" : "/ventes");
    }

    private function processForm(&$form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules, $id = null): void
    {
        $form['date_vente'] = $this->getPost('date_vente', date('Y-m-d'));
        $form['heure_vente'] = $this->getPost('heure_vente', date('H:i'));
        $form['client'] = trim($this->getPost('client', ''));
        $form['plaques'] = trim($this->getPost('plaques', ''));
        $form['modele_vehicule'] = $this->getPost('modele_vehicule', '');
        $form['only_revision'] = $this->request->post('only_revision') ? true : false;
        $form['revision_items'] = $this->request->post('revision_items', []);

        $tarif = !$form['only_revision'] ? Vehicule::getPrixVehicule($form['modele_vehicule'], $vehicules) : 0;
        foreach ($form['revision_items'] as $item) {
            $tarif += $revisionPrices[$item] ?? 0;
        }
        $form['tarif'] = $tarif;
        $revision_items_str = implode(',', $form['revision_items']);

        if (empty($form['client']) || empty($form['plaques']) || empty($form['modele_vehicule'])) {
            $this->session->set('toast_error', "Merci de remplir correctement tous les champs.");
            $this->redirect($_SERVER['REQUEST_URI']);
        }

        $data = [
            'date_vente' => $form['date_vente'],
            'heure_vente' => $form['heure_vente'],
            'client' => $form['client'],
            'plaques' => $form['plaques'],
            'tarif' => $form['tarif'],
            'modele_vehicule' => $form['modele_vehicule'],
            'revision_items' => $revision_items_str,
            'user_id' => $this->session->get('user')['id']
        ];

        $logData = [
            'Date' => date('Y-m-d'),
            'Heure' => date('H:i:s'),
            'Employé' => $this->session->get('user')['nom'],
            'Client' => $form['client'],
            'Plaques' => $form['plaques'],
            'Modèle' => $form['modele_vehicule'],
            'Tarif' => $form['tarif'],
            'Révisions' => $revision_items_str,
            'Action' => $id ? 'modifiée' : 'ajoutée'
        ];

        if ($type === 'contrat') {
            $data['partenariat'] = $partenariat;
            $id ? $this->venteContratModel->update($id, $data) : $this->venteContratModel->create($data);
            $this->log('contrat', $logData, $partenariat);
        } else {
            $id ? $this->venteModel->update($id, $data) : $this->venteModel->create($data);
            $this->log('vente', $logData);
        }

        $coffreMap = $this->config['coffre_revision_map'];

        if (!$form['only_revision'] && isset($coffreMap['Kit de réparation'])) {
            $kit = $coffreMap['Kit de réparation'];
            $this->coffreModel->decrementQuantite($kit['objet'], $kit['quantite']);
        }

        foreach ($form['revision_items'] as $itemLabel) {
            if (isset($coffreMap[$itemLabel])) {
                $objet = $coffreMap[$itemLabel]['objet'];
                $quantite = $coffreMap[$itemLabel]['quantite'];
                $this->coffreModel->decrementQuantite($objet, $quantite);
            }
        }

        $this->session->set('toast_success', $id ? "Vente modifiée avec succès !" : "Vente ajoutée avec succès !");
        $this->redirect($type === 'contrat' ? "/ventes?type=contrat&partenariat=$partenariat" : "/ventes");
    }

    private function getVenteById($type, $id)
    {
        return $type === 'contrat' ? $this->venteContratModel->find($id) : $this->venteModel->find($id);
    }

    private function deleteVente($type, $id): void
    {
        $type === 'contrat' ? $this->venteContratModel->delete($id) : $this->venteModel->delete($id);
    }
}
