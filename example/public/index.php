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

// $app->get('/test', function() {
//   return 'test';
// });
// $app->delegate('/demo/', ROUTER_ROOT.'/demo/index.php');
// $app->delegate('/user/', ROUTER_ROOT.'/user/index.php');
// $app->delegate('/user/test/', ROUTER_ROOT.'/user/index.php');
$app->get('/hello', function() {
  return 'hello';
});
$app->delegate('/user/', ROUTER_ROOT.'/user/UserExport.php', 'MyMsful_Service_User');

$app->error('msful.notfound', function() {
  return array(
    'code' => 404,
    'data'  => 'notfound',
  );
});
