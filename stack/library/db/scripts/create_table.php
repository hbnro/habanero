<?php

info(ln('db.verifying_structure'));

if ( ! $table) {
  error(ln('db.table_name_missing'));
} elseif (in_array($table, db::tables())) {
  error(ln('db.table_already_exists', array('name' => $table)));
} else {
  if ( ! $args) {
    error(ln('db.table_fields_missing', array('name' => $table)));
  } else {
    $pk     =
    $fail   = FALSE;
    $fields = array();

    foreach ($args as $one) {
      @list($name, $type, $length) = explode(':', $one);

      if ( ! in_array($type, static::$types)) {
        error(ln('db.unknown_field', array('type' => $type, 'name' => $name)));

        $fail = TRUE;
      } else {
        notice(ln('db.success_field_type', array('type' => $type, 'name' => $name)));

        $fields[$name] = (array) $type;

        $length && is_num($length) && $fields[$name] []= $length;

        $type === 'primary_key' && $pk = TRUE;
      }
    }


    if (cli::flag('timestamps')) {
      $fields['created_at']  =
      $fields['modified_at'] = array('timestamp');
    }

    if ( ! cli::flag('no-index')) {
      ! $pk && $fields['id'] = array('primary_key');
    }


    if ($fail) {
      error(ln('db.table_fields_missing', array('name' => $table)));
    } else {
      success(ln('db.table_building', array('name' => $table)));
      db_generator::migrate('create_table', $table, $fields, array('force' => TRUE));

      if (cli::flag('model')) {
        $out_file = mkpath(option('mvc.models_path')).DS.$table.EXT;

        if ( ! is_file($out_file)) {
          success(ln('db.model_class_building', array('name' => $table)));

          $code   = "<?php\n\nclass $table extends dbmodel"
                  . "\n{\n}\n";

          write($out_file, $code);
        }
      }
    }
  }
}

done();

/* EOF: ./stack/library/db/scripts/create_table.php */
