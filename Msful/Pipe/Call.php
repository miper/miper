<?php
/**
 * 
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:50:17
 * @version   1.0.0
 */

class Msful_Pipe_Call implements Msful_Pipe_Interface
{
  private $classCache = array();
  /**
   * [pipe description]
   * @param  [type] $options [description]
   * @param  array  $cause   [description]
   * @return [type]          [description]
   */
  function handle($app, $options)
  {
    $_args = $app->request->args;

    list($className, $func) = $options;

    if (!class_exists($className)) {
      throw new \Exception('msful_pipe_service.moduleNotFound class:'.$className);
    }
    if (!isset($this->classCache[$className])) {
      $cls = $this->classCache[$className] = new $className();
    } else {
      $cls = $this->classCache[$className];
    }
    if (!method_exists($cls, $func)) {
      throw new \Exception('msful_pipe_service.methodNotFound method:'.$className.'::'.$func);
    }

    $reflMethod = new ReflectionMethod($className, $func);
    $params = $reflMethod->getParameters();
    $args = array();
    $isArray = is_array($app->datas);
    foreach($params as $param) {
      $name = $param->name;
      $value = $app->request->get($name);
      if ($value === null) {
        if (array_key_exists($name, $_args)) {
          $args[] = $_args[$name];
        } else if ($isArray && array_key_exists($name, $app->datas)) {
          $args[] = $app->datas[$name];
        } else if ($param->isDefaultValueAvailable()) {
          $args[] = $param->getDefaultValue();
        } else {
          throw new Exception('msful.paramNotProvided param:'.$param->name);
        }
      } else {
        $args[] = $value;
      }
    }

    $app->datas = $reflMethod->invokeArgs($cls, $args);
  }
}