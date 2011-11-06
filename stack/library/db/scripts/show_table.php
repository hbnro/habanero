<?php

if (check_table($table)) {
  success(ln('db.table_show_columns', array('name' => $table)));

  $set =
  $heads = array();

  foreach (db::columns($table) as $name => $one) {
    if (empty($heads)) {
      $heads = array_keys($one);
    }

    $set[$name]  = array($name);
    $set[$name] += $one;
  }

  array_unshift($heads, 'name');
  cli::table($set, $heads);


  notice(ln('db.table_show_indexes', array('name' => $table)));

  $idx = array();
  $all = db::indexes($table);

  if ( ! $all) {
    error(ln('db.without_indexes', array('name' => $table)));
  } else {
    $idx = array();

    foreach ($all as $name => $one) {
      $idx []= array($name, join(', ', $one['column']), $one['unique']);
    }
    cli::table($idx, array('name', 'columns', 'unique'));
  }
}

done();

/* EOF: ./stack/library/db/scripts/show_table.php */
