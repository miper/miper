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

$app->get('/user/#{uid}')
  ->service('user::getUser')
  ->output()
  ->end()
  ;

$app->get('/user/recommends')
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
