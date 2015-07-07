<?php
/**
 * 
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-07 17:20:28
 * @version 1.0.0
 */

class Msful_Conf
{
    private static $confs = array();

    public static function load($path)
    {
      require $path;
    }

    public static function set($key, $value = null)
    {
      if (is_array($key)) {
        foreach($key as $k => $v) {
          self::$confs[$k] =  $v;
        }
        return;
      } 
      self::$confs[$key] = $value;
    }

    public static function get($key, $defVal = null)
    {
      if (array_key_exists(self::$confs, $key)) {
        return self::$confs[$key];
      }
      return $defVal;
    }
}