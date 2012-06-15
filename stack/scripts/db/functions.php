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
  $unique = isset($options['unique']) && is_true($options['unique']);
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

/* EOF: ./stack/scripts/db/functions.php */
