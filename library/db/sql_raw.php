<?php

/**
 * SQL raw building
 */

class sql_raw
{

  /**
   * Internal SQL debug
   *
   * @param  string  Query
   * @param  boolean Breakpoint
   * @return void
   */
  final public function debug($sql, $begin = FALSE) {
    static $query = '',
           $start = NULL;


    if (is_true($begin)) {
      $start = ticks();
      $this->last_query []= $sql;
      $query = str_replace("\n", ' ', preg_replace('/^ +/m', '', $sql));
    } elseif (is_false($sql)) {
      debug(sprintf("(%s) $query", ticks($start)));
    } else {
      debug($sql);
    }
  }


  /**
   * SELECT
   *
   * @param  string FROM
   * @param  mixed  Columns
   * @param  array  WHERE
   * @param  array  Params
   * @return string
   */
  final public function build_select($table, $fields = ALL, array $where = array(), array $options = array()) {
    $sql  = "SELECT\n" . $this->build_fields($fields);
    $sql .= "\nFROM\n" . $this->build_fields($table);

    if ( ! empty($where)) {
      $sql .= "\nWHERE\n" . $this->build_where($where);
    }

    if ( ! empty($options['group'])) {
      $sql .= "\nGROUP BY";

      if (is_array($options['group'])) {
        $sql .= "\n" . join(', ', array_map(array('sql', 'names'), $options['group']));
      } else {
        $sql .= "\n" . $this->protect_names($options['group']);
      }
    }

    if ( ! empty($options['order'])) {
      $inc  = 0;
      $sql .= "\nORDER BY";

      foreach ($options['order'] as $one => $set) {
        if (($inc += 1) > 1) {
          $sql .= ', ';
        }

        if (is_num($one)) {//FIX
          $sql .= $set === RANDOM ? "\n$this->random" : "\n" . $this->protect_names($set[0]) . " $set[1]";
          continue;
        }

        $one  = $this->protect_names($one);
        $sql .= "\n$one $set";
      }
    }

    $limit  = ! empty($options['limit']) ? $options['limit'] : 0;
    $offset = ! empty($options['offset']) ? $options['offset'] : 0;

    if ($limit > 0) {
      $sql .= "\nLIMIT " . ($offset > 0 ? "$offset," : '') . $limit;
    }

    return $sql;
  }


  /**
   * INSERT
   *
   * @param  string INTO
   * @param  array  VALUES
   * @return string
   */
   final public function build_insert($table, array $values) {
    $sql  = "INSERT INTO\n" . $this->build_fields($table);
    $sql .= $this->build_values($values, TRUE);

    return $sql;
  }


  /**
   * DELETE
   *
   * @param  string  FROM
   * @param  array   WHERE
   * @param  integer LIMIT
   * @return string
   */
  final public function build_delete($table, array $where = array(), $limit = 0) {
    $sql = "DELETE FROM\n" . $this->build_fields($table);

    if ( ! empty($where)) {
      $sql .= "\nWHERE\n" . $this->build_where($where);
    }
    $sql .= $limit > 0 ? "\nLIMIT $limit" : '';

    return $sql;
  }


  /**
   * UPDATE
   *
   * @param  string  Table
   * @param  array   SET
   * @param  array   WHERE
   * @param  integer LIMIT
   * @return string
   */
  final public function build_update($table, array $fields, array $where  = array(), $limit = 0) {
    $sql  = "UPDATE\n" . $this->build_fields($table);
    $sql .= "\nSET\n" . $this->build_values($fields, FALSE);
    $sql .= "\nWHERE\n" . $this->build_where($where);
    $sql .= $limit > 0 ? "\nLIMIT {$limit}" : '';

    return $sql;
  }
}

/* EOF: ./library/db/sql_raw.php */
