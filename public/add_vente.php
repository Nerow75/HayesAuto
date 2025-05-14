<?php
session_start();
require_once '../includes/db.php';

function getVehiculesFromCSV($filePath)
{
    $vehicules = [];
    if (($handle = fopen($filePath, "r")) !== false) {
        // Lire la première ligne (en-têtes)
        fgetcsv($handle);
        // Lire les données
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $vehicules[] = [
                'brand' => $data[0],
                'model' => $data[1],
                'category' => $data[2],
                'price_sell' => (float)$data[4], // Prix de vente
                'repair_price' => (float)$data[3] // Prix de la réparation
            ];
        }
        fclose($handle);
    }
    return $vehicules;
}

// Charger les véhicules depuis le fichier CSV
$vehicules = getVehiculesFromCSV('../assets/data/vehicules.csv');

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation du tarif
    $tarif = filter_var($_POST['tarif'], FILTER_VALIDATE_FLOAT);
    if ($tarif === false || $tarif <= 0) {
        die("Le tarif doit être un nombre valide supérieur à 0.");
    }

    $stmt = $pdo->prepare("INSERT INTO ventes (user_id, date_vente, heure_vente, client, plaques, tarif, modele_vehicule) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user']['id'],
        $_POST['date_vente'],
        $_POST['heure_vente'],
        $_POST['client'],
        $_POST['plaques'],
        $tarif, // Tarif modifiable
        $_POST['modele_vehicule']
    ]);
    header("Location: ventes.php");
    exit;
}

include '../includes/header.php'; ?>
<h2 class="form-header">Ajouter une vente</h2>
<form method="post">
    <label for="date_vente">Date :</label>
    <input type="date" id="date_vente" name="date_vente" required>

    <label for="heure_vente">Heure :</label>
    <input type="time" id="heure_vente" name="heure_vente" required>

    <label for="client">Client :</label>
    <input type="text" id="client" name="client" required>

    <label for="plaques">Plaques :</label>
    <input type="text" id="plaques" name="plaques" required>

    <label for="search_modele">Rechercher un modèle :</label>
    <input type="text" id="search_modele" placeholder="Rechercher un modèle..." onkeyup="filterVehicules()">

    <label for="modele_vehicule">Modèle Véhicule :</label>
    <select id="modele_vehicule" name="modele_vehicule" required onchange="updateRepairPrice(this)">
        <option value="" disabled selected>-- Sélectionnez un modèle --</option>
        <?php foreach ($vehicules as $vehicule): ?>
            <option value="<?= htmlspecialchars($vehicule['model']) ?>"
                data-price-sell="<?= $vehicule['price_sell'] ?>">
                <?= htmlspecialchars($vehicule['brand']) ?> <?= htmlspecialchars($vehicule['model']) ?> - <?= htmlspecialchars($vehicule['category']) ?> - <?= number_format($vehicule['price_sell'], 2) ?> €
            </option>
        <?php endforeach; ?>
    </select>

    <input type="hidden" id="repair_price" name="repair_price">

    <label for="tarif">Tarif :</label>
    <input type="text" id="tarif" name="tarif" required>

    <button type="submit" class="btn-center">Ajouter une vente</button>
</form>

<div class="back-button">
    <a href="ventes.php" class="btn-back">Retour</a>
</div>

<script src="../assets/js/add_vente.js"></script>
<?php include '../includes/footer.php'; ?>