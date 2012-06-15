<?php

if (check_table($to)) {
  $args   = array_slice($args, 1);
  $fields = array_keys(db::columns($to));

  if ( ! $args) {
    error(ln('db.table_fields_missing', array('name' => $to)));
  } else {
    foreach ($args as $one) {
      @list($name, $type, $length) = explode(':', $one);

      $col = array($type);

      $length && $col []= $length;

      if ( ! check_column($type)) {
        error(ln('db.unknown_field', array('type' => $type, 'name' => $name)));
      } elseif (in_array($name, $fields)) {
        error(ln('db.column_already_exists', array('name' => $name)));
      } else {
        success(ln('db.column_building', array('type' => $type, 'name' => $name)));
        build_migration('add_column', $to, $name, $col);
        done();
      }
    }
  }
}

/* EOF: ./stack/scripts/db/scripts/add_column.php */
