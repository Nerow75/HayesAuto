<?php

namespace App\Controller;

use App\Model\User;
use Twig\Environment;

class ManageUserController extends BaseController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
    }

    public function index(): void
    {
        $user = $this->session->get('user');

        if (!$user || $user['role'] !== 'patron') {
            $this->redirect('/');
        }

        // Supprimer un utilisateur
        if ($this->request->get('delete') && is_numeric($this->request->get('delete'))) {
            $this->handleDeleteUser((int)$this->request->get('delete'));
        }

        // Modifier un utilisateur
        if ($this->request->method() === 'POST' && $this->request->post('edit_user')) {
            $this->handleEditUser();
        }

        // Ajouter un utilisateur
        if ($this->request->method() === 'POST' && $this->request->post('create_user')) {
            $this->handleCreateUser();
        }

        $edit_user = null;
        if ($this->request->get('edit') && is_numeric($this->request->get('edit'))) {
            $edit_user = User::getById((int)$this->request->get('edit'));
        }

        $users = User::getAll();

        echo $this->twig->render('manage_users.html.twig', [
            'users' => $users,
            'edit_user' => $edit_user,
            'current_user_id' => $user['id'],
            'toast_success' => $this->session->get('toast_success'),
            'toast_error' => $this->session->get('toast_error'),
        ]);

        $this->session->set('toast_success', null);
        $this->session->set('toast_error', null);
    }

    private function handleDeleteUser(int $userId): void
    {
        $currentUser = $this->session->get('user');
        $toDelete = User::getById($userId);

        if ($toDelete && $toDelete['role'] !== 'patron' && $userId !== $currentUser['id']) {
            User::delete($userId);
            $this->session->set('toast_success', "Utilisateur supprimé avec succès.");
        } else {
            $this->session->set('toast_error', "Impossible de supprimer ce compte.");
        }

        $this->redirect('/manage-users');
    }

    private function handleEditUser(): void
    {
        $id = (int)$this->getPost('edit_user_id');
        $nom = $this->getPost('edit_nom', null);
        $password = $this->getPost('edit_password', null);

        if ($nom || $password) {
            User::update($id, $nom, $password);
            $this->session->set('toast_success', "Utilisateur modifié avec succès.");
        } else {
            $this->session->set('toast_error', "Aucune donnée à modifier.");
        }

        $this->redirect('/manage-users');
    }

    private function handleCreateUser(): void
    {
        $nom = $this->getPost('new_nom', '');
        $password = $this->getPost('new_password', '');
        $role = $this->getPost('new_role', 'employe');

        if (!empty($nom) && !empty($password)) {
            User::create($nom, $password, $role);
            $this->session->set('toast_success', "Utilisateur créé avec succès.");
        } else {
            $this->session->set('toast_error', "Veuillez remplir tous les champs pour créer un utilisateur.");
        }

        $this->redirect('/manage-users');
    }
}
