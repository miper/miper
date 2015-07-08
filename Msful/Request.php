<?php
/**
 * 请求类
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:36:12
 * @version   1.0.0
 */

require_once __DIR__.'/Const.php';

class Msful_Request
{
  private $gets = array();
  private $posts = array();
  private $servers = array();


  var $method;
  var $url;
  var $format = 'json';
  var $debug = false;
  var $terminal;

  /**
   * 初始化请求
   * @param  array $gets    get请求变量
   * @param  array $posts   post请求变量
   * @param  array $servers server变量
   * @return Msful_Request
   */
  function init($gets, $posts, $servers)
  {
    // 获取请求网址
    $uri = $servers['REQUEST_URI'];
    $uriInfo = parse_url($uri);
    if (!$uriInfo) {
      throw new Exception('msful.uriParseFailed uri:'. $uri);
    }

    // 根据网址后缀来决定使用哪种格式返回
    $url = $uriInfo['path'];
    $pathInfo = pathinfo($url);
    $ext = Msful_Const::FORMAT_JSON;
    if (isset($pathInfo['extension'])) {
      $ext = strtolower($pathInfo['extension']);
      if (!in_array($ext, Msful_Const::$formats)) {
        $ext = Msful_Const::FORMAT_JSON;
      }
    }
    $this->format = $ext;
    $this->url = rtrim(rtrim($pathInfo['dirname'], '/').'/'.$pathInfo['filename'], '/');

    // 初始化请求方法
    $method = strtolower($servers['REQUEST_METHOD']);

    if ($method == 'post') {
      $_method = '';
      if (isset($posts['_METHOD'])) {
        $_method = strtolower($posts['_METHOD']);
      } else if (isset($servers['X_HTTP_METHOD_OVERRIDE'])) {
        $_method = strtolower($servers['X_HTTP_METHOD_OVERRIDE']);
      }
      if ($_method && $_method != $method && in_array($method, Msful_Const::$methods)) {
        $method = $_method;
      }
    }
    $this->method = $method;

    $this->gets = $gets;
    $this->posts = $posts;
    $this->servers = $servers;
  }

  /**
   * 如果是get请求，从gets获取指定的变量，如果为非get请求，则优先从posts里边取，没有则从gets里边取
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed
   */
  function get($key, $defVal = null)
  {
    if ($this->method != 'get') {
      if (array_key_exists($key, $this->posts)) {
        return $this->posts[$key];
      }
    }
    if (array_key_exists($key, $this->gets)) {
      return $this->gets[$key];
    }
    return $defVal;
  }

  /**
   * 获取指定的post变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed         
   */
  function post($key, $defVal = null)
  {
    return $this->_getPostVal(Msful_Const::METHOD_POST, $key, $defVal);
  }

  /**
   * 获取指定的put变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed         
   */
  function put($key, $defVal = null)
  {
    return $this->_getPostVal(Msful_Const::METHOD_PUT, $key, $defVal);
  }

  /**
   * 获取指定的delete变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed         
   */
  function delete($key, $defVal = null)
  {
    return $this->_getPostVal(Msful_Const::METHOD_DELETE, $key, $defVal);
  }

  function _getPostVal($method, $key, $defVal = null)
  {
    if ($this->method != $method) {
      throw new Exception('msful.methodNotMatched expect:'.$this->method);
    }
    if (array_key_exists($this->posts, $key)) {
      return $this->posts[$key];
    }
    return $defVal;
  }
}