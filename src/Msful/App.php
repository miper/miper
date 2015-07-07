<?php
/**
 * APP类，总负责类
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-07 16:12:48
 * @version 1.0.0
 */

require_once __DIR__.'/Const.php';
require_once __DIR__.'/Conf.php';
require_once __DIR__.'/Request.php';

class Msful_App
{
  /** @var Msful_Request 请求 */
  private $request;

  private $hitRoute = false;

  private $routerName = null;

  private $errors = array();

  function __construct()
  {
    $this->request = new Msful_Request();
    $this->request->init($_GET, $_POST, $_SERVER);
  }

  // private $dispatchers = array();
  // private $routes = array();

  /**
   * 分配路由
   * @param  string $glob 路由的匹配规则
   * @param  string $routeName 路由的文件路径
   * @return Msful_App
   */
  function dispatch($glob, $routeName)
  {
    if ($this->routerName) {
      return;
    }
    
    $url = $this->request->url;
    if ($glob[0] == '^') {
      if (preg_match('#'.$glob.'#', $url)) {
        $this->routerName = $routeName;
      }
    } else {
      $glob = rtrim($glob, '/');
      if (strpos($url, $glob) === 0) {
        $this->routeName = $routeName;
      }
    }
  }

  function get($url, $callback)
  {
    return $this->route(Msful_Const::METHOD_GET, $url, $callback);
  }

  function post($url, $callback)
  {
    return $this->route(Msful_Const::METHOD_POST, $url, $callback);
  }

  function put($url, $callback)
  {
    return $this->route(Msful_Const::METHOD_PUT, $url, $callback);
  }

  function delete($url, $callback)
  {
    return $this->route(Msful_Const::METHOD_DELETE, $url, $callback);
  }

  /**
   * 配置路由
   * @param  string | array $method   方法 get、post、delete、push、option
   * @param  string $url      网址
   * @param  callable $callback 回调函数
   * @return Msful_App
   */
  function route($method, $url, $callback)
  {
    if ($this->hitRoute) {
      return;
    }

    if (!is_array($method)) {
      $method = array($method);
    }
    if ($url[0] != '^') {
      $url = rtrim($url, '/');
    }

    $method = array_intersect($method, Msful_Const::$methods);

    if (!empty($method)) {
      $_curl = $this->request->url;
      $args = array();

      // 只放进去和当前匹配的路由
      if (in_array($this->request->method, $method)) {
        if ($url[0] == '^') {
          if (preg_match('#'.$url.'#', $_curl, $args)) {
            array_shift($args);
            $this->hitRoute = true;
          }
        } else if ($url == $_curl) {
          $this->hitRoute = true;
        }
        
        
        if ($this->hitRoute) {
          if (!is_callable($callback)) {
            throw new \Exception('Msful.callbackNotCallable');
          }

          $ret = call_user_func($callback, $args, $this->request, null);
          $this->output($ret);
        }

        //$this->routes[$m][$url] = $callback;
      }
    }
  }

  private function output($ret)
  {
    var_dump($ret);
    exit();
  }

  function start()
  {

    if (!$this->routeName) {
      return $this->triggerError('msful.notfound');
    }

    if (!defined('ROUTER_ROOT')) {
      return $this->triggerError('msful.notfound');
    }
    $rpath = ROUTER_ROOT.'/'.$this->routeName.'/index.php';

    if (!is_readable($rpath)) {
      return $this->triggerError('msful.notfound');
    }
  
    include $rpath;

    if (!$this->hitRoute) {
      return $this->triggerError('msful.notfound');
    }
  }

  function error($code, $callback)
  {
    $this->errors[$code] = $callback;
  }

  private function triggerError($code)
  {
    if (isset($this->errors[$code])) {
      $callback = $this->errors[$code];
      if (!is_callable($callback)) {
        throw new Exception('msful.errorCallbackNotCallable code:'.$code);
      }

      $ret = call_user_func($callback, $this->req);
      $this->output($ret);
    }
  }
}