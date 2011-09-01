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

  function help()
  {
    $db_introduction = ln('tetl.db_generator_intro');
    $db_title = ln('tetl.db_generator');
    $str = <<<HELP

  $db_introduction

  $db_title:

  \bgreen(db:status)\b
  \bgreen(db:show)\b \bblue(table)\b
  \bgreen(db:drop)\b \bblue(table)\b
  \bgreen(db:rename)\b \bblue(table)\b \bwhite(new)\b
  \bgreen(db:create)\b \bblue(table)\b \byellow(field:type[:length])\b [...]
  \bgreen(db:add_column)\b \bblue(table)\b \byellow(field:type[:length])\b [...]
  \bgreen(db:remove_column)\b \bblue(table)\b \byellow(name)\b [...]
  \bgreen(db:rename_column)\b \bblue(table)\b \byellow(name)\b \bwhite(new)\b [...]
  \bgreen(db:change_column)\b \bblue(table)\b \byellow(name)\b \bwhite(type[:length])\b [...]
  \bgreen(db:add_index)\b \bblue(table)\b \byellow(name)\b \bwhite(column)\b [...] [--unique]
  \bgreen(db:remove_index)\b \bblue(table)\b \byellow(name)\b
  \bgreen(db:export)\b \bblue(table)\b \bwhite(file)\b [--raw] [--data]
  \bgreen(db:import)\b \bblue(table)\b \bwhite(file)\b [--raw]

HELP;

    cli::write(cli::format("$str\n"));
  }

  private function init()
  {
    config(CWD.DS.'config'.DS.'database'.EXT);
    import('tetl/db');
  }

  private function check_table($table)
  {
    database::init();

    blue(ln('tetl.verifying_tables'));


    if ( ! $table)
    {
      red(ln('tetl.table_name_missing'));
    }
    elseif ( ! in_array($table, db::tables()))
    {
      red(ln('tetl.table_not_exists', array('name' => $table)));
    }
    else
    {
      return TRUE;
    }
  }

  function status()
  {
    database::init();

    blue(ln('tetl.verifying_database'));


    green(DB_DSN);

    $test = db::tables();

    if (empty($test))
    {
      red(ln('tetl.without_tables'));
    }
    else
    {
      foreach ($test as $one)
      {
        yellow($one);
      }
    }

    white(ln('tetl.done'));
  }

  function show($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      green(ln('tetl.table_show_columns', array('name' => $table)));

      $set =
      $heads = array();

      foreach (db::columns($table) as $name => $one)
      {
        if (empty($heads))
        {
          $heads = array_keys($one);
        }

        $set[$name] = array($name);
        $set[$name] += $one;
      }

      array_unshift($heads, 'name');
      cli::table($set, $heads);


      yellow(ln('tetl.table_show_indexes', array('name' => $table)));

      $idx = array();
      $all = db::indexes($table);

      if ( ! $all)
      {
        red(ln('tetl.table_without_indexes', array('name' => $table)));
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

    white(ln('tetl.done'));
  }

  function drop($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      green(ln('tetl.table_dropping', array('name' => $table)));
      drop_table($table);
    }

    white(ln('tetl.done'));
  }

  function rename($args = array())
  {
    @list($table, $to) = $args;

    database::check_table($table);

    if ( ! $to)
    {
      red(ln('tetl.table_name_missing'));
    }
    elseif (in_array($to, db::tables()))
    {
      red(ln('tetl.table_already_exists', array('name' => $to)));
    }
    else
    {
      green(ln('tetl.renaming_table_to', array('from' => $table, 'to' => $to)));
      rename_table($table, $to);
    }

    white(ln('tetl.done'));
  }

  function create($args = array())
  {
    database::init();

    blue(ln('tetl.verifying_structure'));

    @list($table) = $args;

    $args = array_slice($args, 1);

    if ( ! $table)
    {
      red(ln('tetl.table_name_missing'));
    }
    elseif (in_array($table, db::tables()))
    {
      red(ln('tetl.table_already_exists', array('name' => $table)));
    }
    else
    {
      if ( ! $args)
      {
        red(ln('tetl.table_missing_fields', array('name' => $table)));
      }
      else
      {
        $pk = FALSE;
        $fail = FALSE;
        $fields = array();

        foreach ($args as $one)
        {
          @list($name, $type, $length) = explode(':', $one);

          if ( ! in_array($type, database::$types))
          {
            red(ln('tetl.unknown_field_type', array('type' => $type, 'name' => $name)));

            $fail = TRUE;
          }
          else
          {
            yellow(ln('tetl.success_field_type', array('type' => $type, 'name' => $name)));

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
          red(ln('tetl.table_pk_missing', array('name' => $table)));
        }
        elseif ($fail)
        {
          red(ln('tetl.table_def_incomplete', array('name' => $table)));
        }
        else
        {
          green(ln('tetl.table_def_building', array('name' => $table)));
          db::query(db::build($table, $fields));
        }
      }
    }

    white(ln('tetl.done'));
  }

  function add_column($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        red(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one)
        {
          @list($name, $type, $length) = explode(':', $one);

          $col = array($type);

          $length && $col []= $length;

          if ( ! in_array($type, database::$types))
          {
            red(ln('tetl.unknown_field_type', array('type' => $type, 'name' => $name)));
          }
          elseif (in_array($name, $fields))
          {
            red(ln('tetl.column_already_exists', array('name' => $name)));
          }
          else
          {
            green(ln('tetl.column_building', array('type' => $type, 'name' => $name)));
            add_column($table, $name, $col);
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function remove_column($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        red(ln('tetl.table_fields_missing', array('name' => $table)));
      }
      else
      {
        foreach ($args as $one)
        {
          if ( ! in_array($one, $fields))
          {
            red(ln('tetl.column_not_exists', array('name' => $one)));
          }
          else
          {
            green(ln('tetl.column_dropping', array('name' => $one)));
            remove_column($table, $one);
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function rename_column($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      $fields = array_keys(db::columns($table));
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        red(ln('tetl.table_fields_missing', array('name' => $table)));
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
            red(ln('tetl.column_not_exists', array('name' => $one)));
          }
          elseif ( ! $next)
          {
            red(ln('tetl.column_name_missing'));
          }
          else
          {
            green(ln('tetl.column_renaming', array('from' => $one, 'to' => $next)));
            rename_column($table, $one, $next);
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function change_column($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      $fields = db::columns($table);
      $args = array_slice($args, 1);

      if ( ! $args)
      {
        red(ln('tetl.table_fields_missing', array('name' => $table)));
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
            red(ln('tetl.column_not_exists', array('name' => $one)));
          }
          elseif ( ! $next)
          {
            red(ln('tetl.column_type_missing'));
          }
          else
          {
            @list($type, $length) = explode(':', $next);

            $col = array($type);

            $length && $col []= $length;

            if ( ! in_array($type, database::$types))
            {
              red(ln('tetl.unknown_field_type', array('type' => $type, 'name' => $one)));
            }
            elseif ($fields[$one]['type'] === $type)
            {
              red(ln('tetl.column_already_exists', array('type' => $type, 'name' => $one)));
            }
            else
            {
              green(ln('tetl.column_changing', array('type' => $type, 'name' => $one)));
              change_column($table, $one, $col);
            }
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function add_index($args = array(), $params = array())
  {
    @list($table, $name) = $args;

    if (database::check_table($table))
    {
      if ( ! $name)
      {
        red(ln('tetl.index_name_missing', array('name' => $table)));
      }
      else
      {
        $unique = isset($params['unique']);
        $args = array_slice($args, 2);
        $idx = db::indexes($table);

        if ( ! $args)
        {
          red(ln('tetl.index_columns_missing', array('name' => $table)));
        }
        elseif (array_key_exists($name, $idx))
        {
          red(ln('tetl.index_already_exists', array('name' => $name)));
        }
        else
        {
          $col = array();
          $fields = array_keys(db::columns($table));

          foreach ($args as $one)
          {
            if ( ! in_array($one, $fields))
            {
              red(ln('tetl.column_not_exists', array('name' => $one)));
            }
            else
            {
              yellow(ln('tetl.success_column_index', array('name' => $one)));

              $col []= $one;
            }
          }

          if (sizeof($col) === sizeof($args))
          {
            green(ln('tetl.table_column_indexing', array('name' => $table)));
            add_index($table, $col, array(
              'name' => $name,
              'unique' => $unique === 'unique',
            ));
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function remove_index($args = array())
  {
    @list($table) = $args;

    if (database::check_table($table))
    {
      $args = array_slice(func_get_args(), 1);

      if ( ! $args)
      {
        red(ln('tetl.index_names_missing', array('name' => $table)));
      }
      else
      {
        $idx = db::indexes($table);

        foreach ($args as $one)
        {
          if ( ! array_key_exists($one, $idx))
          {
            red(ln('tetl.index_not_exists', array('name' => $one)));
          }
          else
          {
            green(ln('tetl.index_dropping', array('name' => $one)));
            remove_index($table, $one);
          }
        }
      }
    }

    white(ln('tetl.done'));
  }

  function export($args = array(), $params = array())
  {
    @list($table, $name) = $args;

    if (database::check_table($table))
    {
      green(ln('tetl.table_exporting', array('name' => $table)));

      $name = preg_replace('/\W/', '_', $name) ?: $table;
      $data = isset($params['data']);
      $raw = isset($params['raw']);
      $ext = $raw ? '.sql' : EXT;

      $out_file = CWD.DS.'db'.DS.$name.$ext;
      db::export($out_file, $table, $data, $raw);
    }

    white(ln('tetl.done'));
  }

  function import($args = array(), $params = array())
  {
    @list($name) = $args;

    blue(ln('tetl.verifying_import'));

    if ( ! $name)
    {
      red(ln('tetl.import_name_missing'));
    }
    else
    {
      $raw = isset($params['raw']);
      $ext = $raw ? '.sql' : EXT;

      $inc_file = CWD.DS.'db'.DS.$name.$ext;

      if ( ! is_file($inc_file))
      {
        red(ln('tetl.import_file_missing', array('name' => $name)));
      }
      else
      {
        yellow(ln('tetl.structure_importing', array('name' => $name)));

        database::init();
        db::import($inc_file, $raw);
      }
    }

    white(ln('tetl.done'));
  }
}
