<?php

require dirname(__DIR__) . '/src/app.php';

global $app;

// Boot app
$app->boot();

$user = new Symfony\Component\Security\Core\User\User(
    'admin',
    'somepass',
    [
        'ROLE_ADMIN'
    ]
);


// find the encoder for a UserInterface instance
$encoder = $app['security.encoder_factory']->getEncoder($user);

// compute the encoded password for foo
$password = $encoder->encodePassword('foo', $user->getSalt());
echo $password;
