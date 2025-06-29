<?php

namespace App\Controller;

use App\Model\User;
use App\Core\Csrf;
use App\Core\Session;
use Twig\Environment;

class AuthController
{
    private Environment $twig;
    private Csrf $csrf;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->csrf = new Csrf();
    }

    public function login(): void
    {
        Session::start();

        if (isset($_GET['logout'])) {
            Session::destroy();
            header("Location: /");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $password = $_POST['password'] ?? '';
            $token = $_POST['_csrf_token'] ?? '';

            if (!$this->csrf->validateToken($token)) {
                $_SESSION['toast_error'] = 'Requête invalide (CSRF).';
                header("Location: /");
                exit;
            }

            $userModel = new User();
            $user = $userModel->getByName($nom);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                header("Location: /dashboard");
                exit;
            } else {
                $_SESSION['toast_error'] = "Identifiants invalides.";
                header("Location: /");
                exit;
            }
        }

        echo $this->twig->render('login.html.twig', [
            'toast_error' => $_SESSION['toast_error'] ?? null,
            '_csrf_token' => $this->csrf->generateToken(),
        ]);
        unset($_SESSION['toast_error']);
    }

    public function logout(): void
    {
        Session::start();
        Session::destroy();
        header("Location: /");
        exit;
    }
}
