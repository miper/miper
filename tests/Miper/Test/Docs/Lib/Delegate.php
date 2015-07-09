<?php

class Miper_Test_Docs_Lib_Delegate implements Miper_Delegate_Interface
{
  function delegate(Miper_App $app)
  {
    $app->get('/user/#{uid}')
      ->call(['Miper_Test_Docs_Lib_User_Service', 'getUser'])
      ->output()
      ->end();

    $app->get('/user/recommends/')
      ->call(['Miper_Test_Docs_Lib_User_Service', 'getRecommendUserIds'], 'userIds')
      ->call(['Miper_Test_Docs_Lib_User_Service', 'getUsers'])
      ->output()
      ->end();
  }
}