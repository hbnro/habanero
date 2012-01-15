<?php



info(ln('db.verifying_databases'));

foreach ((array) option('database') as $name => $dsn) {
  bold("$name - $dsn");

  $res  = db::connect($dsn);
  $test = $res->tables();

  if (empty($test)) {
    error(ln('db.without_tables'));
  } else {
    success(ln('db.tables'));

    foreach ($test as $tbl) {
      $count = (int) $res->result($res->select($tbl, 'COUNT(*)'));

      $keys  = array_keys($res->columns($tbl));
      $keys  = sprintf('\clight_gray(%s)\c', join(')\c, \clight_gray(', $keys));

      $text  = sprintf("\byellow($tbl)\b ($keys)\n  => $count");

      cli::writeln($text);
    }
    done();
  }

}

/* EOF: ./library/db/scripts/db_status.php */
