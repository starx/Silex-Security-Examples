<?php

require dirname(__DIR__) . '/src/app.php';

global $app;

// Boot app
$app->boot();
// Run app
$app->run();