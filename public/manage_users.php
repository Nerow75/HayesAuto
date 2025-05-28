<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est connecté et a le rôle "patron"
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patron') {
    header("Location: index.php");
    exit;
}

// Récupérer tous les utilisateurs
$stmt = $pdo->prepare("SELECT id, nom, role FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Supprimer un utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    // On vérifie le rôle de l'utilisateur à supprimer
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $toDelete = $stmt->fetch();
    if ($toDelete && $toDelete['role'] !== 'patron' && $_GET['delete'] != $_SESSION['user']['id']) {
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->execute([$_GET['delete']]);
        $_SESSION['toast_success'] = "Utilisateur supprimé avec succès.";
    } else {
        $_SESSION['toast_error'] = "Impossible de supprimer ce compte.";
    }
    header("Location: manage_users.php");
    exit;
}

// Modifier un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $edit_user_id = $_POST['edit_user_id'];
    $edit_nom = $_POST['edit_nom'] ?? '';
    $edit_password = $_POST['edit_password'] ?? '';

    if (!empty($edit_nom)) {
        $updateStmt = $pdo->prepare("UPDATE users SET nom = ? WHERE id = ?");
        $updateStmt->execute([$edit_nom, $edit_user_id]);
    }

    if (!empty($edit_password)) {
        $hashed_password = password_hash($edit_password, PASSWORD_DEFAULT);
        $updatePasswordStmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $updatePasswordStmt->execute([$hashed_password, $edit_user_id]);
    }

    $_SESSION['toast_success'] = "Utilisateur modifié avec succès.";
    header("Location: manage_users.php");
    exit;
}

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $new_nom = $_POST['new_nom'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_role = $_POST['new_role'] ?? 'employe';

    if (!empty($new_nom) && !empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $createStmt = $pdo->prepare("INSERT INTO users (nom, mot_de_passe, role) VALUES (?, ?, ?)");
        $createStmt->execute([$new_nom, $hashed_password, $new_role]);
        $_SESSION['toast_success'] = "Utilisateur créé avec succès.";
        header("Location: manage_users.php");
        exit;
    } else {
        $_SESSION['toast_error'] = "Veuillez remplir tous les champs pour créer un utilisateur.";
        header("Location: manage_users.php");
        exit;
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2 class="form-header">Gestion des utilisateurs</h2>

<!-- Tableau des utilisateurs -->
<table class="users-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td>
                    <a href="manage_users.php?edit=<?= $user['id'] ?>" class="action-link">Modifier</a>
                    <?php
                    // Empêcher la suppression si l'utilisateur est patron OU si c'est soi-même
                    if (
                        $user['id'] !== $_SESSION['user']['id'] &&
                        $user['role'] !== 'patron'
                    ): ?>
                        | <a href="manage_users.php?delete=<?= $user['id'] ?>" class="action-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Formulaire pour modifier un utilisateur -->
<?php if (isset($_GET['edit']) && is_numeric($_GET['edit'])): ?>
    <?php
    $edit_user_id = $_GET['edit'];
    $editStmt = $pdo->prepare("SELECT id, nom FROM users WHERE id = ?");
    $editStmt->execute([$edit_user_id]);
    $edit_user = $editStmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <?php if ($edit_user): ?>
        <h2 class="form-header">Modifier l'utilisateur</h2>
        <form method="post" class="edit-user-form">
            <input type="hidden" name="edit_user" value="1">
            <input type="hidden" name="edit_user_id" value="<?= $edit_user['id'] ?>">
            <label for="edit_nom">Nom :</label>
            <input type="text" id="edit_nom" name="edit_nom" value="<?= htmlspecialchars($edit_user['nom']) ?>" required>

            <label for="edit_password">Mot de passe :</label>
            <input type="password" id="edit_password" name="edit_password" placeholder="Nouveau mot de passe">

            <button type="submit" class="btn-submit">Modifier</button>
        </form>
    <?php endif; ?>
<?php endif; ?>

<!-- Formulaire pour créer un utilisateur -->
<h2 class="form-header">Créer un utilisateur</h2>
<form method="post" class="create-user-form">
    <input type="hidden" name="create_user" value="1">
    <label for="new_nom">Nom :</label>
    <input type="text" id="new_nom" name="new_nom" required>

    <label for="new_password">Mot de passe :</label>
    <input type="password" id="new_password" name="new_password" required>

    <label for="new_role">Rôle :</label>
    <select id="new_role" name="new_role">
        <option value="employe">Employé</option>
        <option value="patron">Patron</option>
    </select>

    <button type="submit" class="btn-submit">Créer</button>
</form>

<!-- Bouton pour retourner au tableau de bord -->
<div class="back-to-dashboard-container">
    <a href="dashboard.php" class="btn-back-dashboard">Retour au tableau de bord</a>
</div>

<?php include '../includes/footer.php'; ?>