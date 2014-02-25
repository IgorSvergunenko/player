<?php

date_default_timezone_set('Europe/Kiev');
header('Content-Type: text/html;charset=utf-8');
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/IndexController.php';
require_once __DIR__ . '/../app/AdminController.php';
require_once __DIR__ . '/../app/Audio.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

define('WEB_PATH', __DIR__);

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/configs/live.json"));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $app['db'],
));

/**
 * Routes
 */
$app->get('/', 'App\IndexController::index');
$app->post('/search', 'App\IndexController::search');
$app->get('/about', 'App\IndexController::about');
$app->get('/contact', 'App\IndexController::contact');
$app->get('/parameters/{link}/{statId}', 'App\IndexController::parameters');

$app->post('/login', 'App\AdminController::login');
$app->get('/admin', 'App\AdminController::admin');
$app->get('/logout', 'App\AdminController::logout');
$app->get('/dashboard', 'App\AdminController::dashboard');

$app->run();