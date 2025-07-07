<?php

namespace App\Controller;

use App\Core\Logger;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Database;

class BaseController
{
    protected Request $request;
    protected Session $session;
    protected Csrf $csrf;
    protected Logger $logger;
    protected \PDO $db;

    public function __construct()
    {
        // Initialise la session proprement
        Session::start();

        // Dépendances centralisées
        $this->request = new Request();
        $this->session = new Session();
        $this->csrf = new Csrf();
        $this->logger = new Logger();

        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Redirection propre
     */
    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Protection XSS simple
     */
    protected function cleanXSS(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Récupération sécurisée des données POST nettoyées
     */
    protected function getPost(string $key, $default = null): mixed
    {
        $value = $this->request->post($key, $default);
        return is_string($value) ? $this->cleanXSS($value) : $value;
    }

    /**
     * Récupération sécurisée des données GET nettoyées
     */
    protected function getQuery(string $key, $default = null): mixed
    {
        $value = $this->request->get($key, $default);
        return is_string($value) ? $this->cleanXSS($value) : $value;
    }

    /**
     * Générer un token CSRF
     */
    protected function generateCsrfToken(): string
    {
        return $this->csrf->generateToken();
    }

    /**
     * Vérifier un token CSRF
     */
    protected function isCsrfValid(string $token): bool
    {
        return $this->csrf->validateToken($token);
    }

    /**
     * Logger rapide
     */
    protected function log(string $type, array $data, ?string $contrat = null): void
    {
        Logger::logCsv($type, $data, $contrat);
    }

    protected function requireAuth(): array
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/');
        }
        return $user;
    }
}
