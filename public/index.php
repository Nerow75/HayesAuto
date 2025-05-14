<?php
session_start();
require_once '../includes/db.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE nom = ?");
    $stmt->execute([$nom]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2>Connexion</h2>
<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post">
    <label for="nom">Nom d'utilisateur :</label>
    <input type="text" id="nom" name="nom" required>

    <label for="mot_de_passe">Mot de passe :</label>
    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

    <button type="submit">Se connecter</button>
</form>
<?php include '../includes/footer.php'; ?>