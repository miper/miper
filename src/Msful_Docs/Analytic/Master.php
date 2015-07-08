<?php
/**
 * 语法分析工具
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-08 16:12:24
 * @version 1.0.0
 */
require_once __DIR__.'/Param.php';
require_once __DIR__.'/Class.php';
require_once __DIR__.'/Method.php';

class Msful_Docs_Analytic_Master
{
    static function analytic($path, $clsName, $routers = array())
    {
      if (!is_readable($path)) {
        throw new \Exception('msdocs.pathNotReadable path:'.$path);
      }

      require_once $path;
      if (!class_exists($clsName)) {
        throw new \Exception('msdocs.classNotFound class:'.$clsName);
      }

      $aClass = new Msful_Docs_Analytic_Class();
      $aClass->name = $clsName;
      $aClass->path = $path;

      $cls = new ReflectionClass($clsName);
      $comments = self::anaComment($cls->getDocComment());
      $aClass->desc = $comments['description'];

      if (isset($comments['param'])) {
        $aClass->params = self::anaParam($comments['param']);
      }

      // 分析函数
      $aClass->methods = self::anaRouters($cls, $routers);
      echo '<pre>';
      print_r($aClass);
      echo '</pre>';
      die();
    }


    /**
   * 分析评论
   * @param string $comment
   * @return array
   */
  static function anaComment($comment)
  {
    if (!$comment) {
      return array('description' => array());
    }
    $prefix = substr($comment, 0, 2);
    if ($prefix == '//') {
      $ret = self::_anaShortComment(substr($comment, 2));
    } else if ($prefix == '/*') {
      $ret = self::_anaLongComment(substr($comment, 2, -2));
    } else {
      throw new \Exception('docs.u_illegalComment');
    }
    if (empty($ret['description'])) {
      if (!empty($ret['desc'])) {
        $ret['description'] = $ret['desc'];
        unset($ret['desc']);
      } else if (isset($ret['brief'])) {
        $ret['description'] = $ret['brief'];
        unset($ret['brief']);
      } else {
        $ret['description'] = '';
      }
    }
    
    return $ret;
  }
  
  /**
   * 分析短的评论
   * @param string $comment
   */
  static function _anaShortComment($comment)
  {
    return array(
      'description' => $comment,
    );
  }
  
  /**
   * 分析长的评论
   * @param string $comment
   */
  static function _anaLongComment($comment)
  {
    $cs = explode("\n", $comment);
    foreach($cs as $key => &$line) {
      $_line = trim($line);
      if (!$_line) {
        unset($cs[$key]);
        continue;
      }
      if ($_line{0} == '*') {
        $line = substr($_line, 1);
      }
      if (!trim($line)) {
        unset($cs[$key]);
      }
    }
    $ret = array();
    $lastKey = 'description';
    foreach($cs as $line) {
      if (preg_match('#^\s*@([a-z][a-z0-9]+)\s*(.*)#i', $line, $matches)) {
        $key = strtolower($matches[1]);
        $value = $matches[2];
        $ret[$key][] = $value;
        $lastKey = $key;
      } else {
        $ret[$lastKey][] = "\0".trim($line);
      }
    }
    return $ret;
  }

  static function anaParam($params)
  {
    $lastKey = '';
    $result = array();
    $list = array();

    
    foreach($params as $param) {
      if (!$param) {
        continue;
      }
      // 以\0开头的表示是对上一条参数的注释
      if ($param{0} == "\0") {
        if ($lastKey != '') {
          $result[$lastKey]['desc'][] = substr($param, 1);
        }
      } else {
        $arr = preg_split("/[\s \t\r\n]+/", trim($param, ' '));
        if (count($arr) < 2) {
          continue;
        }
        $type = array_shift($arr);
        
        if ($type{0} == '$') {
          $lastKey = substr($type, 1);
          $type = 'mixed';
        } else {
          $lastKey = array_shift($arr);
          
          // 类型推断
          if (strpos($type, '|') === false && $lastKey{0} != '$' && !in_array($type, self::$commonTypes) &&  in_array($lastKey, self::$commonTypes) ) {
            $_tmp = $type;
            $type = $lastKey;
            $lastKey = $_tmp;
          }
        }

        $default = '';
        $optional = false;
        
        if ($lastKey{0} == '[' && substr($lastKey, -1) == ']') {
          $lastKey = trim(substr($lastKey, 1, -1));
          $optional = true;
          
          if ( ($pos = strpos($lastKey, '=')) !== false ) {
            $default = trim(substr($lastKey, $pos + 1));
            $lastKey = trim(substr($lastKey, 0, $pos));
          }
        }
        if ($lastKey{0} == '$') {
          $lastKey = substr($lastKey, 1);
        }
        

        $param = new Msful_Docs_Analytic_Param();
        $param->name = $lastKey;
        $param->type = $type;
        $param->desc = implode("\n", $arr);
        $param->optional = $optional;
        $param->default = $default;

        $list[$lastKey] = $param;
      }
    }
    return $list;
  }

  static function anaRouters($cls, $routers)
  {
    $ret = array();
    foreach($routers as $methodName => $router) {
      if (!$cls->hasMethod($methodName)) {
        continue;
      }

      $router = trim($router);
      list($rmethod, $glob) = explode(' ', $router);
      if (strpos($rmethod, '|') !== false) {
        $rmethod = array_map('trim', explode('|', $rmethod));
      }

      $aMethod = self::anaMethod($cls->getMethod($methodName));
      $aMethod->url = $glob;
      $ret[$methodName] = $aMethod;
    }

    return $ret;
  }

  /**
   * 分析方法
   * @param  [type]  $method   [description]
   * @param  boolean $docFirst [description]
   * @return [type]            [description]
   */
  static function anaMethod($method, $docFirst = false)
  {
    if (!$method->isPublic() 
        || $method->isProtected() 
        || $method->isConstructor() 
        || $method->isDestructor() 
        || $method->isStatic()) {
      return false;
    }
    $name = $method->getName();
    
    if (!$docFirst) {
      // 先根据方法本身获取最准确的参数情况
      $params = $method->getParameters();
      $options = array();
      foreach($params as $param) {
        $name = $param->getName();
        $options[$name] = array(
          'name'      => $name,
          'optional'  => $param->isOptional(),
          'isArray'   => $param->isArray(),
          'default'   => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL,
        );
      }
        
      $result = self::anaComment($method->getDocComment());
      self::_innerAnaMethod($result);
        
      foreach($options as &$option) {
        if (isset($result['param'][$option['name']])) {
          $_info = $result['param'][$option['name']];
          $option['desc'] = $_info['desc'];
          $option['type'] = $_info['type'];
        } else {
          $option['desc'] = '';
          $option['type'] = '';
        }
      }
      $result['param'] = $options;
    } else {
      $result = self::anaComment($method->getDocComment());
      self::_innerAnaMethod($result);
    }
    if (!isset($result['name'])) {
      $result['name'] = $method->getName();
    }
    $aMethod = new Msful_Docs_Analytic_Method();
    foreach($result as $name => $value) {
      $aMethod->$name = $value;
    }
    return $aMethod;
  }

  private static function _innerAnaMethod(&$result) 
  {
    if (isset($result['description'])) {
      if (is_array($result['description'])) {
        $result['description'] = implode("\n", $result['description']);
      }
      if ($result['description'] && $result['description'][0] == "\0") {
        $result['description'] = substr($result['description'], 1);
      }
    } else {
      $result['description'] = '';
    }
    
    if (!empty($result['param'])) {
      $params = $result['param'];
      $result['param'] = array();
      $lastKey = '';
      foreach($params as $param) {
        if (!$param) {
          continue;
        }
        // 以\0开头的表示是对上一条参数的注释
        if ($param{0} == "\0") {
          if ($lastKey != '') {
            $result['param'][$lastKey]['desc'][] = substr($param, 1);
          }
        } else {
          $arr = preg_split("/[\s \t\r\n]+/", trim($param, ' '));
          if (count($arr) < 2) {
            continue;
          }
          $type = array_shift($arr);
          
          if ($type{0} == '$') {
            $lastKey = substr($type, 1);
            $type = 'mixed';
          } else {
            $lastKey = array_shift($arr);
            
            // 类型推断
            if (strpos($type, '|') === false && $lastKey{0} != '$' && !in_array($type, self::$commonTypes) &&  in_array($lastKey, self::$commonTypes) ) {
              $_tmp = $type;
              $type = $lastKey;
              $lastKey = $_tmp;
            }
          }

          $default = '';
          $optional = false;
          
          if ($lastKey{0} == '[' && substr($lastKey, -1) == ']') {
            $lastKey = trim(substr($lastKey, 1, -1));
            $optional = true;
            
            if ( ($pos = strpos($lastKey, '=')) !== false ) {
              $default = trim(substr($lastKey, $pos + 1));
              $lastKey = trim(substr($lastKey, 0, $pos));
            }
          }
          if ($lastKey{0} == '$') {
            $lastKey = substr($lastKey, 1);
          }
          
          $result['param'][$lastKey] = array(
            'desc' => array(implode(' ', $arr)),
            'type' => $type,
            'name' => $lastKey,
            'optional' => $optional,
            'default'  => $default,
          );
        }
      }
      
      foreach($result['param'] as $key => $value) {
        $result['param'][$key]['desc'] = implode("", $value['desc']);
      }
    } else {
      $result['param'] = array();
    }
    
    if (!empty($result['return'])) {
      $first = array_shift($result['return']);
      $return = $result['return'];
      $arr = preg_split("/\s+/", trim($first));
      if (count($arr) >= 1) {
        $type = array_shift($arr);
        $result['return'] = array(
          'type' => $type,
          'desc' => array(implode(' ', $arr)),
        );
        $result['return']['detail'] = array();
        foreach($return as $value) {
          if ($value[0] == ' ' || $value[0] == "\0") {
            $value = substr($value, 1);
          }
          //$result['return']['desc'][] = preg_replace(array('/(  |\t)/'), ' ', $value);
          $result['return']['detail'][] = $value;
        }
        $detail = $result['return']['detail'] = implode("\n", $result['return']['detail']);
        
        if (strpos($detail, '</code>') !== false) {
          $detail = preg_replace('#^[^\w]*<code[^>]*>(.*)</code>\s*$#s', '\1', $detail);
        }
        
        if (strpos($detail, '</pre>') === false && strpos($detail, '</code>') === false) {
          switch($result['return']['type']) {
            case 'array':
              $lang = 'php';
              break;
            default:
              $lang = 'js';
              break;
          }
          $result['return']['detail'] = '<pre lang="'.$lang.'">'.$detail.'</pre>';
        }
      } else {
        $result['return'] = array();
      }
    }
    
    if (!empty($result['throws'])) {
      $throws = array();
      foreach($result['throws'] as $throw) {
        if (preg_match('/^\s*([a-z][a-z\.\_\-0-9]+)\b(.*)$/i', $throw, $matches)) {
          $throws[] = array(
            'code'  => $matches[1],
            'desc'  => trim($matches[2]),
          );
        }
      }
      $result['throws'] = $throws;
    }
  }
}