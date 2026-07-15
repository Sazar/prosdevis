<?php
// Blog public
$router->get('/blog',             [BlogController::class, 'index']);
$router->get('/blog/{slug}',      [BlogController::class, 'show']);
$router->get('/sitemap.xml',      [BlogController::class, 'sitemap']);

// Blog admin
$router->get('/admin/blog',             [BlogController::class, 'adminIndex']);
$router->get('/admin/blog/create',      [BlogController::class, 'create']);
$router->post('/admin/blog',            [BlogController::class, 'store']);
$router->get('/admin/blog/{id}/edit',   [BlogController::class, 'edit']);
$router->post('/admin/blog/{id}',       [BlogController::class, 'update']);
