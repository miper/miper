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

  private $routerPath = null;

  private $errors = array();

  function __construct()
  {
    $this->request = new Msful_Request();
    $this->request->init($_GET, $_POST, $_SERVER);

    register_shutdown_function(array($this, 'onShutdown'));
    set_exception_handler(array($this, 'onException'));
    set_error_handler(array($this, 'onError'));
  }

  function onShutdown()
  {
    if (!$this->hitRoute) {
      $this->triggerError('msful.notfound');
    }
  }

  function onException($ex)
  {
    print_r($ex->getMessage());
  }

  function onError()
  {
    restore_error_handler();
    echo '<pre>';
    print_r(func_get_args());
    echo '</pre>';
    return true;
  }

  // private $dispatchers = array();
  // private $routes = array();

  /**
   * 分配路由
   * @param  string $glob 路由的匹配规则
   * @param  string $path 路由的文件路径
   * @param string $clsName 类名
   * @param array $paramname 初始化参数
   * @return Msful_App
   */
  function delegate($glob, $path, $clsName = null, $confs = null)
  {
    if ($this->routerPath) {
      return;
    }
    
    $url = $this->request->url;
    $glob = rtrim($glob, '/');
    if (strpos($url, $glob) === 0) {
      $this->routerPath = $path;

      if (!is_readable($path)) {
        $this->triggerError('msful.notfound');
        return;
      }

      $doRoute = false;

      $routers = false;

      if ($clsName === null) {
        // 如果文件返回的是数组，表明是定义了一组路由项
        
        $configs = include($path);
        if (empty($configs) || !is_array($configs)) {
          return;
        } 
        $path = $configs['path'];
        $clsName = $configs['class'];
        $routers = $configs['routers'];

        $doRoute = true;
      }

      require_once $path;
      if (!class_exists($clsName)) {
        return $this->triggerError('msful.notfound', 'class:'.$clsName);
      }

      // 加载类，如果实现了接口，则调用接口里边定义的_router方法，获取router的相关配置
      if ($confs === null) {
        $cls = new $clsName();
      } else {
        $cls = new $clsName($confs);
      }
      
      if ($routers === false && $cls instanceof Msful_Router_Interface) {
        $routers = $cls->_routers();
      }
      if (!$routers) {
        return;
      }

      foreach($routers as $clsMethod => $router) {
        $router = trim($router);
        list($method, $glob) = explode(' ', $router);
        if (strpos($method, '|') !== false) {
          $method = explode('|', $method);
        }
        $this->route($method, $glob, array($cls, $clsMethod));
        if ($this->hitRoute) {
          break;
        }
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
        $args = $this->handleGlob($url, $this->request->url);
        if ($args === false) {
          return;
        }
        $this->hitRoute = true;
        
        if (!is_callable($callback)) {
          throw new \Exception('Msful.callbackNotCallable');
        }

        $this->doCallback($callback, $args);
      }
    }
  }

  private function doCallback($callback, $urlArgs = array())
  {
    if (is_string($callback)) {
      if (strpos($callback, '::') !== false) {
        $reflMethod = new ReflectionMethod($callback);
      } else {
        $reflMethod = new ReflectionFunction($callback);
      }
    } else if (is_array($callback)) {
      $reflMethod = new ReflectionMethod($callback[0], $callback[1]);
    } else {
      // 其他情况没法根据反射获取参数情况
      ob_start();
      $ret = call_user_func($callback);
      ob_clean();
      return $this->output($ret);
    }

    $params = $reflMethod->getParameters();
    $args = array();
    foreach($params as $param) {
      $name = $param->name;
      $value = $this->request->get($name);
      if ($value === null) {
        if (array_key_exists($name, $urlArgs)) {
          $args[] = $urlArgs[$name];
        } else if ($param->isDefaultValueAvailable()) {
          $args[] = $param->getDefaultValue();
        } else {
          throw new Exception('msful.paramNotProvided param:'.$param->name);
        }
      } else {
        $args[] = $value;
      }
    }

    // 方法不允许有任何输出
    ob_start();
    $ret = call_user_func_array($callback, $args);
    ob_clean();

    $this->output($ret);
  }

  private function output($ret)
  {
    $format = $this->request->format; 
    $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    switch($format) {
      case Msful_Const::FORMAT_JSON:
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode(array(
          'code' => 200,
          'data' => $ret,
        ), $options);
        break;
      case Msful_Const::FORMAT_HTML:
        header('Content-type: text/html; charset=UTF-8');
        if (is_scalar($ret)) {
          echo $ret;
        } else {
          echo '<pre>';
          print_r($ret);
          echo '</pre>';
        }
        break;
      case Msful_Const::FORMAT_TEXT:
        header('Content-type: text/plain; charset=UTF-8');
        if (is_scalar($ret)) {
          echo $ret;
        } else {
          print_r($ret);
        }
        break;
      case Msful_Const::FORMAT_JAVASCRIPT:
        header('Content-type: application/x-javascript; charset=UTF-8');
        if ( ($callback = $this->request->get('callback')) ) {
          echo sprintf('%s(%s);', $callback, json_encode(array(
            'code' => 200,
            'data' => $ret,
            ), $options));
        } else {
          if (!is_scalar($ret)) {
            echo json_encode($ret, $options);
          } else {
            echo $ret;
          }
        }
        break;
    }
    exit();
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

      $ret = call_user_func($callback);
      $this->output($ret);
    }
  }

  private function handleGlob($glob, $url)
  {
    $pos = strpos($glob, '#{');
    if ($pos === false) {
      return $glob == $url;
    }

    $expEnabled = false;
    $args = array();

    while(true) {
      $pos += 2;
      $pos2 = strpos($glob, '}', $pos);
      $adorn = '';

      if ($pos2 === false) {
        break;
      }

      $tag = substr($glob, $pos, $pos2 - $pos);
      
      if ($tag && ($pos3 = strpos($tag, ':')) !== false) {
        $type = strpos($tag, $pos3 + 1);
        $tag = strpos($tag, 0, $pos3);
      } else {
        $type = 'int';
      }

      switch($type) {
        case 'chinese':
          $exp = '[x{4e00}-x{9fa5}]+';
          $adorn = 'u';
          break;
        case 'int':
          $exp = '[0-9]+';
          break;
        case 'hex':
          $exp = '[0-9a-z]+';
          break;
        default:
          $exp = '[0-9a-zA-Z\-\_\.]+';
          break;
      }

      $glob = substr($glob, 0, $pos - 2).'('. $exp.')'.substr($glob, $pos2 + 1);
      $args[$tag] = null;
      $expEnabled = true;

      $pos = strpos($glob, '#{');
      if ($pos === false) {
        break;
      }
    }

    if (!$expEnabled) {
      return $glob == $url;
    }

    $glob = '#^'.$glob.'#'.$adorn;
    if (preg_match($glob, $url, $ms)) {
      $num = 1;
      foreach($args as $key => $value) {
        if (array_key_exists($num, $ms)) {
          $args[$key] = $ms[$num];
        }
        $num++;
      }
    } else {
      return false;
    }
    return $args;
  }
}

interface Msful_Router_Interface
{
  /**
   * 获取路由的相关配置
   * @return array 配置内容
   * -array
   *   -@methodName = @url
   *     @methodName(string, 方法名)
   *     @url(string, format="#method #url_glob")
   *       #method(
   *         enum, 
   *         "请求方法，如get，post，或者get|post", 
   *         values="['get', 'post', 'put', 'delete']"
   *         )
   *       #url_glob(
   *         string, 
   *         "网址格式，如/foo/bar, 或者/foo/#{user_id}"
   *         )
   * 
   */
  function _routers();
}