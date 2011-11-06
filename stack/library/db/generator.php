<?php

require __DIR__.DS.'initialize'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'db');

app_generator::usage(ln('db.generator_usage'));

app_generator::alias('db:status', 'dbs ds d');
app_generator::alias('db:show_table', 'db:show show');
app_generator::alias('db:drop_table', 'db:drop drop');
app_generator::alias('db:rename_table', 'db:rename rename');
app_generator::alias('db:create_table', 'db:create table model');



// database status
app_generator::implement('db:status', function () {
  require __DIR__.DS.'scripts'.DS.'db_status'.EXT;
});


// show table
app_generator::implement('db:show_table', function ($table = '') {
  require __DIR__.DS.'scripts'.DS.'show_table'.EXT;
});


// drop table
app_generator::implement('db:drop_table', function ($table = '') {
  if (check_table($table)) {
    success(ln('db.table_dropping', array('name' => $table)));
    build_migration('drop_table', $table);
    done();
  }
});


// rename table
app_generator::implement('db:rename_table', function ($table = '', $to = '') {
  check_table($table);

  if ( ! $to) {
    error(ln('db.table_name_missing'));
  } elseif (in_array($to, db::tables())) {
    error(ln('db.table_already_exists', array('name' => $to)));
  } else {
    success(ln('db.renaming_table_to', array('from' => $table, 'to' => $to)));
    build_migration('rename_table', $table, $to);
    done();
  }
});


// create table
app_generator::implement('db:create_table', function ($table = '') {
  $args = array_slice(func_get_args(), 1);
  require __DIR__.DS.'scripts'.DS.'create_table'.EXT;
});


// add columns
app_generator::implement('db:add_column', function ($to = '') {
  if (check_table($to)) {
    $fields = array_keys(db::columns($to));
    $args   = array_slice(func_get_args(), 1);

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
});


// remove columns
app_generator::implement('db:remove_column', function ($from = '') {
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
});


// rename column name
app_generator::implement('db:rename_column', function ($from = '') {
  if (check_table($from)) {
    $fields = array_keys(db::columns($from));
    $args   = array_slice(func_get_args(), 1);

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
});


// change column definition
app_generator::implement('db:change_column', function ($from = '') {
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
});


// create table index
app_generator::implement('db:add_index', function ($to = '', $name = '') {
  if (check_table($to)) {
    if ( ! $name) {
      error(ln('db.index_name_missing', array('name' => $to)));
    } else {
      $unique = cli::flag('unique');
      $args   = array_slice(func_get_args(), 2);
      $idx    = db::indexes($table);

      if ( ! $args) {
        error(ln('db.index_columns_missing'));
      } elseif (array_key_exists($name, $idx)) {
        error(ln('db.index_already_exists', array('name' => $name, 'table' => $to)));
      } else {
        $col    = array();
        $fields = array_keys(db::columns($to));

        foreach ($args as $one) {
          if ( ! in_array($one, $fields)) {
            error(ln('db.column_not_exists', array('name' => $one, 'table' => $to)));
          } else {
            notice(ln('db.success_column_index', array('name' => $one, 'table' => $to)));
            $col []= $one;
          }
        }

        if (sizeof($col) === sizeof($args)) {
          success(ln('db.indexing_table', array('name' => $name, 'table' => $to)));
          build_migration('add_index', $to, $col, array(
            'unique' => !! $unique,
            'name' => $name,
          ));
          done();
        }
      }
    }
  }
});


// remove table index
app_generator::implement('db:remove_index', function ($from = '', $name = '') {
  if (check_table($from)) {
    $args = array_slice(func_get_args(), 1);

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
});


// database backups
app_generator::implement('db:backup', function () {
  require __DIR__.DS.'scripts'.DS.'backups'.EXT;
});


// execute migrations
app_generator::implement('db:migrate', function () {
  require __DIR__.DS.'scripts'.DS.'load_migrations'.EXT;
});



function check_table($name) {
  info(ln('db.verifying_structure'));

  if ( ! $name) {
    error(ln('db.table_name_missing'));
  } elseif ( ! in_array($name, db::tables())) {
    error(ln('db.table_not_exists', array('name' => $name)));
  } else {
    return TRUE;
  }
}

function check_column($type) {
  static $set = array(
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

  return in_array($type, $set);
}

function build_migration($callback) {
  $args = array_slice(func_get_args(), 1);
  require __DIR__.DS.'scripts'.DS.__FUNCTION__.EXT;
  build_schema();
}

function build_schema() {
  require __DIR__.DS.'scripts'.DS.__FUNCTION__.EXT;
}

/* EOF: ./stack/library/db/generator.php */
