<?php

/**
 * DB model
 */

class db_model extends a_record
{

  // connection
  public static $database = 'default';


  /**
   * Save row
   *
   * @param  boolean Skip validation?
   * @return model
   */
  final public function save($skip = FALSE) {
    static::callback($this, 'before_save');

    if ( ! $skip && ! $this->is_valid()) {
      return FALSE;
    }


    $fields = static::stamp($this->props, $this->is_new());

    unset($fields[static::pk()]);

    if ($this->is_new()) {
      $this->props[static::pk()] = static::conn()->insert(static::table(), $fields, static::pk());
      $this->new_record = FALSE;
    } else {
      static::conn()->update(static::table(), $fields, array(
        static::pk() => $this->props[static::pk()],
      ));
    }

    static::callback($this, 'after_save');

    return $this;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count($params = array()) {
    return (int) static::conn()->result(static::conn()->select(static::table(), 'COUNT(*)', ! empty($params['where']) ? $params['where'] : $params));
  }


  /**
   * Handle missing methods
   *
   * @param  string Method
   * @param  array  Arguments
   * @return mixed
   */
  final public static function missing($method, $arguments) {
    if (strpos($method, 'find_by_') === 0) {
      $row = static::conn()->fetch(static::conn()->select(static::table(), ALL, array(
        substr($method, 8) => $arguments,
      )), AS_ARRAY);

      return $row ? new static($row, 'after_find') : FALSE;
    } elseif (strpos($method, 'count_by_') === 0) {
      return static::count(static::merge(substr($method, 9), $arguments));
    } elseif (strpos($method, 'find_or_create_by_') === 0) {
      $test = static::merge(substr($method, 18), $arguments);
      $res  = static::conn()->select(static::table(), ALL, $test);

      return static::conn()->numrows($res) ? new static(static::conn()->fetch($res, AS_ARRAY), 'after_find') : static::create($test);
    }

    if (method_exists(static::conn(), $method)) {
      return call_user_func_array(array(static::conn(), $method), $arguments);
    }
    return parent::super($method, $arguments);
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns() {
    $idx = get_called_class() . '_columns';

    if (empty(static::$cache[$idx])) {
      static::$cache[$idx] = static::conn()->columns(static::table());
    }
    return static::$cache[$idx];
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk() {
    $idx = get_called_class() . '_pk';

    if (empty(static::$cache[$idx])) {
      foreach (static::columns() as $key => $one) {
        if ($one['type'] === 'primary_key') {
          static::$cache[$idx] = $key;

          break;
        }
      }

      if ( ! isset(static::$cache[$idx])) {
        raise(ln('ar.primary_key_missing', array('model' => get_called_class())));
      }
    }
    return static::$cache[$idx];
  }


  /**
   * Delete all records
   *
   * @param  array Where
   * @return void
   */
  final public static function delete_all(array $params = array()) {
    static::conn()->delete(static::table(), $params);
  }


  /**
   * Update all records
   *
   * @param  array Fields
   * @param  array Where
   * @return void
   */
  final public static function update_all(array $data, array $params = array()) {
    static::conn()->update(static::table(), $data, $params);
  }


  /**#@+
   * @ignore
   */

  // cached connection
  final private static function conn() {
    return db::connect(option('database.' . static::$database));
  }

  // each iteration
  final protected static function block($get, $where, $params, $lambda) {
    $res = static::conn()->select(static::table(), $get ?: ALL, $where, $params);
    while ($row = static::conn()->fetch($res, AS_ARRAY)) {
      $lambda(new static($row, 'after_find'));
    }
  }

  // find rows
  final protected static function finder($wich, $what, $where, $options) {
    switch ($wich) {
      case 'first';
      case 'last';
        $options['limit'] = 1;
        $options['order'] = array(
          static::pk() => $wich === 'first' ? ASC : DESC,
        );

        $row = static::conn()->fetch(static::conn()->select(static::table(), $what ?: ALL, $where, $options), AS_ARRAY);

        return $row ? new static($row, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = static::conn()->select(static::table(), $what ?: ALL, $where, $options);

        while ($row = static::conn()->fetch($res, AS_ARRAY)) {
          $out []= new static($row, 'after_find');
        }
        return $out;
      break;
      default;
        $row = static::conn()->fetch(static::conn()->select(static::table(), $what ?: ALL, array(
          static::pk() => $wich,
        ), $options), AS_ARRAY);

        return $row ? new static($row, 'after_find') : FALSE;
      break;
    }
  }

  /**#@-*/

}

/* EOF: ./library/a_record/drivers/db_model.php */
