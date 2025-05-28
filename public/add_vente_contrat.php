<?php
session_start();
require_once '../includes/db.php';
$config = require dirname(__DIR__) . '/config/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$partenariat = $_GET['partenariat'] ?? '';
if (!in_array($partenariat, $config['partenariats'])) {
    die('Partenariat inconnu.');
}

$revision_prices = $config['revision_prices'];
$contract_prices = $config['contract_prices'];
$partenaire_prices = $contract_prices[$partenariat] ?? null;

$form = [
    'date_vente' => '',
    'heure_vente' => '',
    'client' => '',
    'plaques' => '',
    'modele_vehicule' => '',
    'tarif' => '',
    'revision_items' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['date_vente'] = $_POST['date_vente'] ?? '';
    $form['heure_vente'] = $_POST['heure_vente'] ?? '';
    $form['client'] = trim($_POST['client'] ?? '');
    $form['plaques'] = trim($_POST['plaques'] ?? '');
    $form['modele_vehicule'] = $_POST['modele_vehicule'] ?? '';
    $form['tarif'] = $_POST['tarif'] ?? '';
    $form['revision_items'] = $_POST['revision_items'] ?? [];

    // Vérification des champs
    if (
        empty($form['date_vente']) ||
        empty($form['heure_vente']) ||
        empty($form['client']) ||
        empty($form['plaques']) ||
        empty($form['modele_vehicule']) ||
        empty($form['tarif']) ||
        !is_numeric($form['tarif']) || $form['tarif'] <= 0
    ) {
        $_SESSION['toast_error'] = "Merci de remplir correctement tous les champs.";
        header("Location: add_vente_contrat.php?partenariat=" . urlencode($partenariat));
        exit;
    } else {
        // Calcul du tarif total avec les options de révision
        $tarif = floatval($form['tarif']);
        foreach ($form['revision_items'] as $item) {
            if (isset($revision_prices[$item])) {
                $tarif += $revision_prices[$item];
            }
        }

        $stmt = $pdo->prepare("INSERT INTO ventes_contrat (partenariat, date_vente, heure_vente, client, plaques, tarif, modele_vehicule, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $partenariat,
            $form['date_vente'],
            $form['heure_vente'],
            $form['client'],
            $form['plaques'],
            $tarif,
            $form['modele_vehicule'],
            $_SESSION['user']['id']
        ]);

        // Log CSV
        $logFile = dirname(__DIR__) . '/logs/' . strtolower($partenariat) . '-log.csv';
        $isNewFile = !file_exists($logFile);
        $logRow = [
            date('Y-m-d'),
            date('H:i:s'),
            $_SESSION['user']['nom'],
            $form['client'],
            $form['plaques'],
            $form['modele_vehicule'],
            $tarif
        ];
        $fp = fopen($logFile, 'a');
        if ($isNewFile) {
            fputcsv($fp, ['Date', 'Heure', 'Employé', 'Client', 'Plaques', 'Modèle', 'Tarif'], ';');
        }
        fputcsv($fp, $logRow, ';');
        fclose($fp);

        $_SESSION['toast_success'] = "Vente ajoutée avec succès !";
        header("Location: ventes_contrat.php?partenariat=" . urlencode($partenariat));
        exit;
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="form-header">Ajouter une vente - <?= htmlspecialchars($partenariat) ?></h2>

<form method="post" class="add-vente-form">
    <label for="date_vente">Date :</label>
    <input type="date" id="date_vente" name="date_vente" value="<?= htmlspecialchars($form['date_vente']) ?>" required>

    <label for="heure_vente">Heure :</label>
    <input type="time" id="heure_vente" name="heure_vente" value="<?= htmlspecialchars($form['heure_vente']) ?>" required>

    <label for="client">Client :</label>
    <input type="text" id="client" name="client" value="<?= htmlspecialchars($form['client']) ?>" required>

    <label for="plaques">Plaques :</label>
    <input type="text" id="plaques" name="plaques" value="<?= htmlspecialchars($form['plaques']) ?>" required>

    <div class="checkbox-group">
        <h3>Options de Révision</h3>
        <?php foreach ($revision_prices as $item => $price): ?>
            <label>
                <input type="checkbox" name="revision_items[]" value="<?= htmlspecialchars($item) ?>"
                    data-price="<?= $price ?>"
                    <?= in_array($item, $form['revision_items']) ? 'checked' : '' ?>>
                <?= htmlspecialchars($item) ?> (+<?= htmlspecialchars($price) ?>$)
            </label>
        <?php endforeach; ?>
    </div>

    <label for="modele_vehicule">Modèle Véhicule :</label>
    <input type="text" id="modele_vehicule" name="modele_vehicule" value="<?= htmlspecialchars($form['modele_vehicule']) ?>" required>

    <label for="tarif">Tarif :</label>
    <select id="tarif" name="tarif" required>
        <option value="">-- Sélectionner un tarif --</option>
        <?php if ($partenaire_prices): ?>
            <option value="<?= $partenaire_prices['garage'] ?>" <?= $form['tarif'] == $partenaire_prices['garage'] ? 'selected' : '' ?>>
                Garage (<?= $partenaire_prices['garage'] ?> $)
            </option>
            <option value="<?= $partenaire_prices['terrain'] ?>" <?= $form['tarif'] == $partenaire_prices['terrain'] ? 'selected' : '' ?>>
                Terrain (<?= $partenaire_prices['terrain'] ?> $)
            </option>
            <option value="<?= $partenaire_prices['critique'] ?>" <?= $form['tarif'] == $partenaire_prices['critique'] ? 'selected' : '' ?>>
                Critique (<?= $partenaire_prices['critique'] ?> $)
            </option>
        <?php endif; ?>
    </select>

    <div style="text-align:center; margin-top:20px;">
        <button type="submit" class="btn-submit">Ajouter la vente</button>
        <button type="reset" class="btn-reset">Réinitialiser</button>
    </div>
</form>

<div class="back-button">
    <a href="ventes_contrat.php?partenariat=<?= urlencode($partenariat) ?>" class="btn-back">Retour</a>
</div>
<?php include '../includes/footer.php'; ?>