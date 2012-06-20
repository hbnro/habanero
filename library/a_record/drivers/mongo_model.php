<?php

/**
 * MongoDB model
 */

class mongo_model extends a_record
{

  // properties
  public static $columns = array();

  // connection
  public static $database = 'mongodb';


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

    $start  = ticks();
    $fields = static::stamp($this->changed, $this->props, $this->is_new());

    unset($fields['_id']);

    $params = compact('fields');
    $type   = $this->is_new() ? 'insert' : 'update';

    if ($this->is_new()) {
      if (static::conn()->insert($fields)) {
        $this->new_record = FALSE;
        $this->props = $fields;
      }
    } else {
      static::conn()->update(array(
        '_id' => $this->props['_id'],
      ), $fields);
    }

    static::debug($type, $start, compact('params'));
    static::callback($this, 'after_save');

    return $this;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count(array $params = array()) {
    $start = ticks();
    $out   = (int) static::conn()->count( ! empty($params['where']) ? $params['where'] : $params);
    static::debug('count', $start, compact('params'));
    return $out;
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
      $where = static::merge(substr($method, 8), $arguments);
      $row   = static::select(array(), $where, array('single' => TRUE));

      return $row ? new static($row, 'after_find') : FALSE;
    } elseif (strpos($method, 'count_by_') === 0) {
      return static::count(static::merge(substr($method, 9), $arguments));
    } elseif (strpos($method, 'find_or_create_by_') === 0) {
      $where = static::merge(substr($method, 18), $arguments);
      $res   = static::select(array(), $where, array('single' => TRUE));

      return $res ? new static($res, 'after_find') : static::create($where);
    }

    return static::super($method, $arguments);
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns() {
    return array_merge(array(
      '_id' => array(
        'type' => 'primary_key',
      ),
    ), static::$columns);
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk() {
    return '_id';
  }


  /**
   * Delete all records
   *
   * @param  array Where
   * @return void
   */
  final public static function delete_all(array $params = array()) {
    $start = ticks();

    if (array_key_exists('_id', $params)) {
      $params['_id'] = static::ids($params['_id']);
    }

    $out = static::conn()->remove($params);
    static::debug('delete', $start, compact('params'));

    return $out;
  }


  /**
   * Update all records
   *
   * @param  array Fields
   * @param  array Where
   * @return void
   */
  final public static function update_all(array $data, array $params = array()) {
    $start = ticks();

    $tmp = (object) $data;

    static::callback($tmp, 'before_save');

    $data = array('$set' => (array) $tmp);

    if (array_key_exists('_id', $params)) {
      $params['_id'] = static::ids($params['_id']);
    }

    $out = static::conn()->update($params, $data, array('multiple' => TRUE));
    static::debug('update', $start, compact('params', 'data'));

    static::callback($tmp, 'after_save');

    return $out;
  }



  /**#@+
   * @ignore
   */

  // multi _id
  final private static function ids($set) {
    if (is_array($set)) {
      $tmp = array();
      foreach ($set as $k => $v) {
        $tmp []= new MongoId($v);
      }
      return array('$in' => $tmp);
    } else {
      return new MongoId($set);
    }
  }

  // selection
  final private static function select($fields, $where, $options) {
    $start  = ticks();
    $where  = static::parse($where);
    $method = ! empty($options['single']) ? 'findOne' : 'find';

    if (array_key_exists('_id', $where)) {
      $where['_id'] = static::ids($where['_id']);
      $method = 'findOne';
    }

    $row = static::conn()->$method($where, $fields);

    ! empty($options['limit']) && $row->limit($options['limit']);
    ! empty($options['offset']) && $row->skip($options['offset']);

    //TODO: WTF with group?
    if ( ! empty($options['order'])) {
      foreach ($options['order'] as $key => $val) {
        $options['order'][$key] = $val === 'DESC' ? -1 : 1;
      }
      $row->sort($options['order']);
    }

    $out = is_object($row) ? iterator_to_array($row) : $row;
    static::debug('select', $start, compact('fields', 'where'));

    return $out;
  }

  // dynamic where
  private static function parse($test) {// TODO: implement Javascript filter callbacks...
    foreach ($test as $key => $val) {
      unset($test[$key]);

      if (is_keyword($key)) {
        $test['$' . strtolower($key)] = $val;
      } elseif (strpos($key, '/_or_/')) {
        $test['$or'] = array();

        foreach (explode('_or_') as $one) {
          $test['$or'] []= array($one => $val);
        }
      } elseif (preg_match('/^(.+?)(\s+(!=?|[<>]=?|<>|NOT|R?LIKE)\s*|)$/', $key, $match)) {
        switch ($match[2]) {// TODO: do testing!
          case 'NOT'; case '<>'; case '!'; case '!=';
            $test[$match[1]] = array(is_array($val) ? '$nin': '$ne' => $val);
          break;
          case '<'; case '<=';
            $test[$match[1]] = array('$lt' . (substr($match[2], -1) === '=' ? 'e' : '') => $val);
          break;
          case '>'; case '>=';
            $test[$match[1]] = array('$gt' . (substr($match[2], -1) === '=' ? 'e' : '') => $val);
          break;
          case 'RLIKE';
            $test[$match[1]] = '/' . preg_quote($match[2], '/') . '/gis';
          break;
          case 'LIKE';
            $test[$match[1]] = '/' . str_replace('\\\\\*', '.*?', preg_quote($match[2], '/')) . '/gis';
          break;
          default;
            $test[$match[1]] = is_array($val) ? array('$in' => $val) : $val;
          break;
        }
      }
    }

    return $test;
  }

  // connection
  final private static function conn() {
    if (empty(static::$cache[static::$database])) {
      $dsn_string = option('database.' . static::$database);
      $database   = substr($dsn_string, strrpos($dsn_string, '/') + 1);


      $mongo    = $dsn_string ? new Mongo($dsn_string) : new Mongo;
      $database = $database ?: 'default';

      static::$cache[static::$database] = $mongo->$database;

      logger::debug("Connect: mongodb:$database@$mongo");
    }
    return static::$cache[static::$database]->{static::table()};
  }

  // each iteration
  final protected static function block($get, $where, $params, $lambda) {
    $res = static::select($get, $where, $params);
    while ($row = array_shift($res)) {
      $lambda(new static($row, 'after_find', FALSE, $params));
    }
  }

  // find rows
  final protected static function finder($wich, $what, $where, $options) {
    switch ($wich) {
      case 'first';
      case 'last';
        $row = static::select($what, $where, array(
          'offset' => $wich === 'first' ? 0 : static::count($where) - 1,
          'limit' => 1,
        ));

        return $row ? new static(array_shift($row), 'after_find', FALSE, $options) : FALSE;
      break;
      case 'all';
        $out = array();
        $res = static::select($what, $where, $options);

        while ($row = array_shift($res)) {
          $out []= new static($row, 'after_find', FALSE, $options);
        }
        return $out;
      break;
      default;
        $row = static::select($what, array(
          '_id' => $wich,
        ), $options);

        return $row ? new static($row, 'after_find', FALSE, $options) : FALSE;
      break;
    }
  }

  // debug
  final protected static function debug($type, $start, $params) {
    logger::debug(sprintf("(%s) $type#%s %s", ticks($start), static::table(), dump($params)));
  }

  /**#@-*/
}

/* EOF: ./library/a_record/drivers/mongo_model.php */
