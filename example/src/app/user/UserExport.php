<?php

/**
 * 用户类
 * test
 * @author  ronnie<dengxiaolong@hunbasha.com>
 * @since  2015/7/8 16:49:23
 * @param string $avatar 头像地址
 * @param enum[int] $status 状态 vars(-1:删除, -2:禁用, 1:正常)
 * @param string $uname 用户名
 */
class UserExport
{
  /**
   * 获取用户信息
   * @param  int $uid 用户ID
   * @param [boolean=false] $detail 是否返回详细情况
   * @return array | false  用户信息，没有返回false
   * -array
   *   -uid(int, 用户ID)
   *   -string uname 用户昵称
   *   -sex enum[int] 性别 "[1(男),2(女)]"
   * 
   * %case $detail=true
   *   -avatar
   *   -create_time
   *   -status
   * %endcase
   */
  function getUser($uid, $detail = false)
  {
    return array(
      'uid' => $uid,
      'uname' => 'ronnie',
      'sex' => 1,
      'flag' => 3,
    );
  }

  /**
   * 获取指定的城市的推荐用户
   * @param  int $cityId 城市ID
   * @param  [int=20] $num 数量
   * @return array
   * -array
   *  -int $uid 用户id
   */
  function getRecommendUserIds($cityId, $num = 20)
  {
    return array(
      rand(30, 44),
      rand(334,343),
      rand(223, 243),
      rand(1932, 2234),
      );
  }

  /**
   * 批量获取用户
   * @param  [type]
   * @return [type]
   */
  function batchGetUsers($userIds)
  {
    $ret = array();
    foreach($userIds as $uid) {
      $ret[] = array(
        'uid' => $uid,
        'uname' => 'uname'.rand(10000, 99999),
        'sex' => rand(1, 2),
        'flag' => rand(1,4),
      );
    }
    return $ret;
  }

  /**
   * 删除用户
   * @param  int $uid 用户ID
   */
  function deleteUser($uid)
  {

  }

  /**
   * 更新用户信息
   * @param  int $uid  用户ID
   * @param  array  $data 用户信息
   * -array
   *   -uname(string, 账号)
   *   -password(string, 密码)
   *   -sex(enum[int], 状态)
   */
  function updateUser($uid, array $data)
  {

  }
}


// $this->get('/user/#{uid}', array('Jiehun_Service_User', 'getUser'));