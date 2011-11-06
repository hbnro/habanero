<?php

info(ln('db.verifying_database'));

bold(DB_DSN);

$test = db::tables();

if (empty($test)) {
  error(ln('db.without_tables'));
} else {
  success(ln('db.tables'));

  foreach ($test as $one) {
    $count = (int) db::result(db::select($one, 'COUNT(*)'));

    $keys  = array_keys(db::columns($one));
    $keys  = sprintf('\clight_gray(%s)\c', join(')\c, \clight_gray(', $keys));

    $text  = sprintf("\byellow($one)\b ($keys)\n  => $count");

    cli::writeln($text);
  }
}

bold(ln('tetl.done'));

/* EOF: ./stack/library/db/scripts/st.php */
