Msful
=========


## Micro Service Restful Framework

微服务专用PHP框架

### 使用demo

```php

$app = new Msful_App();
$app->get('/demo', function() {
  return 'get /demo';
});

$app->post('/demo', function() {
  return 'post /demo';
});


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