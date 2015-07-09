<?php
/**
 * 处理http请求的管道命令
 * 
 * @authors ronnie (comdeng@live.com)
 * @date    2015-07-08 23:25:03
 * @version $Id$
 */

class Msful_Pipe_Request implements Msful_Pipe_Interface
{
  function handle($app, $options)
  {
    $closure = null;
    if (is_array($options)) {
      list($method, $glob) = $options;
      if (count($options) > 2) {
        $closure = $options[2];
      }
    } else {
      $options = trim($options);
      if ( ($pos = strpos($options, ' ')) !== false) {
        $method = trim($options, 0, $pos);
        $glob = trim($options, $pos + 1);
      } else {
        $method = Msful_Const::METHOD_GET;
        $glob = $options;
      }
    }
    $method = strtolower($method);

    if ($method != $app->request->method) {
      return false;
    }

    $args = $this->handleGlob($glob, $app->request->url);
    if ($args === false) {
      return false;
    }
    if (is_array($args)) {
      $app->request->args = $args;
    }

    $app->code = Msful_Const::HTTP_CODE_OK;

    if ($closure && is_callable($closure)) {
      $app->datas = call_user_func($closure, $app->request);
    }
    return true;
  }

  /**
   * 处理glob，看是否和url匹配
   * 
   * @param  string $glob 匹配模式
   * @param  string $url 比较的网址
   * @return boolean | array
   * 
   */
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
        $type = substr($tag, $pos3 + 1);
        $tag = substr($tag, 0, $pos3);
      } else {
        $type = 'int';
      }
      $multiFlag = '+';
      if ($type[0] == '?') {
        $multiFlag = '*';
        $type = substr($type, 1);
      }
      switch($type) {
        case 'chinese':
          $exp = '[x{4e00}-x{9fa5}]';
          $adorn = 'u';
          break;
        case 'int':
          $exp = '[0-9]';
          break;
        case 'hex':
          $exp = '[0-9a-z]';
          break;
        case 'string':
          $exp = '[0-9a-zA-Z\-\_\.]';
          break;
      }
      $left = substr($glob, 0, $pos - 2);
      if ($multiFlag == '*' && substr($left, -1) == '/') {
        $left .= '?';
      } 
      $glob = $left.'('. $exp.$multiFlag.')'.substr($glob, $pos2 + 1);
      $args[$tag] = $type;
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
          if ($args[$key] == 'int') {
            $args[$key] = intval($ms[$num]);
          } else {
            $args[$key] = $ms[$num];
          }
        }
        $num++;
      }
    } else {
      return false;
    }
    return $args;
  }
}