<?php
session_start();
require_once '../includes/db.php';
$config = require dirname(__DIR__) . '/config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}


// Récupérer les ventes par employé
$stmt = $pdo->prepare("
    SELECT u.nom AS employee_name, SUM(v.tarif) AS total_sales, COUNT(v.id) AS nb_ventes
    FROM ventes v
    JOIN users u ON v.user_id = u.id
    GROUP BY u.nom
");
$stmt->execute();
$employee_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le pourcentage des ventes
$total_sales = array_sum(array_column($employee_sales, 'total_sales'));
foreach ($employee_sales as $k => $employee) {
    $employee_sales[$k]['percentage'] = $total_sales > 0 ? round(($employee['total_sales'] / $total_sales) * 100, 2) : 0;
}

$revision_prices = $config['revision_prices'];
$contract_prices = $config['contract_prices'];
$radio_frequency = $config['radio_frequency'];
?>

<?php include '../includes/header.php'; ?>
<h2 class="dashboard-title">Tableau de Bord</h2>
<?php if ($_SESSION['user']['role'] === 'patron'): ?>
    <div class="manage-users-container">
        <a href="manage_users.php" class="btn-manage-users">Gérer les utilisateurs</a>
    </div>
<?php endif; ?>
<div class="dashboard-container">
    <!-- Fréquence de la radio -->
    <div class="dashboard-card">
        <h3>Fréquence Radio</h3>
        <p><?= htmlspecialchars($radio_frequency) ?></p>
    </div>

    <!-- Répartition des ventes -->
    <div class="dashboard-card">
        <h3>Répartition des Ventes</h3>
        <ul class="sales-distribution-list">
            <?php foreach ($employee_sales as $employee): ?>
                <li>
                    <strong><?= htmlspecialchars($employee['employee_name']) ?></strong> :
                    <?= htmlspecialchars($employee['percentage']) ?> % —
                    <?= number_format($employee['total_sales'], 2, ',', ' ') ?> $
                    (<?= htmlspecialchars($employee['nb_ventes']) ?> ventes)
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="ventes.php" class="btn-view-sales">Voir mes ventes</a>
    </div>

    <!-- Tarifs des éléments de révision -->
    <div class="dashboard-card">
        <h3>Tarifs Révision</h3>
        <ul>
            <?php foreach ($revision_prices as $item => $price): ?>
                <li><?= htmlspecialchars($item) ?> : <?= htmlspecialchars($price) ?> $</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Prix des contrats -->
    <div class="dashboard-card">
        <h3>Prix des Contrats</h3>
        <ul>
            <?php foreach ($contract_prices as $company => $types): ?>
                <li>
                    <strong><?= htmlspecialchars($company) ?></strong> :
                    Garage : <?= htmlspecialchars($types['garage']) ?> $ |
                    Terrain : <?= htmlspecialchars($types['terrain']) ?> $ |
                    Critique : <?= htmlspecialchars($types['critique']) ?> $
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($_SESSION['user']['role'] === 'patron'): ?>
        <div class="dashboard-card">
            <h3>Historique des actions</h3>
            <ul class="history-list">
                <?php
                $logFile = dirname(__DIR__) . '/logs/other-log.txt';
                if (file_exists($logFile)) {
                    $lines = array_reverse(array_slice(file($logFile), -4));
                    foreach ($lines as $line) {
                        echo '<li>' . htmlspecialchars(trim($line)) . '</li>';
                    }
                } else {
                    echo '<li>Aucune action récente.</li>';
                }
                ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>