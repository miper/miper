<?php
/**
 * 用户接口
 * @authors ronnie (comdeng@live.com)
 * @date    2015-07-09 08:10:55
 * @version $Id$
 */

class Happy_Delegate_User implements Miper_Delegate_Interface
{
  function delegate(Miper_App $app)
  {
    require_once LIBRARY_DIR.'/app/user/UserExport.php';

    $app->get('/user/#{uid}')
      ->call(['UserExport', 'getUser'])
      ->output()
      ->end()
      ;

    $app->get('/user/recommends')
      ->call(['UserExport', 'getRecommendUserIds'], 'userIds')
      ->call(['UserExport', 'batchGetUsers'], array(
          '@item' => array(
              'uid' => 'user_id',
              'flag' => '@unset'
            )
        ))
      ->output()
      ->end()
      ;
  }
}