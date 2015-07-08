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
   * @param  mixed      $options  选项
   * @param  closure    $cause    条件
   * @param  closure    $wrapper  封装器
   * @param  mixed      $datas   输入数据
   * @return boolean 返回false，表示后续终端执行
   */
  function pipe($app, $options, $cause, $wrapper, &$datas);
}