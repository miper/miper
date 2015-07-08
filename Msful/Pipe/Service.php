<?php
/**
 * 
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:50:17
 * @version   1.0.0
 */

class Msful_Pipe_Service implements Msful_Pipe_Interface
{
  /**
   * [pipe description]
   * @param  [type] $options [description]
   * @param  array  $cause   [description]
   * @param  array  $wrapper [description]
   * @return [type]          [description]
   */
  function pipe($app, $options, $cause, $wrapper, &$datas)
  {
    list($module, $func) = explode('::', $options);
    $className = ucfirst($module).'Export';
    $path = SERVICE_ROOT.'/'.$module.'/'.$className.'.php';
    require_once $path;

    $reflMethod = new ReflectionMethod($className, $func);
    $params = $reflMethod->getParameters();
    $args = array();
    foreach($params as $param) {
      $name = $param->name;
      $value = $app->request->get($name);
      if ($value === null) {
        if (array_key_exists($name, $app->args)) {
          $args[] = $app->args[$name];
        } else if ($param->isDefaultValueAvailable()) {
          $args[] = $param->getDefaultValue();
        } else {
          throw new Exception('msful.paramNotProvided param:'.$param->name);
        }
      } else {
        $args[] = $value;
      }
    }

    $datas = $reflMethod->invokeArgs(new $className, $args);
  }
}