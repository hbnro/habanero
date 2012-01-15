<?php

/**
 * Database scheme library
 */

class sql_scheme extends sql_query
{

  /**
   * Initiate transaction
   *
   * @return boolean
   */
  final public function begin() {
    return (boolean) $this->begin_transaction();
  }


  /**
   * Commit the current transaction
   *
   * @return boolean
   */
  final public function commit() {
    return (boolean) $this->commit_transaction();
  }


  /**
   * Cancel current transaction
   *
   * @return boolean
   */
  final public function rollback() {
    return (boolean) $this->rollback_transaction();
  }


  /**
   * Database import
   *
   * @param  string  Filepath
   * @param  boolean Treat as plain SQL?
   * @return mixed
   */
  final public function import($from, $raw = FALSE) {
    ob_start();

    $old  = include $from;
    $test = ob_get_clean();


    if ( ! is_array($old)) {
      if (is_true($raw)) {
        return array_map(array($this, 'execute'), $this->query_parse($test));
      }
      return FALSE;
    }

    foreach ((array) $old as $key => $val) {
      if ( ! empty($val['scheme'])) {
        $this->build_table($key, (array) $val['scheme']);
      }

      if ( ! empty($val['data'])) {
        foreach ((array) $val['data'] as $one) {
          $this->insert($key, $one);
        }
      }
    }
  }


  /**
   * Database export
   *
   * @param  string  Filepath
   * @param  string  Simple filter
   * @param  boolean Export data?
   * @param  boolean Export as plain SQL?
   * @return array
   */
  final public function export($to, $mask = '*', $data = FALSE, $raw = FALSE) {
    $out = array();

    foreach ($this->tables($mask) as $one) {
      foreach ($this->columns($one) as $key => $val) {
        $out[$one]['scheme'][$key] = array(
          $val['type'],
          $val['length'],
          $val['default'],
        );
      }

      if (is_true($data)) {
        $result = $this->select($one, ALL);
        $out[$one]['data'] = $this->fetch_all($result, AS_ARRAY);
      }
    }

    if (is_true($raw)) {
      $old = array();

      foreach ($out as $key => $val) {
        $old []= $this->build_table($key, $val['scheme']) . ';';

        if ( ! empty($val['data'])) {
          foreach ((array) $val['data'] as $one) {
            $keys   = $this->build_fields($key);
            $values = $this->build_values($one, TRUE);

            $old [] = sprintf("INSERT INTO\n%s\n%s;", $keys, $values);
          }
        }
      }
      $text = join("\n", $old);
    } else {
      $code = var_export($out, TRUE);
      $text = '<' . "?php return $code;";
    }

    write($to, $text);
  }


  /**
   * List all tables
   *
   * @param  string Simple filter
   * @return array
   */
  final public function tables($filter = '*') {
    $out  = array();
    $test = $this->fetch_tables();

    if ($filter === '*') {
      return $test;
    }


    foreach ($test as $one) {
      if (match($filter, $one)) {
        $out []= $one;
      }
    }

    return $out;
  }


  /**
   * List all columns from given table
   *
   * @param     string Table name
   * @staticvar array  Column conversion set
   * @return    array
   */
  final public function columns($of) {
    $test = $this->fetch_columns($of);

    foreach ($test as $key => $val) {
      $default     = ! empty($this->types[$val['type']]) ? $this->types[$val['type']] : $val['type'];
      $val['type'] = strtolower($default);
      $test[$key]  = $val;
    }

    return $test;
  }


  /**
   * List all indexes from given table
   *
   * @param  string Table name
   * @return array
   */
  final public function indexes($of) {
    return $this->fetch_indexes($of);
  }



  /**#@+
   * @ignore
   */

  final protected function a_field($type, $length = 0, $default = NULL) {
    if (empty($type)) {
      return FALSE;
    } else {
      $test = is_string($type) && ! empty($this->raw[$type]) ? $this->raw[$type] : $type;
    }


    if (is_assoc($test)) {
      $test = array_merge(compact('length', 'default'), $test);

      $type    = ! empty($test['type']) ? $test['type'] : $type;
      $length  = ! empty($test['length']) ? $test['length'] : $length;
      $default = ! empty($test['default']) ? $test['default'] : $default;
    } elseif (is_array($test)) {
      @list($type, $length, $default) = $test;

      ! $length && ! empty($this->raw[$type]['length']) && $length = $this->raw[$type]['length'];

      if ( ! empty($this->raw[$type])) {//FIX
        if (is_string($this->raw[$type])) {
          return $this->raw[$type];
        }
        $type = $this->raw[$type]['type'];
      }
    } elseif ($test !== $type) {
      return $test;
    }

    $type  = strtoupper($type);
    $type .= $length > 0 ? sprintf('(%d)', $length) : '';

    if ( ! is_null($default)) {
      $type .= ($default ? ' NOT' : '') . ' NULL';
    }

    $type .= ' DEFAULT ' . (is_null($default) ? 'NULL' : $this->fixate_string($default));

    return $type;
  }

  final protected function build_table($table, array $columns = array()) {
    $name = $this->protect_names($table);

    $sql  = "CREATE TABLE $name";
    $sql .= "\n(\n";


    foreach ($columns as $key => $value) {
      $sql  .= sprintf(" %s %s,\n", $this->protect_names($key), $this->a_field($value));
    }

    $sql  = $columns ? substr($sql, 0, - 2) : '';
    $sql .= "\n)";

    return $sql;
  }
  /**#@-*/

}

/* EOF: ./library/db/sql_scheme.php */
