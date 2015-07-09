<?php

class Miper_Test_Docs_Lib_User_Service
{
  /**
   * 获取指定的用户
   * @param  int $uid 用户ID
   * @return array|false      用户信息
   * -array
   *   -int uid 用户ID
   *   -string uname 用户名称
   *   -int sex 性别
   */
  function getUser($uid)
  {

  }

  /**
   * 获取推荐的用户id
   * @param  int  $cityId 城市ID
   * @param  int $num    数量
   * @return array          用户ID列表
   * -array
   *   -$uid 用户ID
   */
  function getRecommendUserIds($cityId, $num = 20)
  {

  }

  /**
   * 批量获取用户
   * @param  array $userIds 用户ID
   * @return array          用户列表
   * -array
   *   -array
   *     -uid
   *     -uname
   *     -sex
   */
  function getUsers($userIds)
  {

  }
}