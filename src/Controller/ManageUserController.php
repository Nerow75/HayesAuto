<?php

namespace App\Controller;

use App\Core\Database;
use App\Core\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ManageUserController
{
    private $twig;
    private $pdo;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function index()
    {
        Session::start();

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patron') {
            header("Location: index.php");
            exit;
        }

        // Récupérer tous les utilisateurs
        $stmt = $this->pdo->prepare("SELECT id, nom, role FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Supprimer un utilisateur
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $this->handleDeleteUser($_GET['delete']);
        }

        // Modifier un utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
            $this->handleEditUser($_POST);
        }

        // Ajouter un utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
            $this->handleCreateUser($_POST);
        }

        // Chargement de l'utilisateur à modifier
        $edit_user = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_user = $this->getUserById($_GET['edit']);
        }

        echo $this->twig->render('manage_users.html.twig', [
            'users' => $users,
            'edit_user' => $edit_user,
            'current_user_id' => $_SESSION['user']['id']
        ]);
    }

    private function handleDeleteUser($userId)
    {
        // On vérifie le rôle de l'utilisateur à supprimer
        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $toDelete = $stmt->fetch();

        if ($toDelete && $toDelete['role'] !== 'patron' && $userId != $_SESSION['user']['id']) {
            $deleteStmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $deleteStmt->execute([$userId]);
            $_SESSION['toast_success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['toast_error'] = "Impossible de supprimer ce compte.";
        }

        header("Location: manage_users.php");
        exit;
    }

    private function handleEditUser($postData)
    {
        $edit_user_id = $postData['edit_user_id'];
        $edit_nom = $postData['edit_nom'] ?? '';
        $edit_password = $postData['edit_password'] ?? '';

        if (!empty($edit_nom)) {
            $updateStmt = $this->pdo->prepare("UPDATE users SET nom = ? WHERE id = ?");
            $updateStmt->execute([$edit_nom, $edit_user_id]);
        }

        if (!empty($edit_password)) {
            $hashed_password = password_hash($edit_password, PASSWORD_DEFAULT);
            $updatePasswordStmt = $this->pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
            $updatePasswordStmt->execute([$hashed_password, $edit_user_id]);
        }

        $_SESSION['toast_success'] = "Utilisateur modifié avec succès.";
        header("Location: manage_users.php");
        exit;
    }

    private function handleCreateUser($postData)
    {
        $new_nom = $postData['new_nom'] ?? '';
        $new_password = $postData['new_password'] ?? '';
        $new_role = $postData['new_role'] ?? 'employe';

        if (!empty($new_nom) && !empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $createStmt = $this->pdo->prepare("INSERT INTO users (nom, mot_de_passe, role) VALUES (?, ?, ?)");
            $createStmt->execute([$new_nom, $hashed_password, $new_role]);
            $_SESSION['toast_success'] = "Utilisateur créé avec succès.";
        } else {
            $_SESSION['toast_error'] = "Veuillez remplir tous les champs pour créer un utilisateur.";
        }

        header("Location: manage_users.php");
        exit;
    }

    private function getUserById($userId)
    {
        $stmt = $this->pdo->prepare("SELECT id, nom FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
