<?php

if (cli::flag('schema')) {
  info(ln('db.verifying_schema'));

  $schema_file = APP_PATH.DS.'database'.DS.'schema'.EXT;

  $path = str_replace(APP_PATH.DS, '', $schema_file);
  success(ln('db.loading_schema', array('path' => $path)));

  if (is_file($schema_file)) {
    require $schema_file;
  } else {
    error(ln('db.without_schema', array('path' => $path)));
  }
} else {
  info(ln('db.verifying_databases'));

  $cache  = all_migrations();
  $latest = sizeof($cache);
  $name   = db()->name;
  $dsn    = db()->dsn;

  bold("$name - $dsn");

  if (cli::flag('drop-all')) {
    $cache = array();

    db::delete('migration_history');

    foreach (db::tables() as $one) {
      if ($one <> 'migration_history') {
        notice(ln('db.table_dropping', array('name' => $one)));
        drop_table($one);
      }
    }
  }


  if ($test = findfile(APP_PATH.DS.'database'.DS.'migrate', '*'.EXT)) {
    sort($test);

    success(ln('db.migrating_database'));

    foreach ($test as $migration_file) {
      $name = extn($migration_file, TRUE);
      $path = str_replace(APP_PATH.DS, '', $migration_file);

      if ( ! in_array($name, $cache)) {
        notice(ln('db.run_migration', array('path' => $path)));
        require $migration_file;
        add_migration($name);
        $latest += 1;
      }
    }

    if (sizeof($cache) == $latest) {
      notice(ln('db.without_changes'));
    } else {
      build_schema();
    }
  } else {
    error(ln('db.without_migrations'));
  }
}


if (cli::flag('seed')) {
  info(ln('db.verifying_seed'));

  $seed_file = APP_PATH.DS.'database'.DS.'seeds'.EXT;

  if ( ! is_file($seed_file)) {
    error(ln('db.without_seed'));
  } else {
    $path = str_replace(APP_PATH.DS, '', $seed_file);
    success(ln('db.loading_seed', array('path' => $path)));
    require $seed_file;
  }
}

done();

/* EOF: ./stack/scripts/db/scripts/make.php */
