<?php
$this->when('get', '/user/#{uid}')
  ->pipe('service', 'user::getUser')
  ->output();
  ;