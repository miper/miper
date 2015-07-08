<?php
/**
 * APP类，用来控制整个请求的流程
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:28:46
 * @version   1.0.0
 */

class Msful_App
{
  /** @var Msful_Request 请求 */
  var $request;

  /** @var Msful_App app类 */
  private static $app;

  /** @var array 中转数据 */
  private $datas = array();

  private $code = 200;

  var $args = array();

  private $pipeContinue = false;
  private $routerPath;

  private function __construct()
  {
    register_shutdown_function(array($this, 'onShutdown'));
    set_exception_handler(array($this, 'onException'));
    set_error_handler(array($this, 'onError'));

    spl_autoload_register(array($this, 'onAutoload'));

    $this->request = new Msful_Request();
    $gets = $_GET;
    $posts = $_POST;
    $servers = $_SERVER;

    $this->request->init($gets, $posts, $servers);

    unset($_GET);
    unset($_POST);
    unset($_SERVER);

    error_reporting(E_ALL);
    if ($this->request->debug) {
      ini_set('display.errors', true);
    }
  }

  /**
   * 获取唯一实例
   * @return Msful_App
   */
  public static function getAppInstance()
  {
    if (!self::$app) {
      self::$app = new Msful_App();
    }
    return self::$app;
  }

  function onShutdown()
  {
    if (!$this->pipeContinue) {
      $this->triggerError('msful.notfound');
    }
  }

  function onAutoload($clsName, $params = null)
  {
    $path = dirname(__DIR__).DIRECTORY_SEPARATOR. str_replace('_', DIRECTORY_SEPARATOR, $clsName).'.php';
    require_once $path;
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

      require_once $path;
    }
  }

  /**
   * 条件
   * @param string $method 请求方法
   * @param string $glob   请求路径
   * @param callable $closure 闭包
   * @return
   */
  function when($method, $glob)
  {
    if ($this->pipeContinue) {
      return $this;
    }
    if ($method != $this->request->method) {
      return $this;
    }

    $args = $this->handleGlob($glob, $this->request->url);
    if ($args === false) {
      return $this;
    }
    if (is_array($args)) {
      $this->args = $args;
    }
    $this->pipeContinue = true;
    
    return $this;
  }

  private function handleGlob($glob, $url)
  {
    $glob = rtrim($glob, '/');
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

  private $lastDatas = array();
  /**
   * 管道执行
   * @param  [type] $executor [description]
   * @param  [type] $options  [description]
   * @param  array  $causes   [description]
   * @param  array  $wrapper  [description]
   * @return [type]           [description]
   */
  function pipe($executor, $options, $causes = array(), $wrapper = array())
  {
    if (!$this->pipeContinue) {
      return $this;
    }

    $className = 'Msful_Pipe_'.ucfirst($executor);

    $datas = $this->lastDatas;
    
    $cls = new $className();
    $continue = $cls->pipe($this, $options, $causes, $wrapper, $datas);
    $this->lastDatas = $datas;

    if ($continue === false) {
      $this->pipeContinue = false;
    } else {
      $this->datas[$executor][] = $datas;
    }
    return $this;
  }

  function output()
  {
    $format = $this->request->format; 
    $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $ret = $this->datas;
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
}