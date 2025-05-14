<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Fréquence de la radio
$radio_frequency = "57 Mhz";

// Récupérer les ventes par employé
$stmt = $pdo->prepare("
    SELECT u.nom AS employee_name, COUNT(v.id) AS total_sales
    FROM ventes v
    JOIN users u ON v.user_id = u.id
    GROUP BY u.nom
");
$stmt->execute();
$employee_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le pourcentage des ventes
$total_sales = array_sum(array_column($employee_sales, 'total_sales'));
foreach ($employee_sales as &$employee) {
    $employee['percentage'] = round(($employee['total_sales'] / $total_sales) * 100, 2);
}

// Tarifs des éléments de révision
$revision_prices = [
    "Huile Moteur" => 150,
    "Filtre à air" => 200,
    "Bougies d'allumage" => 300,
    "Pneu" => 500,
    "Embrayage" => 400,
    "Plaquettes de frein" => 450,
    "Suspensions" => 400
];

// Prix des contrats
$contract_prices = [
    "Police" => 250,
    "EMS" => 250
];
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
        <ul>
            <?php foreach ($employee_sales as $employee): ?>
                <li>
                    <?= htmlspecialchars($employee['employee_name']) ?> :
                    <?= htmlspecialchars($employee['percentage']) ?>% -
                    <?= htmlspecialchars($employee['total_sales']) ?> $
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
                <li><?= htmlspecialchars($item) ?> : <?= htmlspecialchars($price) ?> €</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Prix des contrats -->
    <div class="dashboard-card">
        <h3>Prix des Contrats</h3>
        <ul>
            <?php foreach ($contract_prices as $company => $price): ?>
                <li><?= htmlspecialchars($company) ?> : <?= htmlspecialchars($price) ?> €</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php include '../includes/footer.php'; ?>