README
=============


Docs格式
-----------


### return


```php
/**
 * 获取路由的相关配置
 * @return array 配置内容
 * -array
 *   -@methodName = @url
 *     @methodName(string, 方法名)
 *     @url(string, format="#method #url_glob")
 *       #method(
 *         enum, 
 *         "请求方法，如get，post，或者get|post", 
 *         ['get', 'post', 'put', 'delete']
 *         )
 *       #url_glob(
 *         string, 
 *         "网址格式，如/foo/bar, 或者/foo/#{user_id}"
 *         )
 * 
 */
```

```php
/**
 * -array
 *   -array
 *     -item_id(string, 项目ID)
 *     -title(string,标题)
 *     -tags(array, 标记的标签)
 *       @item(string, 标签名)
 */
 function(){}
```


routers.conf.php
```php

$this
  ->when('get', '/foo1/#{uid}/bar')
  ->exec('service', [
    'service' => 'foo1',
    'func'    => [
      'getBar1', 
      'get_bar2',
    ],
  ])
  ->exec('wrapper', array(
    'getBar1'   => 'bar1',
    'get_bar2'  => 'bar2',
  ))
  ->exec('view', [
      'path'    => '/foo/bar',
    ],
    function() {
      return $this->terminal = 'wap',
    }
  )
  ->exec('wrapper', array(
    '@default' => 'html',
    'bar1'     => '@unset',
    'bar2'     => array(
      '@unset'    => array('create_time', 'user_status'),
      'password'  => '@unset',
      'uname'     => 'username',
      'avatar'    => '@helper:storage',
    ),
  ))
  ;

$this->get('/user/#{uid}', function() {
  $this
    ->exec('service', '/user/getUser')
    ->exec('view', '/user/user', function() {
      return $this->request->format == 'html';
    })
    ;
})
  

$this->get('/user/#{uid}/logined', function() {
  $this->exec('service', '/user/getUser')
    ->exec('service', 
      '/user/updateUser', 
      // 当用户登录大于5分钟才执行修改
      function($data) {
        return $data && $data['last_login_time'] < time() - 5*60;
      },
      // 当前一个判断为true时，通过该闭包将数据传入/user/updateUser接口
      function($data) {
        return array(
          'uid' => $data,
          'info' => array(
            'last_login_time' => time(),
          ),
        );
      })
    ;
});

$this->post('/user/#{uid}', function() {
  $this->exec('service', '/user/addUser');
});
```php