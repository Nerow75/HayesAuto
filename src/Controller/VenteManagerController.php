<?php

namespace App\Controller;

use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class VenteManagerController
{
    private $twig;
    private $pdo;
    private $csrf;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->pdo = Database::getInstance()->getPdo();
        $this->csrf = new Csrf();
    }

    public function handleRequest()
    {
        Session::start();

        if (!Session::get('user')) {
            header("Location: /");
            exit;
        }

        $action = $_GET['action'] ?? 'index';
        $type = $_GET['type'] ?? 'vente';
        $id = $_GET['id'] ?? null;
        $partenariat = $_GET['partenariat'] ?? null;

        switch ($action) {
            case 'add':
                $this->add($type, $partenariat);
                break;
            case 'edit':
                $this->edit($type, $id, $partenariat);
                break;
            case 'delete':
                $this->delete($type, $id, $partenariat);
                break;
            default:
                header("Location: /ventes");
                exit;
        }
    }

    private function add($type, $partenariat)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $vehicules = $this->getVehiculesFromCSV('assets/data/vehicules.csv');
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
        $vehicules = $this->getVehiculesFromCSV('assets/data/vehicules.csv');
        $revisionPrices = $config['revision_prices'];
        $contractPrices = $config['contract_prices'];

        $form = $this->getVenteById($type, $id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules, $id);
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


    private function delete($type, $id, $partenariat)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $vente = $this->getVenteById($type, $id);
            if (!$vente) {
                $_SESSION['toast_error'] = "Vente introuvable.";
                header("Location: " . ($type === 'contrat' ? "/ventes/contrat?partenariat=" . urlencode($partenariat) : "/ventes"));
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
            $this->logDeletion($type, $vente, $partenariat);
            $_SESSION['toast_success'] = "Vente supprimée avec succès.";
        } else {
            $_SESSION['toast_error'] = "Vente introuvable.";
        }

        header("Location: " . ($type === 'contrat' ? "/ventes/contrat?partenariat=" . urlencode($partenariat) : "/ventes"));
        exit;
    }

    private function getVehiculesFromCSV($filePath)
    {
        $vehicules = [];
        if (file_exists($filePath) && ($handle = fopen($filePath, "r")) !== false) {
            fgetcsv($handle);
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $vehicules[] = [
                    'brand' => $data[0],
                    'model' => $data[1],
                    'category' => $data[2],
                    'price_sell' => (float)$data[4],
                    'repair_price' => (float)$data[3]
                ];
            }
            fclose($handle);
        }
        return $vehicules;
    }

    private function getVenteById($type, $id)
    {
        $stmt = $this->pdo->prepare(
            $type === 'contrat'
                ? "SELECT * FROM ventes_contrat WHERE id = ?"
                : "SELECT * FROM ventes WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function processForm(&$form, $type, $partenariat, $revisionPrices, $contractPrices, $vehicules, $id = null)
    {
        $form['date_vente'] = $_POST['date_vente'] ?? '';
        $form['heure_vente'] = $_POST['heure_vente'] ?? '';
        $form['client'] = trim($_POST['client'] ?? '');
        $form['plaques'] = trim($_POST['plaques'] ?? '');
        $form['only_revision'] = isset($_POST['only_revision']);
        $form['modele_vehicule'] = $_POST['modele_vehicule'] ?? '';
        $form['tarif'] = $_POST['tarif'] ?? '';
        $form['revision_items'] = $_POST['revision_items'] ?? [];

        if (
            empty($form['date_vente']) ||
            empty($form['heure_vente']) ||
            empty($form['client']) ||
            empty($form['plaques']) ||
            empty($form['modele_vehicule']) ||
            empty($form['tarif']) ||
            !is_numeric($form['tarif']) || $form['tarif'] <= 0
        ) {
            $_SESSION['toast_error'] = "Merci de remplir correctement tous les champs.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $tarif = 0;

            if (!$form['only_revision']) {
                foreach ($vehicules as $vehicule) {
                    if ($vehicule['model'] === $_POST['modele_vehicule']) {
                        $tarif += (float)$vehicule['price_sell'];
                        break;
                    }
                }
            }

            if (!empty($_POST['revision_items'])) {
                foreach ($_POST['revision_items'] as $item) {
                    if (isset($revisionPrices[$item])) {
                        $tarif += (float)$revisionPrices[$item];
                    }
                }
            }

            if ($id) {
                $this->updateVente($type, $form, $tarif, $id);
            } else {
                $this->insertVente($type, $form, $tarif, $partenariat);
            }
            $this->logAction($type, $form, $id ? 'modifiée' : 'ajoutée', $partenariat);
            $_SESSION['toast_success'] = "Vente " . ($id ? "modifiée" : "ajoutée") . " avec succès !";
            header("Location: " . ($type === 'contrat' ? "/ventes/contrat?partenariat=" . urlencode($partenariat) : "/ventes"));
            exit;
        }
    }


    private function calculateTotalTarif($baseTarif, $revisionItems, $revisionPrices)
    {
        $tarif = floatval($baseTarif);
        foreach ($revisionItems as $item) {
            if (isset($revisionPrices[$item])) {
                $tarif += $revisionPrices[$item];
            }
        }
        return $tarif;
    }

    private function insertVente($type, $form, $tarif, $partenariat = null)
    {
        if ($type === 'contrat') {
            $stmt = $this->pdo->prepare("INSERT INTO ventes_contrat (partenariat, date_vente, heure_vente, client, plaques, tarif, modele_vehicule, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $partenariat,
                $form['date_vente'],
                $form['heure_vente'],
                $form['client'],
                $form['plaques'],
                $tarif,
                $form['modele_vehicule'],
                $_SESSION['user']['id']
            ]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO ventes (user_id, date_vente, heure_vente, client, plaques, tarif, modele_vehicule) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user']['id'],
                $form['date_vente'],
                $form['heure_vente'],
                $form['client'],
                $form['plaques'],
                $tarif,
                $form['modele_vehicule']
            ]);
        }
    }

    private function updateVente($type, $form, $tarif, $id)
    {
        $stmt = $this->pdo->prepare(
            $type === 'contrat'
                ? "UPDATE ventes_contrat SET date_vente = ?, heure_vente = ?, client = ?, plaques = ?, tarif = ?, modele_vehicule = ? WHERE id = ?"
                : "UPDATE ventes SET date_vente = ?, heure_vente = ?, client = ?, plaques = ?, tarif = ?, modele_vehicule = ? WHERE id = ?"
        );
        $stmt->execute([
            $form['date_vente'],
            $form['heure_vente'],
            $form['client'],
            $form['plaques'],
            $tarif,
            $form['modele_vehicule'],
            $id
        ]);
    }

    private function deleteVente($type, $id)
    {
        $stmt = $this->pdo->prepare(
            $type === 'contrat'
                ? "DELETE FROM ventes_contrat WHERE id = ?"
                : "DELETE FROM ventes WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    private function logAction($type, $form, $action, $partenariat = null)
    {
        $logFile = dirname(__DIR__, 2) . '/logs/' . ($type === 'contrat' ? strtolower($partenariat) . '-log.csv' : 'log-general.csv');
        $isNewFile = !file_exists($logFile);

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        $logRow = [
            date('Y-m-d'),
            date('H:i:s'),
            $_SESSION['user']['nom'],
            $form['client'],
            $form['plaques'],
            $form['modele_vehicule'],
            $form['tarif'],
            $action
        ];
        $fp = fopen($logFile, 'a');
        if ($fp) {
            if ($isNewFile) {
                fputcsv($fp, ['Date', 'Heure', 'Employé', 'Client', 'Plaques', 'Modèle', 'Tarif', 'Action'], ';');
            }
            fputcsv($fp, $logRow, ';');
            fclose($fp);
        }
    }

    private function logDeletion($type, $vente, $partenariat = null)
    {
        $logFile = dirname(__DIR__) . '/logs/other-log.txt';
        $logMessage = sprintf(
            "[%s %s] %s a SUPPRIMÉ la vente %s ID=%s : Client=%s, Plaques=%s, Modèle=%s, Tarif=%s, Date=%s, Heure=%s\n",
            date('Y-m-d'),
            date('H:i:s'),
            $_SESSION['user']['nom'],
            $type === 'contrat' ? 'CONTRAT (' . $partenariat . ')' : '',
            $vente['id'],
            $vente['client'],
            $vente['plaques'],
            $vente['modele_vehicule'],
            $vente['tarif'],
            $vente['date_vente'],
            $vente['heure_vente']
        );
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
