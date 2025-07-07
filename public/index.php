<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Initialisation de Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

// Déclaration des routes FastRoute
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\Controller\AuthController', 'login']);
    $r->addRoute('POST', '/', ['App\Controller\AuthController', 'login']);
    $r->addRoute('GET', '/logout', ['App\Controller\AuthController', 'logout']);
    $r->addRoute('GET', '/dashboard', ['App\Controller\DashboardController', 'index']);
    $r->addRoute('GET', '/ventes', ['App\Controller\VentesController', 'index']);
    $r->addRoute('GET', '/ventes/manager', ['App\Controller\VenteManagerController', 'handleRequest']);
    $r->addRoute('POST', '/ventes/manager', ['App\Controller\VenteManagerController', 'handleRequest']);
    $r->addRoute('GET', '/manage-users', ['App\Controller\ManageUserController', 'index']);
    $r->addRoute('POST', '/manage-users', ['App\Controller\ManageUserController', 'index']);
    $r->addRoute('GET', '/coffre', ['App\Controller\CoffreController', 'index']);
    $r->addRoute('POST', '/coffre', ['App\Controller\CoffreController', 'index']);
    $r->addRoute('GET', '/partenariats', ['App\Controller\VentesController', 'index']);
});

// Récupération de la méthode et de l'URI demandées
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Nettoyage de l'URI des éventuels paramètres GET
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Dispatch de la route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo $twig->render('404.html.twig');
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "Méthode non autorisée.";
        break;

    case FastRoute\Dispatcher::FOUND:
        [$controllerName, $method] = $routeInfo[1];

        if (class_exists($controllerName)) {
            $controller = new $controllerName($twig);

            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                http_response_code(500);
                echo "Erreur : Méthode '$method' introuvable.";
            }
        } else {
            http_response_code(500);
            echo "Erreur : Contrôleur '$controllerName' introuvable.";
        }
        break;
}
