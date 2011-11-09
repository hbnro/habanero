<?php

if (check_table($from)) {
  $args   = array_slice($args, 1);
  $fields = array_keys(db::columns($from));

  if ( ! $args) {
    error(ln('db.table_fields_missing', array('name' => $from)));
  } else {
    $c = sizeof($args);

    for ($i = 0; $i < $c; $i += 2) {
      $one  = $args[$i];
      $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

      if ( ! in_array($one, $fields)) {
        error(ln('db.column_not_exists', array('name' => $one, 'table' => $from)));
      } elseif ( ! $next) {
        error(ln('db.column_name_missing'));
      } else {
        success(ln('db.column_renaming', array('from' => $one, 'to' => $next)));
        build_migration('rename_column', $from, $one, $next);
        done();
      }
    }
  }
}

/* EOF: ./library/db/scripts/rename_column.php */
