<?php

$root = dirname(dirname(__DIR__));

require $root.'/src/Msful/App.php';

define('ROUTER_ROOT', dirname(__DIR__).'/service/');

$app = new Msful_App();

function test($fooId)
{
  return 'hello:'.$fooId;
}

$app->get('/test/#{fooId}', 'test');
$app->delegate('/docs/', $root.'/src/Msful_Docs/Service.php', 'Msful_Docs_Service', array(
  'glob' => ROUTER_ROOT.'**/*Export.php',
  ));

// $app->get('/test', function() {
//   return 'test';
// });
// $app->delegate('/demo/', ROUTER_ROOT.'/demo/index.php');
// $app->delegate('/user/', ROUTER_ROOT.'/user/index.php');
// $app->delegate('/user/test/', ROUTER_ROOT.'/user/index.php');
$app->get('/hello', function() {
  return 'hello';
});
$app->delegate('/user/', ROUTER_ROOT.'user/routers.php');

$app->error('msful.notfound', function() {
  return array(
    'code' => 404,
    'data'  => 'notfound',
  );
});
