<?php
/**
 * 
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-09 15:07:01
 * @version   1.0.0
 */

class Miper_Autoload
{
  private static $loaded = false;

  static function autoload()
  {
    $args = func_get_args();
    $dirs = array();
    if (count($args) == 1) {
      if (is_string($args[0])) {
        $dirs = array($args[0]);
      } else if (is_array($args[0])) {
        $dirs = $args[0];
      }
    } else {
      $dirs = $args;
    }
    array_unshift($dirs, get_include_path());
    set_include_path(implode(PATH_SEPARATOR, $dirs));
  }

  static function registerAutoload()
  {
    if (self::$loaded) {
      return;
    }
    self::$loaded = true;

    spl_autoload_register(function($clsName) {
      $path = str_replace('_', DIRECTORY_SEPARATOR, $clsName).'.php';
      require_once $path;
    });

    if (php_sapi_name() == 'cli' && strpos($GLOBALS['argv'][0], 'phpunit') !== false) {
      self::autoload(__DIR__, dirname(__DIR__).'/tests/');
      
      $autoload = __DIR__.'/../vendor/autoload.php';
      if (is_readable($autoload)) {
        require_once $autoload;
      }
    }
  }
}

Miper_Autoload::registerAutoload();
