<?php

$root = dirname(__DIR__);
define('VENDOR_DIR', $root.'/vendor/');
define('LIBRARY_DIR', $root.'/src/');

require VENDOR_DIR.'/Miper/App.php';

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    VENDOR_DIR,
    LIBRARY_DIR,
  ))
  );

$app = Miper_App::getAppInstance();

$app->get([
    '/test/#{foo:?string}/#{userid}', 
    function($req){
      return array('args' => $req->args);
    }]
  )
  ->output()
  ->end();

$app->
  get([
    '/hello/world', 
    function($req){
      return 'Hello,World';
    }
  ])
  ->output()
  ->end();


$app->delegate('/user/', 'Happy_Delegate_User');

$app->error('msful.notfound', function($msg, $detail) use($app) {
  header('HTTP/1.1 404 Not Found');
  return array(
    'msg'     => $msg,
    'detail'  => $detail,
  );
});
