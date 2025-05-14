<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM ventes WHERE user_id = ?");
$stmt->execute([$user_id]);
$ventes = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<h2 class="dashboard-header">
    <span>Réparations de <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
    <div class="action-buttons">
        <a href="add_vente.php" class="btn-add">Ajouter une réparation</a>
        <a href="dashboard.php" class="btn-back">Retour Dashboard</a>
    </div>
</h2>
<table>
    <tr>
        <th>Date</th>
        <th>Heure</th>
        <th>Client</th>
        <th>Plaques</th>
        <th>Tarif</th>
        <th>Modèle Véhicule</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($ventes as $vente): ?>
        <tr>
            <td>
                <?= htmlspecialchars(DateTime::createFromFormat('Y-m-d', $vente['date_vente'])->format('d/m/Y')) ?>
            </td>
            <td>
                <?= htmlspecialchars(substr($vente['heure_vente'], 0, 5)) ?>
            </td>
            <td><?= htmlspecialchars($vente['client']) ?></td>
            <td><?= htmlspecialchars($vente['plaques']) ?></td>
            <td><?= htmlspecialchars($vente['tarif']) ?> €</td>
            <td><?= htmlspecialchars($vente['modele_vehicule']) ?></td>
            <td>
                <a href="edit_vente.php?id=<?= $vente['id'] ?>">Modifier</a>
                <a href="delete_vente.php?id=<?= $vente['id'] ?>" onclick="return confirm('Êtes-vous sûr ?');">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>