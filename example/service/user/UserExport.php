<?php

/**
 * 用户类
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
    );
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