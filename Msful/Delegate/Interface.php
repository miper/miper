<?php
/**
 * 
 * @authors ronnie (comdeng@live.com)
 * @date    2015-07-09 08:48:43
 * @version $Id$
 */

interface Msful_Delegate_Interface
{
  /**
   * 执行代理流程
   * @param  Msful_App $app [description]
   * @return [type]         [description]
   */
  function delegate(Msful_App $app);
}