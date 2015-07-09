<?php
/**
 * 视图处理流程
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-09 18:20:13
 * @version   1.0.0
 */

class Miper_Pipe_View implements Miper_Pipe_Interface
{
  const DEFAULT_VIEWER = 'Miper_View_Simple';
  /**
   * 管道接口
   * @param  Miper_App  $app      APP
   * @param  mixed      $options  选项
   * @return boolean 返回false，表示后续终端执行
   */
  function handle($app, $options)
  {

    if (is_string($options)) {
      $viewer = self::DEFAULT_VIEWER;
      $path = $options;
    } else {
      list($viewer, $path) = $options;
    }

    $handler = new $viewer();
    return $handler->fetch($path, $app->datas);
  }
}