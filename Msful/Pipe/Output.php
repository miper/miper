<?php
/**
 * 输出的pipe组件
 * @authors ronnie (comdeng@live.com)
 * @date    2015-07-08 23:59:03
 * @version $Id$
 */

class Msful_Pipe_Output implements Msful_Pipe_Interface
{
  
  function handle($app, $force)
  {
    if (!$force && $app->code != Msful_Const::HTTP_CODE_OK) {
      return false;
    }

    $format = $app->request->format; 
    $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $ret = $app->datas;
    switch($format) {
      case Msful_Const::FORMAT_JSON:
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode(array(
          'c' => $app->code,
          'd' => $ret,
          't'  => floatval(sprintf('%.3f', (microtime(true) - $app->request->reqTime)*1000)),
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
        if ( ($callback = $app->request->get('callback')) ) {
          echo sprintf('%s(%s);', $callback, json_encode(array(
            'c' => $app->code,
            'd' => $ret,
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