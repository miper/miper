<?php

$root = dirname(dirname(__DIR__));

require $root.'/src/Msful/App.php';

define('ROUTER_ROOT', dirname(__DIR__).'/service/');

$app = new Msful_App();
$app->dispatch('/demo/', 'demo');
$app->dispatch('/user/', 'user');
$app->dispatch('/user/test/', 'user');

$app->error('msful.notfound', function() {
  header('HTTP/1.1 404 Not Found');
  return array(
    'err' => 'notfound',
  );
});

$app->start();
