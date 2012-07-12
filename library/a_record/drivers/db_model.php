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


    $fields = static::stamp($this);

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
   * MAX value
   *
   * @return integer
   */
  final public static function max($field, array $params = array()) {
    return (int) static::conn()->result(static::conn()->select(static::table(), "MAX($field)", ! empty($params['where']) ? $params['where'] : $params));
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count(array $params = array()) {
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
      $test = static::merge(substr($method, 8), $arguments);
      $row  = static::conn()->fetch(static::conn()->select(static::table(), ALL, $test), AS_ARRAY);

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
    return static::conn()->delete(static::table(), $params);
  }


  /**
   * Update all records
   *
   * @param  array Fields
   * @param  array Where
   * @return void
   */
  final public static function update_all(array $data, array $params = array()) {
    $tmp = (object) $data;

    static::callback($tmp, 'before_save');

    return static::conn()->update(static::table(), (array) $tmp, $params);

    static::callback($tmp, 'after_save');
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
    $res = static::conn()->select(static::table(), static::defaults($get), $where, $params);
    while ($row = static::conn()->fetch($res, AS_ARRAY)) {
      $lambda(new static($row, 'after_find', FALSE, $params));
    }
  }

  // find rows
  final protected static function finder($wich, $what, $where, $options) {
    switch ($wich) {
      case 'first';
      case 'last';
        $options['limit'] = 1;

        if (empty($options['order'])) {
          $options['order'] = array(
            static::pk() => $wich === 'first' ? ASC : DESC,
          );
        }

        $row = static::conn()->fetch(static::conn()->select(static::table(), static::defaults($what), $where, $options), AS_ARRAY);

        return $row ? new static($row, 'after_find', FALSE, $options) : FALSE;
      break;
      case 'all';
        $out = array();
        $res = static::conn()->select(static::table(), static::defaults($what), $where, $options);

        while ($row = static::conn()->fetch($res, AS_ARRAY)) {
          $out []= new static($row, 'after_find', FALSE, $options);
        }
        return $out;
      break;
      default;
        $row = static::conn()->fetch(static::conn()->select(static::table(), static::defaults($what), array(
          static::pk() => $wich,
        ), $options), AS_ARRAY);

        return $row ? new static($row, 'after_find', FALSE, $options) : FALSE;
      break;
    }
  }

  // populate fields in some WAT fashion!
  final private static function defaults($out) {
    if ( ! $out) {
      $out = ALL;
    } else {
      $id  = static::pk();
      $out = (array) $out; // FIX?
      ! in_array($id, $out) && array_unshift($out, $id);
    }
    return $out;
  }

  /**#@-*/

}

/* EOF: ./library/a_record/drivers/db_model.php */
