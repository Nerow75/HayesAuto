<?php

namespace App\Controller;

use App\Core\Database;
use App\Core\Csrf;
use App\Core\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DashboardController
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

    public function index()
    {
        Session::start();

        if (!Session::get('user')) {
            header("Location: index.php");
            exit;
        }

        $user = Session::get('user');
        $config = require __DIR__ . '/../../config/config.php';
        $pdo = Database::getInstance()->getPdo();

        $employeeSales = $this->getEmployeeSales();
        $totalSales = array_sum(array_column($employeeSales, 'total_sales'));

        foreach ($employeeSales as $k => $employee) {
            $employeeSales[$k]['percentage'] = $totalSales > 0 ? round(($employee['total_sales'] / $totalSales) * 100, 2) : 0;
        }

        $revisionPrices = $config['revision_prices'];
        $contractPrices = $config['contract_prices'];

        $logFile = dirname(__DIR__) . '/logs/other-log.txt';
        $historyLines = [];

        if (file_exists($logFile)) {
            $lines = array_reverse(array_slice(file($logFile), -4));
            foreach ($lines as $line) {
                $historyLines[] = trim($line);
            }
        } else {
            $historyLines[] = 'Aucune action récente.';
        }
        $coffre = $pdo->query("SELECT nom_objet, quantite FROM coffre ORDER BY nom_objet ASC")->fetchAll(\PDO::FETCH_ASSOC);


        echo $this->twig->render('dashboard.html.twig', [
            'user' => $user,
            'employee_sales' => $employeeSales,
            'revision_prices' => $revisionPrices,
            'contract_prices' => $contractPrices,
            'history_lines' => $historyLines,
            'coffre' => $coffre,
            'csrf_token' => $this->csrf->generateToken()
        ]);
    }

    private function getEmployeeSales()
    {
        $stmt = $this->pdo->prepare("
            SELECT u.nom AS employee_name, SUM(v.tarif) AS total_sales, COUNT(v.id) AS nb_ventes
            FROM ventes v
            JOIN users u ON v.user_id = u.id
            GROUP BY u.nom
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
