<?php

/**#@+
 * @ignore
 */
import('db');

require __DIR__.DS.'functions'.EXT;

db::implement('missing', function ($method, array $arguments) {
  return call_user_func_array(array(db()->conn, $method), $arguments);
});

i18n::load_path(__DIR__.DS.'locale', 'db');

app_generator::usage('db', ln('db.usage'));
app_generator::alias('db:status', 'db');
app_generator::alias('db:migrate', 'migrate');
app_generator::alias('db:show_table', 'db:show show');
app_generator::alias('db:drop_table', 'db:drop drop');
app_generator::alias('db:create_table', 'db:create table');
app_generator::alias('db:rename_table', 'db:rename rename');
app_generator::alias('db:add_column', 'add_column');
app_generator::alias('db:remove_column', 'remove_column');
app_generator::alias('db:rename_column', 'rename_column');
app_generator::alias('db:change_column', 'change_column');
app_generator::alias('db:add_index', 'add_index');
app_generator::alias('db:remove_index', 'remove_index');
app_generator::alias('db:freeze', 'freeze lock');


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
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'add_column'.EXT;
});


// remove columns
app_generator::implement('db:remove_column', function ($from = '') {
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'remove_column'.EXT;
});


// rename column name
app_generator::implement('db:rename_column', function ($from = '') {
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'rename_column'.EXT;
});


// change column definition
app_generator::implement('db:change_column', function ($from = '') {
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'change_column'.EXT;
});


// create table index
app_generator::implement('db:add_index', function ($to = '', $name = '') {
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'add_index'.EXT;
});


// remove table index
app_generator::implement('db:remove_index', function ($from = '', $name = '') {
  $args = func_get_args();
  require __DIR__.DS.'scripts'.DS.'remove_index'.EXT;
});


// execute migrations
app_generator::implement('db:migrate', function () {
  require __DIR__.DS.'scripts'.DS.'load_migrations'.EXT;
});


// freeze columns
app_generator::implement('db:freeze', function () {
  info(ln('db.locking_tables'));

  $set  = array();
  $test = db::tables();

  if (($key = array_search('migration_history', $test)) !== FALSE) {
    unset($test[$key]);
  }


  foreach ($test as $one) {
    $set[$one] = db::columns($one);
    success(ln('db.freeze_columns', array('table' => $one)));
  }

  $tables_file = mkpath(APP_PATH.DS.'config').DS.'tables'.EXT;
  write($tables_file, sprintf("<?php return %s;\n", var_export($set, TRUE)));

  done();
});



// pre-create migration table
if ( ! in_array('migration_history', db::tables())) {
  create_table('migration_history', array(
    'name' => array('type' => 'string'),
  ));
}

function all_migrations() {
  static $cache = NULL;


  if (is_null($cache)) {
    $cache = array();
    $test  = db::select('migration_history');

    while ($row = db::fetch($test, AS_OBJECT)) {
      $cache []= $row->name;
    }
  }

  return $cache;
}

function add_migration($name) {
  db::insert('migration_history', compact('name'));
}

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

function db() {
  static $res = NULL;

  if (is_null($res)) {
    $name = cli::flag('database') ?: 'default';
    $dsn  = option("database.$name");
    $res  = new stdClass;

    $res->conn = db::connect($dsn);
    $res->name = $name;
    $res->dsn = $dsn;
  }
  return $res;
}

/**#@-*/

/* EOF: ./stack/scripts/db/initialize.php */
