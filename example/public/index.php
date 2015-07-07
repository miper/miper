<?php

$root = dirname(dirname(__DIR__));

require $root.'/Msful/App.php';

define('SERVICE_ROOT', dirname(__DIR__).'/example/service/');

$app = Msful_App::getAppInstance();
$app->delegate('/user/', SERVICE_ROOT.'/user/pipe.conf.php');

$app->error('msful.notfound', function($msg, $detail) use($app) {
  header('HTTP/1.1 404 Not Found');
  return array(
    'msg'     => $msg,
    'detail'  => $detail,
  );
});
