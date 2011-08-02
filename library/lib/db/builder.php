<?php

/**
 * Database query related functions
 */

class db extends prototype
{
  
  /**
   * Select
   *
   * @param  mixed   Table(s)
   * @param  mixed   Column(s)
   * @param  mixed   Conditions
   * @param  mixed   Params
   * @param  boolean Return SQL?
   * @return mixed
   */
  function select($table, $fields = ALL, array $where = array(), array $options = array(), $return = FALSE)
  {
    $sql  = "SELECT\n" . sql::build_fields($fields);
    $sql .= "\nFROM\n" . sql::build_fields($table);
  
    if ( ! empty($where))
    {
      $sql .= "\nWHERE\n" . sql::build_where($where);
    }
    
    if ( ! empty($options['group']))
    {
      $sql .= "\nGROUP BY";
      
      if (is_array($options['group']))
      {
        $sql .= "\n" . join(', ', array_map(array('sql', 'names'), $options['group']));
      }
      else
      {
        $sql .= "\n" . sql::names($options['group']);
      }
    }
  
    if ( ! empty($options['order']))
    {
      $inc  = 0;
      $sql .= "\nORDER BY";
      
      foreach ($options['order'] as $one => $set)
      {
        if (($inc += 1) > 0)
        {
          $sql .= ', ';
        }
        
        if (is_num($one))
        {
          $sql .= "\n" . sql::names($set[0]) . " $set[1]";
          continue;
        }
        
        $one  = sql::names($one);
        $sql .= "\n$one $set";
      }
    }
  
    $limit  = ! empty($options['limit']) ? $options['limit'] : 0;
    $offset = ! empty($options['offset']) ? $options['offset'] : 0;
    
    if ($limit > 0)
    {
      $sql .= "\nLIMIT " . ($offset > 0 ? "$offset," : '') . $limit;
    }
    
    return is_true($return) ? $sql : db::query($sql);
  }
  
  
  /**
   * Insert
   *
   * @param  mixed   Table(s)
   * @param  mixed   Column(s)
   * @param  string  Primary key|Index
   * @param  boolean Return SQL?
   * @return mixed
   */
  function insert($table, $values, $column = NULL, $return = FALSE)
  {
    $sql  = "INSERT INTO\n" . sql::build_fields($table);
    $sql .= sql::build_values($values, TRUE);
  
    if (is_true($return))
    {
      return $sql;
    }
    
    if (is_null($column))
    {// TODO: experimental support for pgsql, try to use db_columns() instead?
      $column = array_shift(array_keys($values));
    }
  
    return db::inserted(db::query($sql), $table, $column);
  }
  
  
  /**
   * Delete
   *
   * @param  mixed   Table(s)
   * @param  mixed   Conditions
   * @param  mixed   Rows to delete
   * @param  boolean Return SQL?
   * @return mixed
   */
  function delete($table, array $where = array(), $limit = 0, $return = FALSE)
  {
    $sql = "DELETE FROM\n" . sql::build_fields($table);
  
    if ( ! empty($where))
    {
      $sql .= "\nWHERE\n" . sql::build_where($where);
    }
    $sql .= $limit > 0 ? "\nLIMIT $limit" : '';
  
    return is_true($return) ? $sql : db::affected(db::query($sql));
  }
  
  
  /**
   * Update
   *
   * @param  mixed   Table(s)
   * @param  mixed   Column(s)
   * @param  mixed   Conditions
   * @param  mixed   Rows to update
   * @param  boolean Return SQL?
   * @return mixed
   */
  function update($table, $fields, array $where = array(), $limit = 0, $return = FALSE)
  {
    $sql  = "UPDATE\n" . sql::build_fields($table);
    $sql .= "\nSET\n" . sql::build_values($fields, FALSE);
    $sql .= "\nWHERE\n" . sql::build_where($where);
    $sql .= $limit > 0 ? "\nLIMIT {$limit}" : '';
    
    return is_true($return) ? $sql : db::affected(db::query($sql));
  }
  
  
  /**
   * Prepare SQL query
   *
   * @param  string Query
   * @param  array  Params|Arguments
   * @return string
   */
  function prepare($sql, array $vars = array())
  {
    if (is_array($vars))
    {
      $sql = strtr($sql, sql::fixate_string($vars, FALSE));
    }
    elseif (func_num_args() > 1)
    {
      $args = sql::fixate_string(array_slice(func_get_args(), 1), FALSE);
      $sql  = preg_replace('/((?<!\\\)\?)/e', 'array_shift($args);', $sql);
    }  
    
    return $sql;
  }
  
  
  /**
   * Automatic escape
   *
   * @param     mixed Query
   * @param     mixed Params|Arguments
   * @staticvar mixed Function callback
   * @return    mixed
   */
  function escape($sql, $vars = array())
  {
    static $repl = NULL;
    
    
    if (is_null($repl))
    {
      $repl = function($type, $value = NULL)
      {
        switch($type)
        {
          case '%n';
            return strlen(trim($value, "\\'")) === 0 ? 'NULL' : $value;
          break;
          case '%f';
            return (float) $value;
          break;
          case '%d';
            return (int) $value;
          break;
          default;
            return $value;
          break;
        }
      };
    }
    
  
    $args = array_slice(func_get_args(), 1);
  
    if (is_array($vars) && ! empty($vars))
    {
      $sql = strtr($sql, sql::fixate_string($vars, FALSE));
    }
    elseif ( ! empty($args))
    {
      $vars = sql::fixate_string($args, FALSE);
      $sql  = preg_replace('/\b%[dsnf]\b/e', '$repl("\\0", array_shift($vars));', $sql);
    }
    
    return $sql;
  }
  
  
  /**
   * Execute raw query
   *
   * @param  string Query
   * @return mixed
   */
  function query($sql)
  {
    $args     = func_get_args();
    $callback = array('db', strpos($sql, '?') > 0 ? 'prep' : 'escape');
    $sql      = sizeof($args) > 1 ? call_user_func_array($callback, $args) : $sql;
    
    $out = sql::execute(sql::query_repare($sql));
    
    if ($message = sql::error())
    {// FIX
      raise(ln('db.database_query_error', array('message' => $message, 'sql' => htmlspecialchars($sql))));
    }
    return $out;
  }
  
  
  /**
   * Unique result
   *
   * @param  mixed SQL result|Query
   * @param  mixed Default value
   * @return mixed
   */
  function result($result, $default = FALSE)
  {
    if (is_string($result))
    {
      $res = db::query($result);
    }
    
    if (db::numrows($result) > 0) 
    {
      return sql::result($result) ?: $default;
    }
    return $default;
  }
  
  
  /**
   * Fetch all rows
   *
   * @param  mixed SQL result|Query
   * @param  mixed AS_ARRAY|AS_OBJECT
   * @return array
   */
  function fetch_all($result, $output = AS_ARRAY)
  {
    $out = array();
    
    if (is_string($result))
    {
      $args     = func_get_args();
      $callback = array('db', strpos($result, ' ') ? 'query' : 'select');
      $result   = call_user_func_array($callback, $args);
    }
    
    
    while ($row = db::fetch($result, $output))
    {
      $out []= $row;
    }
    return $out;
  }
  
  
  /**
   * Fetch single row
   *
   * @param  mixed SQL result
   * @param  mixed AS_ARRAY|AS_OBJECT
   * @return array
   */
  function fetch($result, $output = AS_ARRAY)
  {
    return $output === AS_OBJECT ? sql::fetch_object($result) : sql::fetch_assoc($result);
  }
  
  
  /**
   * Rows count
   *
   * @param  mixed SQL result
   * @return mixed
   */
  function numrows($result)
  {
    return sql::count_rows($result);
  }
  
  
  /**
   * Affected rows
   *
   * @param  mixed SQL result
   * @return mixed
   */
  function affected($result)
  {
    return sql::affected_rows($result);
  }
  
  
  /**
   * Last inserted ID
   *
   * @param  mixed SQL result
   * @param  mixed Table name
   * @param  mixed Primary key|Index
   * @return mixed
   */
  function inserted($result, $table = NULL, $column = NULL)
  {
    return sql::last_id($result, $table, $column);
  }
  
}

/* EOF: ./lib/db/builder.php */
