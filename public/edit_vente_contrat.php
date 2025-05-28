<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patron') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$partenariat = $_GET['partenariat'] ?? '';
if (!$id || !in_array($partenariat, ['LSPD', 'EMS'])) {
    die('Paramètres invalides.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'ancienne vente pour le log
    $stmt = $pdo->prepare("SELECT * FROM ventes_contrat WHERE id = ?");
    $stmt->execute([$id]);
    $oldVente = $stmt->fetch();

    $client = trim($_POST['client'] ?? '');
    $plaques = trim($_POST['plaques'] ?? '');
    $modele_vehicule = $_POST['modele_vehicule'] ?? '';
    $tarif = $_POST['tarif'] ?? '';
    $date_vente = $_POST['date_vente'] ?? '';
    $heure_vente = $_POST['heure_vente'] ?? '';

    if (
        empty($client) ||
        empty($plaques) ||
        empty($modele_vehicule) ||
        empty($tarif) ||
        empty($date_vente) ||
        empty($heure_vente) ||
        !is_numeric($tarif) || $tarif <= 0
    ) {
        $_SESSION['toast_error'] = "Merci de remplir correctement tous les champs.";
        header("Location: edit_vente_contrat.php?id=$id&partenariat=" . urlencode($partenariat));
        exit;
    } else {
        $updateStmt = $pdo->prepare("UPDATE ventes_contrat SET client=?, plaques=?, modele_vehicule=?, tarif=?, date_vente=?, heure_vente=? WHERE id=?");
        $updateStmt->execute([
            $client,
            $plaques,
            $modele_vehicule,
            $tarif,
            $date_vente,
            $heure_vente,
            $id
        ]);

        // Log
        $logFile = dirname(__DIR__) . '/logs/other-log.txt';
        $logMessage = sprintf(
            "[%s %s] %s a MODIFIÉ la vente CONTRAT ID=%s (%s) : AVANT [Client=%s, Plaques=%s, Modèle=%s, Tarif=%s, Date=%s, Heure=%s] APRÈS [Client=%s, Plaques=%s, Modèle=%s, Tarif=%s, Date=%s, Heure=%s]\n",
            date('Y-m-d'),
            date('H:i:s'),
            $_SESSION['user']['nom'],
            $id,
            $partenariat,
            $oldVente['client'],
            $oldVente['plaques'],
            $oldVente['modele_vehicule'],
            $oldVente['tarif'],
            $oldVente['date_vente'],
            $oldVente['heure_vente'],
            $client,
            $plaques,
            $modele_vehicule,
            $tarif,
            $date_vente,
            $heure_vente
        );
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        $_SESSION['toast_success'] = "Vente partenariat modifiée avec succès !";
        header("Location: ventes_contrat.php?partenariat=" . urlencode($partenariat));
        exit;
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM ventes_contrat WHERE id = ?");
    $stmt->execute([$id]);
    $vente = $stmt->fetch();
}

include '../includes/header.php';
?>
<h2 class="form-header">Modifier une vente partenariat (<?= htmlspecialchars($partenariat) ?>)</h2>
<form method="post" class="edit-vente-form">
    <label for="date_vente">Date :</label>
    <input type="date" id="date_vente" name="date_vente" value="<?= htmlspecialchars($vente['date_vente']) ?>" required>

    <label for="heure_vente">Heure :</label>
    <input type="time" id="heure_vente" name="heure_vente" value="<?= htmlspecialchars($vente['heure_vente']) ?>" required>

    <label for="client">Client :</label>
    <input type="text" id="client" name="client" value="<?= htmlspecialchars($vente['client']) ?>" required>

    <label for="plaques">Plaques :</label>
    <input type="text" id="plaques" name="plaques" value="<?= htmlspecialchars($vente['plaques']) ?>" required>

    <label for="tarif">Tarif :</label>
    <input type="text" id="tarif" name="tarif" value="<?= htmlspecialchars($vente['tarif']) ?>" required>

    <label for="modele_vehicule">Modèle Véhicule :</label>
    <input type="text" id="modele_vehicule" name="modele_vehicule" value="<?= htmlspecialchars($vente['modele_vehicule']) ?>" required>

    <div style="text-align:center; margin-top:20px;">
        <button type="submit" class="btn-submit">Modifier</button>
        <button type="reset" class="btn-reset">Réinitialiser</button>
    </div>
</form>
<div class="back-button">
    <a href="ventes_contrat.php?partenariat=<?= urlencode($partenariat) ?>" class="btn-back">Retour</a>
</div>
<?php include '../includes/footer.php'; ?>