<?php

import('tetl/db');

/**
 * DB model
 */

class dbmodel extends model
{

  // primary key
  public static $primary_key = NULL;



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
      $this->props[static::pk()] = db::insert(static::table(), $fields);
      $this->new_record = FALSE;
    } else {
      db::update(static::table(), $fields, array(
        static::pk() => $this->props[static::pk()],
      ));
    }

    static::callback($this, 'after_save');

    return $this;
  }


  /**
   * Delete row
   *
   * @return model
   */
  final public function delete() {
    static::callback($this, 'before_delete');

    db::delete(static::table(), array(
      static::pk() => $this->props[static::pk()],
    ));

    static::callback($this, 'after_delete');

    return $this;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count($params = array()) {
    return (int) db::result(db::select(static::table(), 'COUNT(*)', ! empty($params['where']) ? $params['where'] : $params));
  }


  /**
   * Find rows
   *
   * @param  mixed ID|Properties|...
   * @return mixed
   */
  final public static function find() {
    $args    = func_get_args();

    $wich    = array_shift($args);
    $params  = array_pop($args);

    $where   =
    $options = array();

    if ($params && ! is_assoc($params)) {
      $args []= $params;
    } else {
      $options = (array) $params;
    }

    if ( ! empty($options['where'])) {
      $where = (array) $options['where'];
    }

    $what = ! empty($options['select']) ? $options['select'] : ALL;


    switch ($wich) {
      case 'first';
      case 'last';
        $options['limit'] = 1;
        $options['order'] = array(
          static::pk() => $wich === 'first' ? ASC : DESC,
        );

        $row = db::fetch(db::select(static::table(), $what, $where, $options), AS_ARRAY);

        return $row ? new static($row, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = db::select(static::table(), $what, $where, $options);

        while ($row = db::fetch($res, AS_ARRAY)) {
          $out []= new static($row, 'after_find');
        }
        return $out;
      break;
      default;
        array_unshift($args, $wich);
      break;
    }


    $row = db::fetch(db::select(static::table(), $what, array(
      static::pk() => array_shift($args),
    ), $options), AS_ARRAY);

    return $row ? new static($row, 'after_find') : FALSE;
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
      $row = db::fetch(db::select(static::table(), ALL, array(
        substr($method, 8) => $arguments,
      )), AS_ARRAY);

      return $row ? new static($row, 'after_find') : FALSE;
    } elseif (strpos($method, 'count_by_') === 0) {
      return static::count(static::merge(substr($method, 9), $arguments));
    } elseif (strpos($method, 'find_or_create_by_') === 0) {
      $test = static::merge(substr($method, 18), $arguments);
      $res  = db::select(static::table(), ALL, $test);

      return db::numrows($res) ? new static(db::fetch($res, AS_ARRAY), 'after_find') : static::create($test);
    } elseif (preg_match('/^(?:find_)?(all|first|last)_by_(.+)$/', $method, $match)) {
      return static::find($match[1], array(
        'where' => static::merge($match[2], $arguments),
      ));
    }

    return parent::super($method, $arguments);
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns() {// TODO: implements caching for this...
    return db::columns(static::table());
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk() {
    if ( ! static::$primary_key) {
      foreach (static::columns() as $key => $one) {
        if ($one['type'] === 'primary_key') {
          static::$primary_key = $key;

          break;
        }
      }

      if ( ! static::$primary_key) {
        raise(ln('mvc.primary_key_missing', array('model' => get_called_class())));
      }
    }

    return static::$primary_key;
  }


  /**
   * Delete all records
   *
   * @param  array Where
   * @return void
   */
  final public static function delete_all(array $params = array()) {
    db::delete(static::table(), $params);
  }


  /**
   * Update all records
   *
   * @param  array Fields
   * @param  array Where
   * @return void
   */
  final public static function update_all(array $data, array $params = array()) {
    db::update(static::table(), $data, $params);
  }

}

/* EOF: ./stack/library/app/base/model/drivers/dbmodel.php */
