Msful
=========


## Micro Service Restful Framework

微服务专用PHP框架

### 使用demo

```php


$app = Msful_App::getAppInstance();

require_once '/app/user/UserExport.php';

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