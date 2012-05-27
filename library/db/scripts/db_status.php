<?php

info(ln('db.verifying_databases'));

$name = db()->name;
$dsn  = db()->dsn;

bold("$name - $dsn");

$test = db::tables();

if (empty($test)) {
  error(ln('db.without_tables'));
} else {
  success(ln('db.tables'));

  foreach ($test as $tbl) {
    $count = (int) db::result(db::select($tbl, 'COUNT(*)'));

    $keys  = array_keys(db::columns($tbl));
    $keys  = sprintf('\clight_gray(%s)\c', join(')\c, \clight_gray(', $keys));

    $text  = sprintf("\byellow($tbl)\b ($keys)\n  => $count");

    cli::writeln(cli::format($text));
  }
}

done();

/* EOF: ./library/db/scripts/db_status.php */
