<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$config = require dirname(__DIR__) . '/config/config.php';
$partenariat = $_GET['partenariat'] ?? '';
if (!in_array($partenariat, $config['partenariats'])) {
    die('Partenariat inconnu.');
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM ventes_contrat WHERE partenariat = ? ORDER BY date_vente DESC, heure_vente DESC LIMIT $perPage OFFSET $offset");
$stmt->execute([$partenariat]);
$ventes = $stmt->fetchAll();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM ventes_contrat WHERE partenariat = ?");
$countStmt->execute([$partenariat]);
$totalVentes = $countStmt->fetchColumn();
$totalPages = ceil($totalVentes / $perPage);
?>

<?php include '../includes/header.php'; ?>
<h2 class="ventes-header">
    <span>Fiche partenariat : <?= htmlspecialchars($partenariat) ?></span>
    <div class="action-buttons">
        <a href="add_vente_contrat.php?partenariat=<?= urlencode($partenariat) ?>" class="btn-add">Ajouter une vente</a>
        <a href="partenariats.php" class="btn-back">Retour Partenariats</a>
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
            <th>Employé</th>
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
                    <?php
                    $userStmt = $pdo->prepare("SELECT nom FROM users WHERE id = ?");
                    $userStmt->execute([$vente['user_id']]);
                    $user = $userStmt->fetch();
                    echo htmlspecialchars($user['nom'] ?? 'Inconnu');
                    ?>
                </td>
                <td>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'patron'): ?>
                        <a href="edit_vente_contrat.php?id=<?= $vente['id'] ?>&partenariat=<?= urlencode($partenariat) ?>" class="action-link">Modifier</a>
                        <a href="delete_vente_contrat.php?id=<?= $vente['id'] ?>&partenariat=<?= urlencode($partenariat) ?>" class="action-link">Supprimer</a>
                    <?php else: ?>
                        <span style="color:#aaa;font-style:italic;">Non autorisé</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?partenariat=<?= urlencode($partenariat) ?>&page=<?= $i ?>" class="page-link<?= $i == $page ? ' active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>

<?php include '../includes/footer.php'; ?>