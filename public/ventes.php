<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM ventes WHERE user_id = ? LIMIT $perPage OFFSET $offset");
$stmt->execute([$user_id]);
$ventes = $stmt->fetchAll();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM ventes WHERE user_id = ?");
$countStmt->execute([$user_id]);
$totalVentes = $countStmt->fetchColumn();
$totalPages = ceil($totalVentes / $perPage);
?>

<?php include '../includes/header.php'; ?>
<h2 class="ventes-header">
    <span>Réparations de <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
    <div class="action-buttons">
        <a href="add_vente.php" class="btn-add">Ajouter une réparation</a>
        <a href="dashboard.php" class="btn-back">Retour Dashboard</a>
    </div>
</h2>
<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Client</th>
            <th>Plaques</th>
            <th>Tarif</th>
            <th>Modèle Véhicule</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventes as $vente): ?>
            <tr>
                <td><?= htmlspecialchars(DateTime::createFromFormat('Y-m-d', $vente['date_vente'])->format('d/m/Y')) ?></td>
                <td><?= htmlspecialchars(substr($vente['heure_vente'], 0, 5)) ?></td>
                <td><?= htmlspecialchars($vente['client']) ?></td>
                <td><?= htmlspecialchars($vente['plaques']) ?></td>
                <td><?= htmlspecialchars($vente['tarif']) ?> $</td>
                <td><?= htmlspecialchars($vente['modele_vehicule']) ?></td>
                <td>
                    <a href="edit_vente.php?id=<?= $vente['id'] ?>" class="action-link">Modifier</a>
                    <a href="delete_vente.php?id=<?= $vente['id'] ?>" class="action-link">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="page-link<?= $i == $page ? ' active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>

<?php include '../includes/footer.php'; ?>