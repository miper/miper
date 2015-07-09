<?php
/**
 * 测试文档生成
 * @author    ronnie<comdeng@live.com>
 * @since     2015-07-09 15:15:04
 * @version   1.0.0
 */

class Miper_Test_Docs_Test extends PHPUnit_Framework_TestCase 
{
  /**
   * 测试导航
   * @return [type] [description]
   */
  function testList()
  {
    $doc = new Miper_Docs_Processor(__DIR__.'/Lib/', 'Delegate.php', array(

      ));
    $navs = $doc->getNavs();
    //var_dump($navs);
    $this->assertEquals('foo', 'foo');
  }

  function testClass()
  {
    $this->assertEquals('2', '2');
  }
}