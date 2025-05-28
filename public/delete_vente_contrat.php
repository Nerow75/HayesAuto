<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patron') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;
$partenariat = $_GET['partenariat'] ?? $_POST['partenariat'] ?? '';

if (!$id || !in_array($partenariat, ['LSPD', 'EMS'])) {
    die('Paramètres invalides.');
}

// Si ce n'est pas un POST, afficher la confirmation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Récupérer la vente pour affichage
    $stmt = $pdo->prepare("SELECT * FROM ventes_contrat WHERE id = ?");
    $stmt->execute([$id]);
    $vente = $stmt->fetch();
    if (!$vente) {
        $_SESSION['toast_error'] = "Vente introuvable.";
        header("Location: ventes_contrat.php?partenariat=" . urlencode($partenariat));
        exit;
    }
    include '../includes/header.php';
?>
    <div class="confirm-delete">
        <h2>Confirmation de suppression</h2>
        <p>Voulez-vous vraiment supprimer la vente du <strong><?= htmlspecialchars(DateTime::createFromFormat('Y-m-d', $vente['date_vente'])->format('d/m/Y')) ?></strong>
            pour <strong><?= htmlspecialchars($vente['client']) ?></strong> (Plaques : <strong><?= htmlspecialchars($vente['plaques']) ?></strong>) ?</p>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="partenariat" value="<?= htmlspecialchars($partenariat) ?>">
            <div class="confirm-actions">
                <button type="submit" class="btn-submit" style="background:#dc3545;">Supprimer définitivement</button>
                <a href="ventes_contrat.php?partenariat=<?= urlencode($partenariat) ?>" class="btn-back">Annuler</a>
            </div>
        </form>
    </div>
<?php
    include '../includes/footer.php';
    exit;
}

// Si POST, procéder à la suppression
$stmt = $pdo->prepare("SELECT * FROM ventes_contrat WHERE id = ?");
$stmt->execute([$id]);
$vente = $stmt->fetch();

if ($vente) {
    // Suppression
    $deleteStmt = $pdo->prepare("DELETE FROM ventes_contrat WHERE id = ?");
    $deleteStmt->execute([$id]);

    // Log
    $logFile = dirname(__DIR__) . '/logs/other-log.txt';
    $logMessage = sprintf(
        "[%s %s] %s a SUPPRIMÉ la vente CONTRAT ID=%s (%s) : Client=%s, Plaques=%s, Modèle=%s, Tarif=%s, Date=%s, Heure=%s\n",
        date('Y-m-d'),
        date('H:i:s'),
        $_SESSION['user']['nom'],
        $vente['id'],
        $partenariat,
        $vente['client'],
        $vente['plaques'],
        $vente['modele_vehicule'],
        $vente['tarif'],
        $vente['date_vente'],
        $vente['heure_vente']
    );
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    $_SESSION['toast_success'] = "Vente supprimée avec succès.";
} else {
    $_SESSION['toast_error'] = "Vente introuvable.";
}

header("Location: ventes_contrat.php?partenariat=" . urlencode($partenariat));
exit;
