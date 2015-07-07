<?php

$this->get('^/user/(\d+)', function($args) {
  return 'get user '.$args[0];
});

$this->post('^/user/(\d+)', function($args) {
  return 'add user '.$args[0];
});

$this->delete('^/user/(\d+)', function($args) {
  return 'delete user '.$args[0];
});

$this->put('^/user/(\d+)', function($args, $req) {
  return 'update user '.$args[0];
});


$this->route('get', '/user/test/30', function($args, $req) {
  return $req->url;
});