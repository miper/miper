<?php
/**
 * http请求
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-07 16:29:48
 * @version 1.0.0
 */

require_once __DIR__.'/Const.php';

class Msful_Request
{
  private $gets = array();
  private $posts = array();
  private $servers = array();


  var $method;
  var $url;

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
    $path = parse_url($uri, PHP_URL_PATH);
    $this->url = rtrim($path, '/');

    // 初始化请求方法
    $method = strtolower($servers['REQUEST_METHOD']);

    if ($method == 'post') {
      $_method = '';
      if (isset($posts['_METHOD'])) {
        $_method = strtolower($posts['_METHOD']);
      } else if (isset($servers['X_HTTP_METHOD_OVERRIDE'])) {
        $_method = strtolower($servers['X_HTTP_METHOD_OVERRIDE']);
      }
      if ($_method && $_method != $method && in_array($method, MSful_Const::$methods)) {
        $method = $_method;
      }
    }
    $this->method = $method;
  }

  /**
   * 获取指定的get变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed
   */
  function get($key, $defVal = null)
  {
    if (array_key_exists($this->gets, $key)) {
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
    return $this->_getPostVal(MSful_Const::METHOD_POST, $key, $defVal);
  }

  /**
   * 获取指定的put变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed         
   */
  function put($key, $defVal = null)
  {
    return $this->_getPostVal(MSful_Const::METHOD_PUT, $key, $defVal);
  }

  /**
   * 获取指定的delete变量
   * @param  string $key    key
   * @param  mixed $defVal 默认值
   * @return mixed         
   */
  function delete($key, $defVal = null)
  {
    return $this->_getPostVal(MSful_Const::METHOD_DELETE, $key, $defVal);
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