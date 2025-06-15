<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\AuthController;
use App\Controller\VenteManagerController;
use App\Controller\ManageUserController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

// Définir les routes
$routes = [
    '/' => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    '/dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    '/ventes' => ['controller' => 'VentesController', 'method' => 'index'],
    '/ventes/manager' => ['controller' => 'VenteManagerController', 'method' => 'handleRequest'],
    '/manage-users' => ['controller' => 'ManageUserController', 'method' => 'index'],
    '/partenariats' => ['controller' => 'VentesController', 'method' => 'index'],
    '/partenariats/contrat' => ['controller' => 'VentesController', 'method' => 'index'],
    '/coffre' => ['controller' => 'CoffreController', 'method' => 'index'],
];

// Obtenir l'URI demandée
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Trouver la route correspondante
$routeFound = false;
foreach ($routes as $path => $route) {
    if ($uri === $path) {
        $routeFound = true;
        $controllerName = 'App\\Controller\\' . $route['controller'];
        $method = $route['method'];

        if ($uri === '/partenariats') {
            $_GET['type'] = 'contrat';
        }

        // Instancier le contrôleur et appeler la méthode
        if (class_exists($controllerName)) {
            $controller = new $controllerName($twig);
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                http_response_code(500);
                echo "Erreur interne du serveur : Méthode introuvable.";
            }
        } else {
            http_response_code(500);
            echo "Erreur interne du serveur : Contrôleur introuvable.";
        }
        break;
    }
}

// Si aucune route n'est trouvée, afficher une page 404
if (!$routeFound) {
    http_response_code(404);
    echo $twig->render('404.html.twig');
}
