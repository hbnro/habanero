<?php

/**
 * Database migration functions
 */

/**
 * Create a table
 *
 * @param  string  Table
 * @param  array   Definition
 * @return boolean
 */
function create_table($name, $columns)
{
  if (in_array($name, db::tables()))
  {
    return FALSE;
  }
  
  return (boolean) sql::execute(db::build($name, $columns));
}


/**
 * Drop a table
 *
 * @param  string  Table
 * @return boolean
 */
function drop_table($name)
{
  return (boolean) sql::execute('DROP TABLE ' . sql::names($name));
}


/**
 * Rename a table
 *
 * @param  string  Old table name
 * @param  string  New table name
 * @return boolean
 */
function rename_table($from, $to)
{
  $set = db::tables();

  if ( ! in_array($from, $set) OR in_array($to, $set))
  {
    return FALSE;
  }

  return (boolean) sql::rename_table($from, $to);
}


/**
 * Create column
 *
 * @param  string  Table
 * @param  string  Column name
 * @param  mixed   Column definition
 * @return boolean
 */
function add_column($to, $name, $type)
{
  if (in_array($name, array_keys(db::columns($to))))
  {
    return FALSE;
  }

  return (boolean) sql::add_column($to, $name, $type);
}


/**
 * Remove column
 *
 * @param  string  Table
 * @param  string  Column name
 * @return boolean
 */
function remove_column($from, $name)
{
  if ( ! in_array($name, array_keys(db::columns($from))))
  {
    return FALSE;
  }

  return (boolean) sql::remove_column($from, $name);
}


/**
 * Change column
 *
 * @param  string  Table
 * @param  string  Column name
 * @param  mixed   Column definition
 * @return boolean
 */
function change_column($from, $name, $to)
{
  $set = db::columns($from);
  
  if ( ! array_key_exists($name, $set))
  {
    return FALSE;
  }
  
  return (boolean) sql::change_column($from, $name, $to);
}


/**
 * Rename column
 *
 * @param  string  Table
 * @param  string  Old column name
 * @param  mixed   New column name
 * @return boolean
 */
function rename_column($from, $name, $to)
{
  $set = db::columns($from);
  
  if ( ! array_key_exists($name, $set) OR array_key_exists($to, $set))
  {
    return FALSE;
  }
  
  return sql::rename_column($from, $name, $to);
}


/**
 * Add index
 *
 * @param  string  Table
 * @param  mixed   Column(s)
 * @param  array   Options hash
 * @return boolean
 */
function add_index($to, $column, array $options = array())
{// TODO: support for length?
  $column = (array) $column;
  $unique = isset($options['unique']) && is_true($options['unique']);
  $name   = ! empty($options['name']) ? $options['name'] : $to . '_' . join('_', $column);
  
  return (boolean) sql::add_index($to, $name, $column, $unique);
}


/**
 * Remove index
 *
 * @param  string  Table
 * @param  mixed   Column(s)|Options hash
 * @return boolean
 */
function remove_index($from, $name)
{
  if (is_array($name))
  {
    $column = ! empty($name['column']) ? (array) $name['column'] : $name;
    $name   = ! empty($name['name']) ? $name['name'] : $from . '_' . join('_', $column);
  }
  
  return (boolean) sql::remove_index($name);
}

/* EOF: ./lib/db/migration.php */