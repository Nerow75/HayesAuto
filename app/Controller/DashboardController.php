<?php

namespace App\Controller;

use Twig\Environment;

class DashboardController extends BaseController
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

        $employeeSales = $this->getEmployeeSales();
        $totalSales = array_sum(array_column($employeeSales, 'total_sales'));

        foreach ($employeeSales as $k => $employee) {
            $employeeSales[$k]['percentage'] = $totalSales > 0 ? round(($employee['total_sales'] / $totalSales) * 100, 2) : 0;
        }

        $revisionPrices = $this->config['revision_prices'];
        $contractPrices = $this->config['contract_prices'];

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

        $coffre = $this->db->query("SELECT nom_objet, quantite FROM coffre ORDER BY nom_objet ASC")->fetchAll(\PDO::FETCH_ASSOC);

        echo $this->twig->render('dashboard.html.twig', [
            'user' => $user,
            'employee_sales' => $employeeSales,
            'revision_prices' => $revisionPrices,
            'contract_prices' => $contractPrices,
            'history_lines' => $historyLines,
            'coffre' => $coffre,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    private function getEmployeeSales(): array
    {
        $stmt = $this->db->prepare("
            SELECT u.nom AS employee_name, SUM(v.tarif) AS total_sales, COUNT(v.id) AS nb_ventes
            FROM ventes v
            JOIN users u ON v.user_id = u.id
            GROUP BY u.nom
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
