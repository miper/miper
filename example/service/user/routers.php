<?php
return array(
  'path'    => __DIR__.'/UserExport.php',
  'class'   => 'UserExport',
  'routers' => array(
      'getUser' => 'get /user/#{uid}',
      'addUser' => 'post /user/#{uid}',
    ), 
  );
