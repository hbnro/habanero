<?php

if (check_table($from)) {
  $args = array_slice($args, 1);

  if ( ! $args) {
    error(ln('db.index_name_missing', array('name' => $from)));
  } else {
    $idx = db::indexes($from);

    foreach ($args as $one) {
      if ( ! array_key_exists($one, $idx)) {
        error(ln('db.index_not_exists', array('name' => $one, 'table' => $from)));
      } else {
        success(ln('db.index_dropping', array('name' => $one)));
        build_migration('remove_index', $from, $one);
        done();
      }
    }
  }
}

/* EOF: ./stack/scripts/db/scripts/remove_index.php */
