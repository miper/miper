Miper
=========


## Micro Service Restful Framework with Restful Style

Restful风格、基于管道(Pipe)技术、使用PHP语言的微服务专有框架。

借鉴了Gulp以及linux里边的管道技术的思想，通过对数据流的控制和管理，给微服务的开发带来前所未有的快感和体验。

## Miper的核心思想

1. 提供Restful风格的接口
2. 基于流水线的处理流程
3. 将接口作为基础组件自由拼装
4. 实时生成和浏览接口文档

### 入门

使用`Miper`很容易上手，下面的例子构建了一个简单的接口。

```php
define('VENDOR_DIR', $root.'/vendor/');
define('LIBRARY_DIR', $root.'/src/');

require VENDOR_DIR.'/Miper/Autoload.php';
Miper_Autoload::autoload(VENDOR_DIR, LIBRARY_DIR);

$app = Miper_App::getAppInstance();

// 简单请求
$app->
  get([
    '/hello/world', 
    function($req){
      return 'Hello,World';
    }
  ])
  ->output()
  ->end();

```

你可能觉得，pipe貌似不存在，其实上面的例子等价于
```
// 简单请求
$app->
  pipe('request', array(
    'get',
    '/hello/world', 
    function($req){
      return 'Hello,World';
    })
  )
  ->pipe('output')
  ->end();
```

下面来看一个稍微复杂的例子，这个接口调用另外一个类的函数返回数据并输出结果。


```
require_once '/app/user/UserExport.php';
$app
  // get请求，网址匹配/user/#{uid}时进行后续处理，如访问/user/40
  // 等于 ->pipe('request', ['get', '/user/#{uid}'])
  ->get('/user/#{uid}')  
  // 将请求映射到user模块的getUser方法
  // 等于 ->pipe('call', ['UserExport', 'getUser'])
  ->call(['UserExport', 'getUser'])
  // 输出结果
  // 等于 ->pipe('output')
  ->output()
  // 结束该流程
  ->end()
  ;
```
接下来是一个更加复杂的例子，它利用管道将第一个函数调用结果传给第二个接口，最终输出需要的数据。
```
$app
  // get请求，匹配/user/recommens网址
  ->get('/user/recommends')
  // 将请求先映射到user模块的getRecommendUserIds，
  // 并将返回的用户ID数组封装成array('userIds' => array(2,3,22))的形式，供下一个接口使用
  ->call(['UserExport', 'getRecommendUserIds'], 'userIds')
  // 将前一个接口获取的数据作为参数传递给user模块的batchGetUsers函数
  ->call(['UserExport', 'batchGetUsers'],
    // TODO 用数组模板来约束输出数据的格式
    array(
      '@item' => array(
          'uid' => 'user_id',
          'flag' => '@unset'
        )
    )
  )
  // 输出结果
  ->output()
  // 结束此流程
  ->end()
  ;

```

### nginx 配置
```nginx
server {
    server_name  localhost;
    listen       18600;
    root         ~/msful/example/public/;

    location / { 
      rewrite ^(/.*?)$ /index.php last;
    }   

    location /index.php {
      fastcgi_pass   127.0.0.1:9001;
      fastcgi_index  index.php;
      fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
      fastcgi_param  HTTP_HOST          $host;
      include        fastcgi_params;
    }   
}

```

### TODO

* 文档生成功能
* 数据库ORM/基础功能自动化
* 自动化校验
* 接口数据拼装
* 数据流式及简单逻辑处理
* 自动化测试

** markdown/jade/less支持
