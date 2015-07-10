<?php
/**
 * 简单的模板处理器
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-09 18:28:47
 * @version   1.0.0
 */

class Miper_View_Simple
{
  function fetch($path, $vars = array())
  {
    $delPath = false;
    if (is_string($path) && !is_file($path)) {
      $delPath = true;
      $dir = sys_get_temp_dir();
      if (is_writable($dir)) {
        $tmpFile = tempnam($dir, __CLASS__);
        file_put_contents($tmpFile, $path);
        $path = $tmpFile;
      } else {
        throw new Exception('miper_view_simple.tmpNotWritable path:'.$dir);
      }
    }
    
    ob_start();
    extract($vars);

    include $path;
    $result = ob_get_clean();
    if ($delPath) {
      @unlink($path);
    }
    return $result;
  }
}
