<?php
/**
 * 管道接口
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-08 19:51:39
 * @version   1.0.0
 */

interface Msful_Pipe_Interface
{
  /**
   * 管道接口
   * @param  Msful_App  $app      APP
   * @param  mixed      $options  选项
   * @return boolean 返回false，表示后续终端执行
   */
  function handle($app, $options);
}