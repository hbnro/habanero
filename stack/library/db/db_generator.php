<?php

require __DIR__.DS.'initialize'.EXT;

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
    } elseif ( ! in_array($table, db::tables())) {
      error(ln('db.table_not_exists', array('name' => $table)));
    } else {
      return TRUE;
    }
  }

  final public static function migrate($callback) {
    $args = array_slice(func_get_args(), 1);
    require __DIR__.DS.'scripts'.DS.'build_migration'.EXT;
    static::schema();
  }

  final public static function missing($method, $arguments) {
    $script_file = __DIR__.DS.'scripts'.DS.$method.EXT;

    if ( ! is_file($script_file)) {
      return static::help();
    }
    require $script_file;
  }

  final public static function show($table = '') {
    require __DIR__.DS.'scripts'.DS.'show_table'.EXT;
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
    } elseif (in_array($to, db::tables())) {
      error(ln('db.table_already_exists', array('name' => $to)));
    } else {
      success(ln('db.renaming_table_to', array('from' => $table, 'to' => $to)));
      static::migrate('rename_table', $table, $to);
    }
    bold(ln('tetl.done'));
  }

  final public static function create($table = '') {
    $args = array_slice(func_get_args(), 1);
    require __DIR__.DS.'scripts'.DS.'create_table'.EXT;
  }

  final public static function add_column($table = '') {
    if (static::check_table($table)) {
      $fields = array_keys(db::columns($table));
      $args   = array_slice(func_get_args(), 1);

      if ( ! $args) {
        error(ln('db.table_fields_missing', array('name' => $table)));
      } else {
        foreach ($args as $one) {
          @list($name, $type, $length) = explode(':', $one);

          $col = array($type);

          $length && $col []= $length;

          if ( ! in_array($type, static::$types)) {
            error(ln('db.unknown_field', array('type' => $type, 'name' => $name)));
          } elseif (in_array($name, $fields)) {
            error(ln('db.column_already_exists', array('name' => $name)));
          } else {
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
      } else {
        foreach ($args as $one) {
          if ( ! in_array($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          } else {
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
      } else {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2) {
          $one  = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! in_array($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          } elseif ( ! $next) {
            error(ln('db.column_name_missing'));
          } else {
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
      } else {
        $c = sizeof($args);

        for ($i = 0; $i < $c; $i += 2) {
          $one  = $args[$i];
          $next = isset($args[$i + 1]) ? $args[$i + 1] : NULL;

          if ( ! array_key_exists($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
          } elseif ( ! $next) {
            error(ln('db.column_type_missing'));
          } else {
            @list($type, $length) = explode(':', $next);

            $col = array($type);

            $length && $col []= $length;

            if ( ! in_array($type, static::$types)) {
              error(ln('db.unknown_field', array('type' => $type, 'name' => $one)));
            } elseif ($fields[$one]['type'] === $type) {
              error(ln('db.column_already_exists', array('name' => $one)));
            } else {
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
      } else {
        $unique = cli::flag('unique');
        $args   = array_slice(func_get_args(), 2);
        $idx    = db::indexes($table);

        if ( ! $args) {
          error(ln('db.index_columns_missing'));
        } elseif (array_key_exists($name, $idx)) {
          error(ln('db.index_already_exists', array('name' => $name, 'table' => $table)));
        } else {
          $col    = array();
          $fields = array_keys(db::columns($table));

          foreach ($args as $one) {
            if ( ! in_array($one, $fields)) {
              error(ln('db.column_not_exists', array('name' => $one, 'table' => $table)));
            } else {
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
      } else {
        $idx = db::indexes($table);

        foreach ($args as $one) {
          if ( ! array_key_exists($one, $idx)) {
            error(ln('db.index_not_exists', array('name' => $one, 'table' => $table)));
          } else {
            success(ln('db.index_dropping', array('name' => $one)));
            static::migrate('remove_index', $table, $one);
          }
        }
      }
    }

    bold(ln('tetl.done'));
  }

}

/* EOF: ./stack/console/mods/db/generator.php */
