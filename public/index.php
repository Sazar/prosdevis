<?php

// Bootstrap
define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->safeLoad();

use App\Helpers\Session;

Session::start();

// Router simple
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET'  => [
        '/'                   => ['App\Controllers\LandingController',   'index'],
        '/login'              => ['App\Controllers\AuthController',       'showLogin'],
        '/logout'             => ['App\Controllers\AuthController',       'logout'],
        '/forgot-password'    => ['App\Controllers\AuthController',       'showForgotPassword'],
        '/dashboard'          => ['App\Controllers\DashboardController',  'index'],
        '/quotes'             => ['App\Controllers\QuoteController',      'index'],
        '/quotes/new'         => ['App\Controllers\QuoteController',      'create'],
        '/quotes/([0-9]+)'    => ['App\Controllers\QuoteController',      'show'],
        '/quotes/([0-9]+)/edit' => ['App\Controllers\QuoteController',   'edit'],
        '/quotes/([0-9]+)/pdf' => ['App\Controllers\QuoteController',    'pdf'],
        '/invoices'           => ['App\Controllers\InvoiceController',   'index'],
        '/clients'            => ['App\Controllers\ClientController',    'index'],
        '/clients/new'        => ['App\Controllers\ClientController',    'create'],
        '/settings'           => ['App\Controllers\SettingsController',  'index'],
        '/blog'               => ['App\Controllers\BlogController',      'index'],
        '/blog/([a-z0-9-]+)'  => ['App\Controllers\BlogController',      'show'],
        '/pricing'            => ['App\Controllers\LandingController',   'pricing'],
    ],
    'POST' => [
        '/login'              => ['App\Controllers\AuthController',      'login'],
        '/forgot-password'    => ['App\Controllers\AuthController',      'forgotPassword'],
        '/quotes'             => ['App\Controllers\QuoteController',     'store'],
        '/quotes/([0-9]+)'    => ['App\Controllers\QuoteController',     'update'],
        '/quotes/([0-9]+)/convert' => ['App\Controllers\QuoteController','convertToInvoice'],
        '/quotes/([0-9]+)/sign'    => ['App\Controllers\QuoteController','sign'],
        '/clients'            => ['App\Controllers\ClientController',   'store'],
        '/settings'           => ['App\Controllers\SettingsController', 'update'],
    ],
];

$matched = false;
foreach ($routes[$method] ?? [] as $pattern => $handler) {
    $regex = '#^' . $pattern . '$#';
    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches);
        [$class, $action] = $handler;
        if (class_exists($class)) {
            $controller = new $class();
            $controller->$action(...$matches);
        } else {
            http_response_code(500);
            echo 'Controller not found: ' . $class;
        }
        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require ROOT . '/app/Views/errors/404.php';
}
