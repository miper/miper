<?php

$root = dirname(__DIR__);
define('VENDOR_DIR', $root.'/vendor/');
define('LIBRARY_DIR', $root.'/src/');

require VENDOR_DIR.'/Msful/App.php';

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    VENDOR_DIR,
    LIBRARY_DIR,
  ))
  );

$app = Msful_App::getAppInstance();
$app->delegate('/user/', 'Happy_Delegate_User');

$app->error('msful.notfound', function($msg, $detail) use($app) {
  header('HTTP/1.1 404 Not Found');
  return array(
    'msg'     => $msg,
    'detail'  => $detail,
  );
});
