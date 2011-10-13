<?php

import('tetl/db');

i18n::load_path(__DIR__.DS.'locale', 'db');


class db_generator extends prototype
{

  private static $types = array(
            'primary_key',
            'text',
            'string',
            'integer',
            'numeric',
            'float',
            'boolean',
            'binary',
            'timestamp',
            'datetime',
            'date',
            'time',
          );

  final private static function check_table($table) {
    info(ln('db.verifying_structure'));


    if ( ! $table) {
      error(ln('db.table_name_missing'));
    }
    elseif ( ! in_array($table, db::tables())) {
      error(ln('db.table_not_exists', array('name' => $table)));
    }
    else
    {
      return TRUE;
    }
  }

  final private static function schema() {
    info(ln('db.verifying_schema'));

    $out = array();
    $schema_file = CWD.DS.'db'.DS.'schema'.EXT;

    $path = str_replace(CWD.DS, '', $schema_file);
    success(ln('db.updating_schema', array('path' => $path)));

    foreach (db::tables() as $one) {
      $out []= sprintf("create_table('$one', array(");

      foreach (db::columns($one) as $key => $val) {
        $def = array("'{$val['type']}'");

        $val['length'] && $def []= $val['length'];

        $out []= sprintf("  '$key' => array(%s),", join(', ', $def));
      }

      $out []= "), array('force' => TRUE));";
    }

    $out []= '';

    foreach (db::indexes($one) as $key => $val) {
      $def  = array("'name' => '$key'");
      $cols = "'" . join("', '", $val['column']) . "'";

      ! empty($val['unique']) && $def []= "'unique' => TRUE";

      $out []= sprintf("add_index('$one', array($cols), array(%s));", join(', ', $def));
    }

    write($schema_file, sprintf("<?php\n/* %s */\n%s\n", date('Y-m-d H:i:s'), join("\n", $out)));
  }

  final private static function migrate($callback) {
    $args = array_slice(func_get_args(), 1);
    $name = cli::flag('name', $callback);
    $time = time();

    $migration_name = date('YmdHis_', $time).$args[0].'_'.$name;
    $migration_path = mkpath(CWD.DS.'db'.DS.'migrate');
    $migration_file = $migration_path.DS.$migration_name.EXT;


    foreach ($args as $i => $one) {
      if (is_array($one)) {
        $text = var_export($one, TRUE);

        $text = preg_replace('/ \d+\s+=>/', '', $text);
        $text = preg_replace('/array\s+\(/', 'array(', $text);
        $text = preg_replace('/[\'"](\d+)[\'"]/', '\\1', $text);
        $text = preg_replace('/([\'"]\w+[\'"])\s+=>\s+(?=\w+)/s', '\\1 => ', $text);

        $text = str_replace('( ', '(', $text);
        $text = str_replace(',)', ')', $text);

        $args[$i] = $text;
      }
      else
      {
        $args[$i] = "'$one'";
      }
    }


    $code = sprintf("$callback(%s);\n", join(', ', $args));

    if ( ! is_file($migration_file)) {
      $date = date('Y-m-d H:i:s', $time);

      write($migration_file, "<?php\n/* $date */\n$code");
    }
    else
    {
      write($migration_file, $code, 1);
    }

    @eval($code);

    static::schema();
  }

  final public static function st() {
    info(ln('db.verifying_database'));
    bold(DB_DSN);

    $test = db::tables();

    if (empty($test)) {
      error(ln('db.without_tables'));
    }
    else
    {
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
  }

  final public static function make() {
    if (cli::flag('schema')) {
      info(ln('db.verifying_schema'));

      $schema_file = CWD.DS.'db'.DS.'schema'.EXT;

      $path = str_replace(CWD.DS, '', $schema_file);
      success(ln('db.loading_schema', array('path' => $path)));

      require $schema_file;
    }
    else
    {
      if ( ! cli::flag('seed')) {
        info(ln('db.verifying_database'));
        bold(DB_DSN);

        if (cli::flag('drop-all')) {
          foreach (db::tables() as $one) {
            notice(ln('db.table_dropping', array('name' => $one)));
            drop_table($one);
          }
        }


        if ($test = findfile(CWD.DS.'db'.DS.'migrate', '*'.EXT)) {
          sort($test);

          success(ln('db.migrating_database'));

          foreach ($test as $migration_file) {
            $path = str_replace(CWD.DS, '', $migration_file);
            notice(ln('db.run_migration', array('path' => $path)));
            require $migration_file;
          }

          static::schema();
        }
        else
        {
          error(ln('db.without_migrations'));
        }
      }

      info(ln('db.verifying_seed'));

      $seed_file = CWD.DS.'db'.DS.'seeds'.EXT;

      if ( ! is_file($seed_file)) {
        error(ln('db.without_seed'));
      }
      else
      {
        $path = str_replace(CWD.DS, '', $seed_file);
        success(ln('db.loading_seed', array('path' => $path)));
        require $seed_file;
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function show($table = '') {
    if (static::check_table($table)) {
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
      }
      else
      {
        $idx = array();

        foreach ($all as $name => $one) {
          $idx []= array($name, join(', ', $one['column']), $one['unique']);
        }
        cli::table($idx, array('name', 'columns', 'unique'));
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function drop($table = '') {
    if (static::check_table($table)) {
      success(ln('db.table_dropping', array('name' => $table)));
      static::migrate('drop_table', $table);
    }

    bold(ln('tetl.done'));
  }

  final public static function rename($table = '', $to = '') {
    static::check_table($table);

    if ( ! $to) {
      error(ln('db.table_name_missing'));
    }
    elseif (in_array($to, db::tables())) {
      error(ln('db.table_already_exists', array('name' => $to)));
    }
    else
    {
      success(ln('db.renaming_table_to', array('from' => $table, 'to' => $to)));
      static::migrate('rename_table', $table, $to);
    }

    bold(ln('tetl.done'));
  }

  final public static function create($table = '') {
    info(ln('db.verifying_structure'));

    $args = array_slice(func_get_args(), 1);

    if ( ! $table) {
      error(ln('db.table_name_missing'));
    }
    elseif (in_array($table, db::tables())) {
      error(ln('db.table_already_exists', array('name' => $table)));
    }
    else
    {
      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $pk     =
        $fail   = FALSE;
        $fields = array();

        foreach ($args as $one) {
          @list($name, $type, $length) = explode(':', $one);

          if ( ! in_array($type, static::$types)) {
            error(ln('db.unknown_field', array('type' => $type, 'name' => $name)));

            $fail = TRUE;
          }
          else
          {
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

        ! $pk && $fields['id'] = array('primary_key');


        if ($fail) {
          error(ln('db.table_fields_missing', array('name' => $table)));
        }
        else
        {
          success(ln('db.table_building', array('name' => $table)));
          static::migrate('create_table', $table, $fields, array('force' => TRUE));

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

    bold(ln('tetl.done'));
  }

  final public static function add_column($table = '') {
    if (static::check_table($table)) {
      $fields = array_keys(db::columns($table));
      $args   = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one) {
          @list($name, $type, $length) = explode(':', $one);

          $col = array($type);

          $length && $col []= $length;

          if ( ! in_array($type, static::$types)) {
            error(ln('db.unknown_field', array('type' => $type, 'name' => $name)));
          }
          elseif (in_array($name, $fields)) {
            error(ln('db.column_already_exists', array('name' => $name)));
          }
          else
          {
            success(ln('db.column_building', array('type' => $type, 'name' => $name)));
            static::migrate('add_column', $table, $name, $col);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function remove_column($table = '') {
    if (static::check_table($table)) {
      $fields = array_keys(db::columns($table));
      $args   = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one) {
          if ( ! in_array($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          else
          {
            success(ln('db.column_dropping', array('name' => $one)));
            static::migrate('remove_column', $table, $one);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function rename_column($table = '') {
    if (static::check_table($table)) {
      $fields = array_keys(db::columns($table));
      $args   = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2) {
          $one  = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! in_array($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          elseif ( ! $next) {
            error(ln('db.column_name_missing'));
          }
          else
          {
            success(ln('db.column_renaming', array('from' => $one, 'to' => $next)));
            static::migrate('rename_column', $table, $one, $next);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function change_column($table = '') {
    if (static::check_table($table)) {
      $fields = db::columns($table);
      $args   = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2) {
          $one  = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! array_key_exists($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          elseif ( ! $next) {
            error(ln('db.column_type_missing'));
          }
          else
          {
            @list($type, $length) = explode(':', $next);

            $col = array($type);

            $length && $col []= $length;

            if ( ! in_array($type, static::$types)) {
              error(ln('db.unknown_field', array('type' => $type, 'name' => $one)));
            }
            elseif ($fields[$one]['type'] === $type) {
              error(ln('db.column_already_exists', array('name' => $one)));
            }
            else
            {
              success(ln('db.column_changing', array('type' => $type, 'name' => $one)));
              static::migrate('change_column', $table, $one, $col);
            }
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function add_index($table = '', $name = '') {
    if (static::check_table($table)) {
      if ( ! $name) {
        error(ln('db.index_name_missing', array('name' => $table)));
      }
      else
      {
        $unique = cli::flag('unique');
        $args   = array_slice(func_get_args(), 2);
        $idx    = db::indexes($table);

        if ( ! $args) {
          error(ln('db.index_columns_missing'));
        }
        elseif (array_key_exists($name, $idx)) {
          error(ln('db.index_already_exists', array('name' => $name, 'table' => $table)));
        }
        else
        {
          $col    = array();
          $fields = array_keys(db::columns($table));

          foreach ($args as $one) {
            if ( ! in_array($one, $fields)) {
              error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
            }
            else
            {
              notice(ln('db.success_column_index', array('name' => $one, 'table' => $table)));

              $col []= $one;
            }
          }

          if (sizeof($col) === sizeof($args)) {
            success(ln('db.indexing_table', array('name' => $name, 'table' => $table)));
            static::migrate('add_index', $table, $col, array(
              'name' => $name,
              'unique' => $unique === 'unique',
            ));
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function remove_index($table = '') {
    if (static::check_table($table)) {
      $args = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.index_name_missing', array('name' => $table)));
      }
      else
      {
        $idx = db::indexes($table);

        foreach ($args as $one) {
          if ( ! array_key_exists($one, $idx)) {
            error(ln('db.index_not_exists', array('name' => $one, 'table' => $table)));
          }
          else
          {
            success(ln('db.index_dropping', array('name' => $one)));
            static::migrate('remove_index', $table, $one);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function backup($name = '') {
    if (cli::flag('import')) {
      info(ln('db.verifying_import'));

      if ( ! $name) {
        error(ln('db.import_name_missing'));
      }
      else
      {
        $inc_file  = CWD.DS.'db'.DS.'backup'.DS.$name;
        $inc_file .= cli::flag('raw') ? '.sql' : EXT;

        $path = str_replace(CWD.DS, '', $inc_file);

        if ( ! is_file($inc_file)) {
          error(ln('db.import_file_missing', array('path' => $path)));
        }
        else
        {
          success(ln('db.importing', array('path' => $path)));

          db::import($inc_file, cli::flag('raw'));
        }
      }
    }
    else
    {
      info(ln('db.verifying_export'));

      if ( ! $name) {
        error(ln('db.export_name_missing'));
      }
      else
      {
        $name = preg_replace('/\W/', '_', $name);

        $data = cli::flag('data');
        $raw  = cli::flag('raw');
        $ext  = $raw ? '.sql' : EXT;

        $out_file = mkpath(CWD.DS.'db'.DS.'backup').DS.$name.$ext;

        if (is_file($out_file)) {
          error(ln('db.export_already_exists'));
        }
        else
        {
          $path = str_replace(CWD.DS, '', $out_file);

          touch($out_file);
          success(ln('db.exporting', array('path' => $path)));
          db::export($out_file, '*', $data, $raw);
        }
      }
    }
    bold(ln('tetl.done'));
  }

}

/* EOF: ./stack/console/mods/db/generator.php */
