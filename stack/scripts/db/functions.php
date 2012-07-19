<?php

/**
 * Database migration functions
 */

/**
 * Create a table
 *
 * @param  string  Table
 * @param  array   Definition
 * @param  array   Options hash
 * @return boolean
 */
function create_table($name, array $columns, array $options = array()) {
  ! empty($options['force']) && @drop_table($name);
  return (boolean) db::create($name, $columns);
}


/**
 * Drop a table
 *
 * @param  string  Table
 * @return boolean
 */
function drop_table($name) {
  return (boolean) db::drop($name);
}


/**
 * Rename a table
 *
 * @param  string  Old table name
 * @param  string  New table name
 * @return boolean
 */
function rename_table($from, $to) {
  return (boolean) db::rename_table($from, $to);
}


/**
 * Create column
 *
 * @param  string  Table
 * @param  string  Column name
 * @param  mixed   Column definition
 * @return boolean
 */
function add_column($to, $name, $type) {
  return (boolean) db::add_column($to, $name, $type);
}


/**
 * Remove column
 *
 * @param  string  Table
 * @param  string  Column name
 * @return boolean
 */
function remove_column($from, $name) {
  return (boolean) db::remove_column($from, $name);
}


/**
 * Change column
 *
 * @param  string  Table
 * @param  string  Column name
 * @param  mixed   Column definition
 * @return boolean
 */
function change_column($from, $name, $to) {
  return (boolean) db::change_column($from, $name, $to);
}


/**
 * Rename column
 *
 * @param  string  Table
 * @param  string  Old column name
 * @param  mixed   New column name
 * @return boolean
 */
function rename_column($from, $name, $to) {
  return db::rename_column($from, $name, $to);
}


/**
 * Add index
 *
 * @param  string  Table
 * @param  mixed   Column(s)
 * @param  array   Options hash
 * @return boolean
 */
function add_index($to, $column, array $options = array()) {// TODO: support for length?
  $column = (array) $column;
  $unique = ! empty($options['unique']);
  $name   = ! empty($options['name']) ? $options['name'] : $to . '_' . join('_', $column);

  return (boolean) db::add_index($to, $name, $column, $unique);
}


/**
 * Remove index
 *
 * @param  string  Table
 * @param  mixed   Column(s)|Options hash
 * @return boolean
 */
function remove_index($from, $name) {
  if (is_array($name)) {
    $column = ! empty($name['column']) ? (array) $name['column'] : $name;
    $name   = ! empty($name['name']) ? $name['name'] : $from . '_' . join('_', $column);
  }

  return (boolean) db::remove_index($name);
}



/**#@+
 * @ignore
 */

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

function has_schema() {
  return in_array('migration_history', db::tables());
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

/* EOF: ./stack/scripts/db/functions.php */
