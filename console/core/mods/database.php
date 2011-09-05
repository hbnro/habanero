<?php

class database extends prototype
{
  static $types = array(
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

  final public static function help()
  {
    $db_introduction = ln('tetl.database_generator');
    $str = <<<HELP

  $db_introduction

  \bgreen(db.st)\b
  \bgreen(db.make)\b
  \bgreen(db.show)\b \bblue(table)\b
  \bgreen(db.drop)\b \bblue(table)\b
  \bgreen(db.rename)\b \bblue(table)\b \bwhite(new)\b
  \bgreen(db.create)\b \bblue(table)\b \byellow(field:type[:length])\b [...]
  \bgreen(db.add_column)\b \bblue(table)\b \byellow(field:type[:length])\b [...]
  \bgreen(db.remove_column)\b \bblue(table)\b \byellow(name)\b [...]
  \bgreen(db.rename_column)\b \bblue(table)\b \byellow(name)\b \bwhite(new)\b [...]
  \bgreen(db.change_column)\b \bblue(table)\b \byellow(name)\b \bwhite(type[:length])\b [...]
  \bgreen(db.add_index)\b \bblue(table)\b \byellow(name)\b \bwhite(column)\b [...] [--unique]
  \bgreen(db.remove_index)\b \bblue(table)\b \byellow(name)\b
  \bgreen(db.export)\b \bblue(table)\b \bwhite(file)\b [--raw] [--data]
  \bgreen(db.import)\b \bblue(table)\b \bwhite(file)\b [--raw]

HELP;

    cli::write(cli::format("$str\n"));
  }

  final private static function init()
  {
    config(CWD.DS.'config'.DS.'database'.EXT);
    import('tetl/db');
  }

  final private static function check_table($table)
  {
    self::init();

    info(ln('tetl.verifying_structure'));


    if ( ! $table)
    {
      error(ln('tetl.table_name_missing'));
    }
    elseif ( ! in_array($table, db::tables()))
    {
      error(ln('tetl.table_not_exists', array('name' => $table)));
    }
    else
    {
      return TRUE;
    }
  }

  final private static function migrate($name)
  {
    $args = array_slice(func_get_args(), 1);
    $time = time();

    $migration_name = date('YmdHis_', $time).$args[0].'_'.$name;
    $migration_path = mkpath(CWD.DS.'db'.DS.'migrate');
    $migration_file = $migration_path.DS.$migration_name.EXT;


    foreach ($args as $i => $one)
    {
      if (is_array($one))
      {
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


    $callback = sprintf("$name(%s);\n", join(', ', $args));

    if ( ! is_file($migration_file))
    {
      $date = date('Y-m-d H:i:s', $time);

      write($migration_file, "<?php\n/* $date */\n$callback");
    }
    else
    {
      write($migration_file, $callback, 1);
    }

    @eval($callback);
  }

  function st()
  {
    self::init();

    info(ln('tetl.verifying_database'));


    success(DB_DSN);

    $test = db::tables();

    if (empty($test))
    {
      error(ln('tetl.without_tables'));
    }
    else
    {
      foreach ($test as $one)
      {
        $count = (int) db::result(db::select($one, 'COUNT(*)'));
        cli::writeln("- \byellow($one)\b ($count)");
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function make($args = array(), $params = array())
  {
    self::init();

    info(ln('tetl.verifying_database'));

    foreach (db::tables() as $one)
    {
      notice(ln('tetl.table_dropping', array('name' => $one)));
      drop_table($one);
    }

    info(ln('tetl.migrating_database'));

    if ($test = findfile(CWD.DS.'db'.DS.'migrate', '*'.EXT))
    {
      sort($test);

      foreach ($test as $migration_file)
      {
        $path = str_replace(CWD.DS, '', $migration_file);
        success(ln('tetl.run_migration', array('path' => $path)));
        require $migration_file;
      }
    }
    else
    {
      error(ln('tetl.without_migrations'));
    }

    info(ln('tetl.verifying_seed'));

    $seed_file = CWD.DS.'db'.DS.'seed'.EXT;

    if ( ! is_file($seed_file))
    {
      error(ln('tetl.without_seed'));
    }
    else
    {
      $path = str_replace(CWD.DS, '', $seed_file);
      success(ln('tetl.loading_seed', array('path' => $path)));
      require $seed_file;
    }

    bold(ln('tetl.done'));
  }

  final public static function show($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      success(ln('tetl.table_show_columns', array('name' => $table)));

      $set =
      $heads = array();

      foreach (db::columns($table) as $name => $one)
      {
        if (empty($heads))
        {
          $heads = array_keys($one);
        }

        $set[$name]  = array($name);
        $set[$name] += $one;
      }

      array_unshift($heads, 'name');
      cli::table($set, $heads);


      notice(ln('tetl.table_show_indexes', array('name' => $table)));

      $idx = array();
      $all = db::indexes($table);

      if ( ! $all)
      {
        error(ln('tetl.without_indexes', array('name' => $table)));
      }
      else
      {
        $idx = array();

        foreach ($all as $name => $one)
        {
          $idx []= array($name, join(', ', $one['column']), $one['unique']);
        }
        cli::table($idx, array('name', 'columns', 'unique'));
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function drop($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      success(ln('tetl.table_dropping', array('name' => $table)));
      self::migrate('drop_table', $table);
    }

    bold(ln('tetl.done'));
  }

  final public static function rename($args = array())
  {
    @list($table, $to) = $args;

    self::check_table($table);

    if ( ! $to)
    {
      error(ln('tetl.table_name_missing'));
    }
    elseif (in_array($to, db::tables()))
    {
      error(ln('tetl.table_already_exists', array('name' => $to)));
    }
    else
    {
      success(ln('tetl.renaming_table_to', array('from' => $table, 'to' => $to)));
      self::migrate('rename_table', $table, $to);
    }

    bold(ln('tetl.done'));
  }

  final public static function create($args = array())
  {
    self::init();

    info(ln('tetl.verifying_structure'));

    @list($table) = $args;

    $args = array_slice($args, 1);

    if ( ! $table)
    {
      error(ln('tetl.table_name_missing'));
    }
    elseif (in_array($table, db::tables()))
    {
      error(ln('tetl.table_already_exists', array('name' => $table)));
    }
    else
    {
      if ( ! $args)
      {
        error(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $pk = FALSE;
        $fail = FALSE;
        $fields = array();

        foreach ($args as $one)
        {
          @list($name, $type, $length) = explode(':', $one);

          if ( ! in_array($type, self::$types))
          {
            error(ln('tetl.unknown_field', array('type' => $type, 'name' => $name)));

            $fail = TRUE;
          }
          else
          {
            notice(ln('tetl.success_field_type', array('type' => $type, 'name' => $name)));

            $fields[$name] = array();
            $fields[$name] []= $type;

            if ($length && is_num($length))
            {
              $fields[$name] []= $length;
            }

            if ($type === 'primary_key')
            {
              $pk = TRUE;
            }
          }
        }

        if ( ! $pk)
        {
          error(ln('tetl.table_pk_missing', array('name' => $table)));
        }
        elseif ($fail)
        {
          error(ln('tetl.table_fields_missing', array('name' => $table)));
        }
        else
        {
          success(ln('tetl.table_building', array('name' => $table)));
          self::migrate('create_table', $table, $fields);
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function add_column($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        error(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one)
        {
          @list($name, $type, $length) = explode(':', $one);

          $col = array($type);

          $length && $col []= $length;

          if ( ! in_array($type, self::$types))
          {
            error(ln('tetl.unknown_field', array('type' => $type, 'name' => $name)));
          }
          elseif (in_array($name, $fields))
          {
            error(ln('tetl.column_already_exists', array('name' => $name)));
          }
          else
          {
            success(ln('tetl.column_building', array('type' => $type, 'name' => $name)));
            self::migrate('add_column', $table, $name, $col);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function remove_column($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        error(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one)
        {
          if ( ! in_array($one, $fields))
          {
            error(ln('tetl.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          else
          {
            success(ln('tetl.column_dropping', array('name' => $one)));
            self::migrate('remove_column', $table, $one);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function rename_column($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        error(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2)
        {
          $one = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! in_array($one, $fields))
          {
            error(ln('tetl.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          elseif ( ! $next)
          {
            error(ln('tetl.column_name_missing'));
          }
          else
          {
            success(ln('tetl.column_renaming', array('from' => $one, 'to' => $next)));
            self::migrate('rename_column', $table, $one, $next);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function change_column($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      $fields = db::columns($table);
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        error(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2)
        {
          $one = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! array_key_exists($one, $fields))
          {
            error(ln('tetl.column_not_exists', array('name' => $one, 'table' => $table)));
          }
          elseif ( ! $next)
          {
            error(ln('tetl.column_type_missing'));
          }
          else
          {
            @list($type, $length) = explode(':', $next);

            $col = array($type);

            $length && $col []= $length;

            if ( ! in_array($type, self::$types))
            {
              error(ln('tetl.unknown_field', array('type' => $type, 'name' => $one)));
            }
            elseif ($fields[$one]['type'] === $type)
            {
              error(ln('tetl.column_already_exists', array('name' => $one)));
            }
            else
            {
              success(ln('tetl.column_changing', array('type' => $type, 'name' => $one)));
              self::migrate('change_column', $table, $one, $col);
            }
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function add_index($args = array(), $params = array())
  {
    @list($table, $name) = $args;

    if (self::check_table($table))
    {
      if ( ! $name)
      {
        error(ln('tetl.index_name_missing', array('name' => $table)));
      }
      else
      {
        $unique = isset($params['unique']);
        $args = array_slice($args, 2);
        $idx = db::indexes($table);

        if ( ! $args)
        {
          error(ln('tetl.index_columns_missing'));
        }
        elseif (array_key_exists($name, $idx))
        {
          error(ln('tetl.index_already_exists', array('name' => $name, 'table' => $table)));
        }
        else
        {
          $col = array();
          $fields = array_keys(db::columns($table));

          foreach ($args as $one)
          {
            if ( ! in_array($one, $fields))
            {
              error(ln('tetl.column_not_exists', array('name' => $one, 'table' => $table)));
            }
            else
            {
              notice(ln('tetl.success_column_index', array('name' => $one, 'table' => $table)));

              $col []= $one;
            }
          }

          if (sizeof($col) === sizeof($args))
          {
            success(ln('tetl.indexing_table', array('name' => $name, 'table' => $table)));
            self::migrate('add_index', $table, $col, array(
              'name' => $name,
              'unique' => $unique === 'unique',
            ));
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function remove_index($args = array())
  {
    @list($table) = $args;

    if (self::check_table($table))
    {
      $args = array_slice(func_get_args(), 1);

      if ( ! $args)
      {
        error(ln('tetl.index_name_missing', array('name' => $table)));
      }
      else
      {
        $idx = db::indexes($table);

        foreach ($args as $one)
        {
          if ( ! array_key_exists($one, $idx))
          {
            error(ln('tetl.index_not_exists', array('name' => $one, 'table' => $table)));
          }
          else
          {
            success(ln('tetl.index_dropping', array('name' => $one)));
            self::migrate('remove_index', $table, $one);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function export($args = array(), $params = array())
  {
    info(ln('tetl.verifying_export'));

    @list($name) = $args;

    if ( ! $name)
    {
      error(ln('tetl.export_name_missing'));
    }
    else
    {
      self::init();

      $name = preg_replace('/\W/', '_', $name);

      $data = isset($params['data']);
      $raw = isset($params['raw']);
      $ext = $raw ? '.sql' : EXT;

      $out_file = mkpath(CWD.DS.'db'.DS.'backup').DS.date('YmdHis_').$name.$ext;
      $path = str_replace(CWD.DS, '', $out_file);

      success(ln('tetl.exporting', array('path' => $path)));
      db::export($out_file, '*', $data, $raw);
    }

    bold(ln('tetl.done'));
  }

  final public static function import($args = array(), $params = array())
  {
    @list($name) = $args;

    info(ln('tetl.verifying_import'));

    if ( ! $name)
    {
      error(ln('tetl.import_name_missing'));
    }
    else
    {
      $raw = isset($params['raw']);
      $inc_file = CWD.DS.$name;

      if ( ! is_file($inc_file))
      {
        error(ln('tetl.import_file_missing', array('path' => $inc_file)));
      }
      else
      {
        $path = str_replace(CWD.DS, '', $inc_file);
        success(ln('tetl.importing', array('path' => $path)));

        self::init();
        db::import($inc_file, $raw);
      }
    }

    bold(ln('tetl.done'));
  }
}
