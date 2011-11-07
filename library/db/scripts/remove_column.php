<?php

if (check_table($from)) {
  $fields = array_keys(db::columns($from));
  $args   = array_slice(func_get_args(), 1);

  if ( ! $args) {
    error(ln('db.table_fields_missing', array('name' => $from)));
  } else {
    foreach ($args as $one) {
      if ( ! in_array($one, $fields)) {
        error(ln('db.column_not_exists', array('name' => $one, 'table' => $from)));
      } else {
        success(ln('db.column_dropping', array('name' => $one)));
        build_migration('remove_column', $from, $one);
        done();
      }
    }
  }
}

/* EOF: ./library/db/scripts/remove_column.php */
