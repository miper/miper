<?php
/**
 * 
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-08 15:52:56
 * @version 1.0.0
 */

class Msful_Docs_Service implements Msful_Router_Interface
{
  private $conf;

  function __construct($conf = array())
  {
    $this->conf = $conf;
  }

  function _routers()
  {
    return array(
      'homepage' => 'get /docs/'
      );
  }

  function homepage()
  {
    $glob = $this->conf['glob'];

    $files = glob($glob);
    if (!$files) {
      return array();
    }
    require_once __DIR__.'/Analytic/Master.php';

    foreach($files as $file) {
      $config = include($file);
      $comments = Msful_Docs_Analytic_Master::analytic($config['path'], $config['class'], $config['routers']);
    }
    return $comments;
  }
}