<?php

namespace App\Controller;

use App\Core\Session;
use App\Model\User;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ManageUserController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
    }

    public function index()
    {
        Session::start();

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patron') {
            header("Location: index.php");
            exit;
        }

        // Supprimer un utilisateur
        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $this->handleDeleteUser((int) $_GET['delete']);
        }

        // Modifier un utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
            $this->handleEditUser($_POST);
        }

        // Ajouter un utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
            $this->handleCreateUser($_POST);
        }

        $edit_user = null;
        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
            $edit_user = User::getById((int) $_GET['edit']);
        }

        $users = User::getAll();

        echo $this->twig->render('manage_users.html.twig', [
            'users' => $users,
            'edit_user' => $edit_user,
            'current_user_id' => $_SESSION['user']['id']
        ]);
    }

    private function handleDeleteUser(int $userId)
    {
        $toDelete = User::getById($userId);

        if ($toDelete && $toDelete['role'] !== 'patron' && $userId !== $_SESSION['user']['id']) {
            User::delete($userId);
            $_SESSION['toast_success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['toast_error'] = "Impossible de supprimer ce compte.";
        }

        header("Location: manage-users");
        exit;
    }

    private function handleEditUser(array $data)
    {
        $id = $data['edit_user_id'];
        $nom = $data['edit_nom'] ?? null;
        $password = $data['edit_password'] ?? null;

        if ($nom || $password) {
            User::update($id, $nom, $password);
            $_SESSION['toast_success'] = "Utilisateur modifié avec succès.";
        } else {
            $_SESSION['toast_error'] = "Aucune donnée à modifier.";
        }

        header("Location: manage-users");
        exit;
    }

    private function handleCreateUser(array $data)
    {
        $nom = $data['new_nom'] ?? '';
        $password = $data['new_password'] ?? '';
        $role = $data['new_role'] ?? 'employe';

        if (!empty($nom) && !empty($password)) {
            User::create($nom, $password, $role);
            $_SESSION['toast_success'] = "Utilisateur créé avec succès.";
        } else {
            $_SESSION['toast_error'] = "Veuillez remplir tous les champs pour créer un utilisateur.";
        }

        header("Location: manage-users");
        exit;
    }
}
