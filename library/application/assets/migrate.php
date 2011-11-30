<?php

require 'tetlphp/framework/initialize.php';

run(function () {
  import('db');
  import('a_record');

  echo 'Migrating database structure...';

  require getcwd().DS.'database'.DS.'schema'.EXT;
  require getcwd().DS.'database'.DS.'seeds'.EXT;

  echo "OK\n";
});
