<?php

namespace App\Controller;

use App\Model\User;
use Twig\Environment;

class AuthController extends BaseController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
    }

    public function login(): void
    {
        // Déconnexion si besoin
        if ($this->request->get('logout')) {
            $this->session->destroy();
            $this->redirect('/');
        }

        if ($this->request->method() === 'POST') {
            $nom = trim($this->getPost('nom', ''));
            $password = $this->getPost('password', '');
            $token = $this->getPost('_csrf_token', '');

            if (!$this->isCsrfValid($token)) {
                $this->session->set('toast_error', 'Requête invalide (CSRF).');
                $this->redirect('/');
            }

            $userModel = new User();
            $user = $userModel->getByName($nom);

            if ($user && password_verify($password, $user['password'])) {
                $this->session->set('user', $user);
                $this->redirect('/dashboard');
            } else {
                $this->session->set('toast_error', "Identifiants invalides.");
                $this->redirect('/');
            }
        }

        echo $this->twig->render('login.html.twig', [
            'toast_error' => $this->session->get('toast_error'),
            '_csrf_token' => $this->generateCsrfToken(),
        ]);

        $this->session->set('toast_error', null);
    }

    public function logout(): void
    {
        $this->session->destroy();
        $this->redirect('/');
    }
}
