<?php

if (check_table($from)) {
  $fields = db::columns($from);
  $args   = array_slice(func_get_args(), 1);

  if ( ! $args) {
    error(ln('db.table_fields_missing', array('name' => $from)));
  } else {
    $c = sizeof($args);

    for ($i = 0; $i < $c; $i += 2) {
      $one  = $args[$i];
      $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

      if ( ! array_key_exists($one, $fields)) {
        error(ln('db.column_not_exists', array('name' => $one, 'table' => $from)));
      } elseif ( ! $next) {
        error(ln('db.column_type_missing'));
      } else {
        @list($type, $length) = explode(':', $next);

        $col = array($type);

        $length && $col []= $length;

        if ( ! check_column($type)) {
          error(ln('db.unknown_field', array('type' => $type, 'name' => $one)));
        } elseif ($fields[$one]['type'] === $type) {
          error(ln('db.column_already_exists', array('name' => $one)));
        } else {
          success(ln('db.column_changing', array('type' => $type, 'name' => $one)));
          build_migration('change_column', $from, $one, $col);
          done();
        }
      }
    }
  }
}

/* EOF: ./stack/library/db/scripts/change_column.php */
