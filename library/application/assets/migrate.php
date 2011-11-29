<?php
require 'tetlphp/framework/initialize.php';

run(function () {
  import('db');
  import('a_record');

  echo 'Migrating database structure...';

  require 'database/schema.php';
  require 'database/seeds.php';

  echo 'OK';
});
