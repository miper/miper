Msful
=========


## Micro Service Restful Framework

微服务专用PHP框架。

## Msful的核心思想

1. 提供Restful风格的接口
2. 基于流水线的处理流程
3. 将接口作为基础组件自由拼装
4. 实时生成和浏览接口文档

### 使用demo

```php

define('SERVICE_ROOT', dirname(__DIR__).'/example/service/');

$app = new Msful_App();

$app
  // get请求，网址匹配/user/#{uid}时进行后续处理，如访问/user/40
  // 等于 ->pipe('when', 'get /user/#{uid}')
  ->get('/user/#{uid}')  
  // 将请求映射到user模块的getUser方法
  // 等于 ->pipe('service', 'user::getUser')
  ->service('user::getUser')
  // 输出结果
  // 等于 ->pipe('output')
  ->output()
  // 结束该流程
  ->end()
  ;

$app
  // get请求，匹配/user/recommens网址
  ->get('/user/recommends')
  // 将请求先映射到user模块的getRecommendUserIds，
  // 并将返回的用户ID数组封装成array('userIds' => array(2,3,22))的形式，供下一个接口使用
  ->service('user::getRecommendUserIds', 'userIds')
  // 将前一个接口获取的数据作为参数传递给user模块的batchGetUsers函数
  ->service('user::batchGetUsers', 
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
