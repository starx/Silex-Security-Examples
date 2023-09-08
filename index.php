<?php

use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

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
        'pattern' => '^.*$',  // Protects the whole application, change as needed
        'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
        'logout' => array('logout_path' => '/logout'),
        'users' => array(
            'user' => array('ROLE_USER', 'userpass'),
            'admin' => array('ROLE_ADMIN', 'adminpass'),
        ),
    ),
);

$app['security.access_manager'] = function ($app) {
    return new Symfony\Component\Security\Core\Authorization\AccessDecisionManager(
        $app['security.voters'],
        'unanimous',  // This is the strategy
        false,        // Whether to allow access if all voters abstain. Set to true if you want to allow by default.
        true          // Whether to allow access if equal grant and deny votes are received.
    );
};

$app['security.voters'] = $app->extend('security.voters', function($voters) use ($app) {
//    $voters[] = new RoleHierarchyVoter(new RoleHierarchy($app['security.role_hierarchy']));
//    $voters[] = new AuthenticatedVoter($app['security.trust_resolver']);

    $whitelistedIps = ['127.0.0.1', '192.16.56.1'];  // Adjust this to your needs
    $voters[] = new \App\IpVoter($app['request_stack'], $whitelistedIps);


    return $voters;
});

$app['security.access_rules'] = array(
    array('^/admin', ['IS_AUTHENTICATED_FULLY', 'WHITELISTED_IP', 'ROLE_ADMIN',]),
    array('^/secured', ['IS_AUTHENTICATED_FULLY', 'WHITELISTED_IP', 'ROLE_USER']),
    array('^/login', ['IS_AUTHENTICATED_ANONYMOUSLY']),
    array('^.*$', ['IS_AUTHENTICATED_FULLY', 'WHITELISTED_IP', 'ROLE_USER',]),
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
    return $app['twig']->render('user.html.twig', [
        'last_username' => $app['session']->get('_security.last_username'),
    ]);
});

$app->get('/admin', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('admin.html.twig');
});

$app->run();