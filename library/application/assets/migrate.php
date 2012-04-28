<?php

require 'tetlphp/framework/initialize.php';

run(function () {
  import('db');
  import('a_record');

  echo "Migrating database structure...\n";

  require APP_PATH.DS.'database'.DS.'schema'.EXT;
  require APP_PATH.DS.'database'.DS.'seeds'.EXT;

  echo "OK\n";
});
