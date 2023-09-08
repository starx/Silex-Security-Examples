<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__.'/vendor/autoload.php';

$app = new \Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());


$app['security.encoder.digest'] = function ($app) {
    return new Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder();
};
$app['security.firewalls'] = array(
    'secured' => array(
        'pattern' => '^/',  // Protects the whole application, change as needed
        'anonymous' => true,
        'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
        'logout' => array('logout_path' => '/logout'),
        'users' => array(
            'user' => array('ROLE_USER', 'userpass'),
            'admin' => array('ROLE_ADMIN', 'adminpass'),
        ),
    ),
);

$app['security.voters'] = $app->extend('security.voters', function($voters) use ($app) {
    $voters[] = new \App\RouteVoter();
    return $voters;
});

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
    array('^/user', 'ROLE_USER')
);

$app->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('index.html.twig');
});

$app->get('/login', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/secured', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('secured.html.twig');
});


$app->get('/user', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    if (!$app['security.authorization_checker']->isGranted('USER')) {
        throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
    }
    return $app['twig']->render('user.html.twig', [
        'last_username' => $app['session']->get('_security.last_username'),
    ]);
});

$app->get('/admin', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    if (!$app['security.authorization_checker']->isGranted('ADMIN')) {
        throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
    }
    return $app['twig']->render('admin.html.twig');
});

$app->run();