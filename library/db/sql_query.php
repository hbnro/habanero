<?php

/**
 * Database query related functions
 */

class sql_query extends sql_base
{
  /**
   * Select
   *
   * @param  mixed Table(s)
   * @param  mixed Column(s)
   * @param  mixed Conditions
   * @param  mixed Params
   * @return mixed
   */
  final public function select($table, $fields = ALL, array $where = array(), array $options = array()) {
    return $this->query($this->build_select($table, $fields, $where, $options));
  }


  /**
   * Insert
   *
   * @param  mixed  Table(s)
   * @param  mixed  Column(s)
   * @param  string Primary key|Index
   * @return mixed
   */
  final public function insert($table, $values, $column = NULL) {
    return $this->inserted($this->query($this->build_insert($table, $values)), $table, $column);
  }


  /**
   * Delete
   *
   * @param  mixed Table(s)
   * @param  mixed Conditions
   * @param  mixed Rows to delete
   * @return mixed
   */
  final public function delete($table, array $where = array(), $limit = 0) {
    return $this->affected($this->query($this->build_delete($table, $where, $limit)));
  }


  /**
   * Update
   *
   * @param  mixed Table(s)
   * @param  mixed Column(s)
   * @param  mixed Conditions
   * @param  mixed Rows to update
   * @return mixed
   */
  final public function update($table, $fields, array $where = array(), $limit = 0) {
    return $this->affected($this->query($this->build_update($table, $fields, $where, $limit)));
  }


  /**
   * Prepare SQL query
   *
   * @param  string Query
   * @param  array  Params
   * @return string
   */
  final public function prepare($sql, array $vars = array()) {
    if (is_assoc($vars)) {
      $sql = strtr($sql, $this->fixate_string($vars, FALSE));
    } else {
      $args = $this->fixate_string($vars, FALSE);
      $sql  = preg_replace('/((?<!\\\)\?)/e', 'array_shift($args);', $sql);
    }

    return $sql;
  }


  /**
   * Execute raw query
   *
   * @param  string Query
   * @param  array  Params
   * @return mixed
   */
  final public function query($sql, $repl = array()) {
    if (func_num_args() > 1) {
      $args = func_num_args() > 2 ? func_get_args() : (array) $repl;
      $sql  = $this->prepare($sql, $args);
    }

    $out = $this->execute($this->query_repare($sql));

    if ($message = $this->has_error()) {// FIX
      raise(ln('db.database_query_error', array('message' => $message, 'sql' => end($this->last_query))));
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
  final public function result($test, $default = FALSE) {
    if (is_string($test)) {
      $test = $this->query($test);
    }
    return $this->fetch_result($test) ?: $default;
  }


  /**
   * Fetch all rows
   *
   * @param  mixed SQL result|Query
   * @param  mixed AS_ARRAY|AS_OBJECT
   * @return array
   */
  final public function fetch_all($result, $output = AS_ARRAY) {
    $out = array();

    if (is_string($result)) {
      $args     = func_get_args();
      $callback = strpos($result, ' ') ? 'query' : 'select';
      $result   = call_user_func_array(array($this, $callback), $args);
    }

    while ($row = $this->fetch($result, $output)) {
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
  final public function fetch($result, $output = AS_ARRAY) {
    return $output === AS_OBJECT ? $this->fetch_object($result) : $this->fetch_assoc($result);
  }


  /**
   * Rows count
   *
   * @param  mixed SQL result
   * @return mixed
   */
  final public function numrows($result) {
    return $this->count_rows($result);
  }


  /**
   * Affected rows
   *
   * @param  mixed SQL result
   * @return mixed
   */
  final public function affected($result) {
    return $this->affected_rows($result);
  }


  /**
   * Last inserted ID
   *
   * @param  mixed SQL result
   * @param  mixed Table name
   * @param  mixed Primary key|Index
   * @return mixed
   */
  final public function inserted($result, $table = NULL, $column = NULL) {
    return $this->last_inserted_id($result, $table, $column);
  }
}

/* EOF: ./library/db/sql_query.php */
