<?php

chdir(__DIR__);

call_user_func(function () {
  require 'tetlphp/framework/initialize.php';

  import('application');

  run(function () {
  });
});
