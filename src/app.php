<?php

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
date_default_timezone_set('Europe/London');

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new Silex\Provider\SecurityServiceProvider(), [
    'security.firewalls' => [
        'login' => array(
            'pattern' => '^/login$',
        ),
        'admin' => [
            'pattern' => '^/admin',
            'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
            'logout' => array('logout_path' => '/admin/logout', 'invalidate_session' => true),
            'users' => function () use ($app) {
                return new \Starx\SilexDocker\Security\Provider\UserProvider($app['users.db']);
            },
        ],
    ],
    'security.role_hierarchy' => [
        'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'),
    ],
    'security.access_rules' => [
        array('^/admin', 'ROLE_ADMIN'),
        array('^.*$', 'ROLE_USER'),
    ]
]);

$app['security.voters'] = $app->extend('security.voters', function($voters) use ($app) {
    $voters[] = new \Starx\SilexDocker\Security\Voter\ActiveUserVoter($app['users.db']);
    return $voters;
});

$app['security.access_manager'] = $app->share(function($app) {
    return new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager($app['security.voters'], 'unanimous');
});

$app->error(function (\Exception $e, $code) use ($app) {
    if($code === Response::HTTP_FORBIDDEN) {
        $deniedReason = $app['request']->attributes->get('_access_denied_reason');

        $msg = $deniedReason ?: $e->getMessage() ?: 'You do not have permission to access this resource.';
        return $app->json(['error' => $msg], 403);
    }
});

$app['users.db'] = $app->share(function() {
    $encoder = new MessageDigestPasswordEncoder('sha512', true, 5000);

    return [
        'admin' => [
            'id' => 1,
            'username' => 'admin',
            'password' => $encoder->encodePassword('adminPass', ''),
            'roles' => ['ROLE_ADMIN'],
            'custom_active_check' => true,
        ],

        'admin_old' => [
            'id' => 2,
            'username' => 'admin_old',
            'password' => $encoder->encodePassword('adminPass', ''),
            'roles' => ['ROLE_ADMIN'],
            'custom_active_check' => false,
        ]   
    ];
});


$app->get('/_whoami', function() use ($app) {
    $token = $app['security.token_storage']->getToken();
    $user  = $token ? $token->getUser() : null;
    $factory = $app['security.encoder_factory'];
    $enc = $user ? $factory->getEncoder($user) : null;

    return new \Symfony\Component\HttpFoundation\Response(
        sprintf(
            "user=%s\nroles=%s\nencoder=%s\n",
            $user ? $user->getUsername() : 'anon',
            $user ? implode(',', $user->getRoles()) : '-',
            $enc ? get_class($enc) : '-'
        ),
        200,
        ['Content-Type' => 'text/plain']
    );
});

$app->get("/", function () {
    return "Hello world!";
});

$app->get("/hello/{name}", function ($name) use ($app) {
    return "Hello ".$app->escape($name);
});

$app->get("/admin" , function () {
    return "Admin page";
});

$app->get("/admin/login_check" , function () {
    return "Login check";
});

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});
