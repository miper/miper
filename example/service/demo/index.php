<?php
/**
 * 
 * @author ronnie<comdeng@live.com>
 * @since    2015-07-07 18:20:30
 * @version 1.0.0
 */
$this->route(['get', 'post'], '/demo/hello', function($args, $req) {
  return 'hello,world.'.$req->method;
});

$this->route('get', '/demo/test', function() {
  return [
    ['a' => 'b'],
  ];
});

$this->route('get', '^/demo/(\d+)$', function($args) {
  return $args;
});