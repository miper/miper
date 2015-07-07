<?php
$this->get('/user/#{uid}')
  ->service('user::getUser')
  ->output()
  ->end()
  ;

$this->get('/user/recommends')
  ->service('user::getRecommendUserIds', 'userIds')
  ->service('user::batchGetUsers', array(
      '@item' => array(
          'uid' => 'user_id',
          'flag' => '@unset'
        )
    ))
  ->output()
  ->end()
  ;