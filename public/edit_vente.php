<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE ventes SET date_vente = ?, heure_vente = ?, client = ?, plaques = ?, tarif = ?, modele_vehicule = ? WHERE id = ?");
    $stmt->execute([
        $_POST['date_vente'],
        $_POST['heure_vente'],
        $_POST['client'],
        $_POST['plaques'],
        $_POST['tarif'],
        $_POST['modele_vehicule'],
        $id
    ]);
    header("Location: ventes.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM ventes WHERE id = ?");
$stmt->execute([$id]);
$vente = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>
<h2>Modifier une vente</h2>
<form method="post">
    <label for="date_vente">Date :</label>
    <input type="date" id="date_vente" name="date_vente" value="<?= $vente['date_vente'] ?>" required>

    <label for="heure_vente">Heure :</label>
    <input type="time" id="heure_vente" name="heure_vente" value="<?= $vente['heure_vente'] ?>" required>

    <label for="client">Client :</label>
    <input type="text" id="client" name="client" value="<?= $vente['client'] ?>" required>

    <label for="plaques">Plaques :</label>
    <input type="text" id="plaques" name="plaques" value="<?= $vente['plaques'] ?>" required>

    <label for="tarif">Tarif :</label>
    <input type="number" id="tarif" name="tarif" step="0.01" value="<?= $vente['tarif'] ?>" required>

    <label for="modele_vehicule">Modèle Véhicule :</label>
    <input type="text" id="modele_vehicule" name="modele_vehicule" value="<?= $vente['modele_vehicule'] ?>" required>

    <button type="submit">Modifier</button>
</form>
<?php include '../includes/footer.php'; ?>