<?php

if (cli::flag('schema')) {
  info(ln('db.verifying_schema'));

  $schema_file = CWD.DS.'db'.DS.'schema'.EXT;

  $path = str_replace(CWD.DS, '', $schema_file);
  success(ln('db.loading_schema', array('path' => $path)));

  require $schema_file;
} else {
  if ( ! cli::flag('seed')) {
    info(ln('db.verifying_database'));
    bold(DB_DSN);

    if (cli::flag('drop-all')) {
      foreach (db::tables() as $one) {
        notice(ln('db.table_dropping', array('name' => $one)));
        drop_table($one);
      }
    }


    if ($test = findfile(CWD.DS.'db'.DS.'migrate', '*'.EXT)) {
      sort($test);

      success(ln('db.migrating_database'));

      foreach ($test as $migration_file) {
        $path = str_replace(CWD.DS, '', $migration_file);
        notice(ln('db.run_migration', array('path' => $path)));
        require $migration_file;
      }
      db_generator::schema();
    } else {
      error(ln('db.without_migrations'));
    }
  }

  info(ln('db.verifying_seed'));

  $seed_file = CWD.DS.'db'.DS.'seeds'.EXT;

  if ( ! is_file($seed_file)) {
    error(ln('db.without_seed'));
  } else {
    $path = str_replace(CWD.DS, '', $seed_file);
    success(ln('db.loading_seed', array('path' => $path)));
    require $seed_file;
  }
}

done();

/* EOF: ./library/db/scripts/make.php */
