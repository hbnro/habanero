<?php

if (check_table($to)) {
  if ( ! $name) {
    error(ln('db.index_name_missing', array('name' => $to)));
  } else {
    $args   = array_slice($args, 2);
    $unique = cli::flag('unique');
    $idx    = db::indexes($to);

    if ( ! $args) {
      error(ln('db.index_columns_missing'));
    } elseif (array_key_exists($name, $idx)) {
      error(ln('db.index_already_exists', array('name' => $name, 'table' => $to)));
    } else {
      $col    = array();
      $fields = array_keys(db::columns($to));

      foreach ($args as $one) {
        if ( ! in_array($one, $fields)) {
          error(ln('db.column_not_exists', array('name' => $one, 'table' => $to)));
        } else {
          notice(ln('db.success_column_index', array('name' => $one, 'table' => $to)));
          $col []= $one;
        }
      }

      if (sizeof($col) === sizeof($args)) {
        success(ln('db.indexing_table', array('name' => $name, 'table' => $to)));
        build_migration('add_index', $to, $col, array(
          'unique' => !! $unique,
          'name' => $name,
        ));
        done();
      }
    }
  }
}

/* EOF: ./stack/scripts/db/scripts/add_index.php */
