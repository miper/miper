<?php
/**
 * APP类，用来控制整个请求的流程
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:28:46
 * @version   1.0.0
 */

class Miper_App
{
  /** @var Miper_Request 请求 */
  var $request;

  /** @var Miper_App app类 */
  private static $app;

  /** @var array 中转数据 */
  var $datas;

  var $code;

  private $start;

  private $pipeContinue = true;
  private $hitDelegate = false;

  private $pipers = array();

  private function __construct()
  {
    register_shutdown_function(array($this, 'onShutdown'));
    set_exception_handler(array($this, 'onException'));
    set_error_handler(array($this, 'onError'));

    spl_autoload_register(array($this, 'onAutoload'));

    $this->request = new Miper_Request();
    $gets = $_GET;
    $posts = $_POST;
    $servers = array();

    foreach($_SERVER as $key => $value) {
      if (preg_match('#^(SERVER|HTTP|REQUEST|REMOTE)\_#', $key)) {
        $servers[$key] = $value;
      }
    }

    $this->request->init($gets, $posts, $servers);

    unset($_GET);
    unset($_POST);
    unset($_SERVER);

    error_reporting(E_ALL);
    if ($this->request->debug) {
      ini_set('display.errors', true);
    }

      
    $this->code = Miper_Const::HTTP_CODE_NOT_FOUND;
  }

  /**
   * 获取唯一实例
   * @return Miper_App
   */
  public static function getAppInstance()
  {
    if (!self::$app) {
      self::$app = new Miper_App();
    }
    return self::$app;
  }

  function onShutdown()
  {
    if ($this->code != Miper_Const::HTTP_CODE_OK) {
      $this->triggerError('msful.notfound');
    }
  }

  function onAutoload($clsName, $params = null)
  {
    $path = str_replace('_', DIRECTORY_SEPARATOR, $clsName).'.php';
    require_once $path;
  }

  function onException($ex)
  {
    print_r($ex->getMessage());
  }

  function onError()
  {
    echo '<pre>';
    print_r(func_get_args());
    echo '</pre>';
    return true;
  }

  private function triggerError($msg, $detail = null)
  {
    if (isset($this->errors[$msg])) {
      $callback = $this->errors[$msg];
      if (!is_callable($callback)) {
        throw new Exception('msful.errorCallbackNotCallable code:'.$code);
      }

      $ret = call_user_func($callback, $msg, $detail);
      $this->datas = $ret;
      $this->pipeContinue = true;
      $this->pipe('output', true);
    }
  }

  
  function error($code, $callback)
  {
    $this->errors[$code] = $callback;
  }

  /**
   * 分配路由
   * @param  string $glob 路由的匹配规则
   * @param  string $className 用来处理的代理类
   * @return Miper_App
   */
  function delegate($glob, $className)
  {
    if ($this->hitDelegate) {
      return $this;
    }
    
    $url = $this->request->url;
    $glob = rtrim($glob, '/');
    if (strpos($url, $glob) === 0) {
      $this->hitDelegate = true;

      if (!class_exists($className)) {
        throw new Exception('msful.notfound', 'delegete not found:'.$className);
      }
      $cls = new $className();
      if ($cls instanceof Miper_Delegate_Interface) {
        $cls->delegate($this);
      }
    }
  }

  function __call($func, $args) {
    if (in_array($func, Miper_Const::$methods)) {
      $firstArg = $args[0];
      if (is_string($firstArg)) {
        $firstArg = array($func, $firstArg);
      } else if (is_array($firstArg)) {
        array_unshift($firstArg, $func);
      }
      $args[0] = $firstArg;
      $func = 'request';
    }
    array_unshift($args, $func);
    return call_user_func_array([$this, 'pipe'], $args);
  }

  /**
   * 管道执行
   * @param  [type] $executor [description]
   * @param  [type] $options  [description]
   * @param  mixed  $wrapper  [description]
   * @param  mixed  $causes   [description]
   * @return [type]           [description]
   */
  function pipe($executor, $options = null, $wrapper = null, $causes = null)
  {
    if (!$this->pipeContinue) {
      return $this;
    }

    // 判断是否允许进行该条件
    if ($causes === false) {
      return $this;
    }
    
    if ($causes !== null && is_callable($causes)) {
      $causeRet = call_user_func($causes);
      if ($causeRet === false) {
        return $this;
      }
    }

    if (!isset($this->pipers[$executor])) {
      $className = 'Miper_Pipe_'.ucfirst($executor);
      $cls = $this->pipers[$executor] = new $className();
    } else {
      $cls = $this->pipers[$executor];
    }

    $continue = $cls->handle($this, $options);

    if ($continue === false) {
      $this->pipeContinue = false;
    }

    // 对数据进行封装处理
    if ($this->datas !== null) {
      if (is_string($wrapper)) {
        $this->datas = array($wrapper => $this->datas);
      } else if (is_callable($wrapper)) {
        $this->datas = call_user_func($wrapper, $this->datas);
      }
    }

    return $this;
  }

  /**
   * @return 结束
   */
  function end()
  {
      $this->pipeContinue = true;
  }
}