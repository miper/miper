<?php

$root = dirname(dirname(__DIR__));

require $root.'/Msful/App.php';

define('SERVICE_ROOT', dirname(__DIR__).'/service/');

$app = Msful_App::getAppInstance();
$app->delegate('/user/', SERVICE_ROOT.'/user/pipe.conf.php');

// $app->when('get', '/user/#{uid}')
//   ->pipe('service', 'user::getUser')
//   ->output();
//   ;