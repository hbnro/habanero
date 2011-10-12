<?php

/**
 * MongoDB model
 */

class mongdel extends model
{

  // properties
  public static $columns = array();



  /**
   * Save row
   *
   * @param  boolean Skip validation?
   * @return model
   */
  final public function save($skip = FALSE)
  {
    static::callback($this, 'before_save');

    if ( ! $skip && ! $this->is_valid())
    {
      return FALSE;
    }


    $fields = static::stamp($this->props, $this->is_new());

    unset($fields['_id']);

    if ($this->is_new())
    {
      if (static::conn()->insert($fields))
      {
        $last_record = static::conn()->findOne($fields, (array) '_id');
        $this->props['_id'] = $last_record['_id'];
        $this->new_record = FALSE;
      }
    }
    else
    {
      static::conn()->update(array(
        '_id' => $this->props['_id'],
      ), $fields);
    }

    static::callback($this, 'after_save');

    return $this;
  }


  /**
   * Delete row
   *
   * @return model
   */
  final public function delete()
  {
    static::callback($this, 'before_delete');

    static::conn()->remove(array(
      '_id' => $this->props['_id'],
    ));

    static::callback($this, 'after_delete');

    return $this;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count($params = array())
  {
    return (int) static::conn()->count( ! empty($params['where']) ? $params['where'] : $params);
  }


  /**
   * Find rows
   *
   * @param  mixed ID|Properties|...
   * @return mixed
   */
  final public static function find()
  {
    $args    = func_get_args();

    $wich    = array_shift($args);
    $params  = array_pop($args);

    $where   =
    $options = array();

    if ($params && ! is_assoc($params))
    {
      $args []= $params;
    }
    else
    {
      $options = (array) $params;
    }

    if ( ! empty($options['where']))
    {
      $where = (array) $options['where'];
    }

    $what = ! empty($options['select']) ? $options['select'] : array();


    switch ($wich)
    {
      case 'first';
      case 'last';
        $row = static::select($what, $where, array(
          'offset' => $wich === 'first' ? 0 : static::count($where) - 1,
          'limit' => 1,
        ));

        return $row ? new static(array_shift($row), 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = static::select($what, $where, $options);

        while ($row = array_shift($res))
        {
          $out []= new static($row, 'after_find');
        }
        return $out;
      break;
      default;
        array_unshift($args, $wich);
      break;
    }


    $row = static::select($what, array(
      '_id' => array_shift($args),
    ), $options);

    return $row ? new static(array_shift($row), 'after_find') : FALSE;
  }


  /**
   * Handle missing methods
   *
   * @param  string Method
   * @param  array  Arguments
   * @return mixed
   */
  final public static function missing($method, $arguments)
  {
    if (strpos($method, 'find_by_') === 0)
    {
      $where = static::merge(substr($method, 8), $arguments);
      $row   = static::select(array(), $where, array('single' => TRUE));

      return $row ? new static($row, 'after_find') : FALSE;
    }
    elseif (strpos($method, 'count_by_') === 0)
    {
      return static::count(static::merge(substr($method, 9), $arguments));
    }
    elseif (strpos($method, 'find_or_create_by_') === 0)
    {
      $where = static::merge(substr($method, 18), $arguments);
      $res   = static::select(array(), $where, array('single' => TRUE));

      return $res ? new static($res, 'after_find') : static::create($where);
    }
    elseif (preg_match('/^(?:find_)?(all|first|last)_by_(.+)$/', $method, $match))
    {
      return static::find($match[1], array(
        'where' => static::merge($match[2], $arguments),
      ));
    }

    return static::super($method, $arguments);
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns()
  {
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
  final public static function pk()
  {
    return '_id';
  }


  /**
   * Delete all records
   *
   * @param  array Where
   * @return void
   */
  final public static function delete_all(array $params = array())
  {
    static::conn()->remove($params);
  }


  /**
   * Update all records
   *
   * @param  array Fields
   * @param  array Where
   * @return void
   */
  final public static function update_all(array $data, array $params = array())
  {
    static::conn()->update($params, $data, array('multiple' => TRUE));
  }



  /**#@+
   * @ignore
   */

  // selection
  final private static function select($fields, $where, $options)
  {
    $method = ! empty($options['single']) ? 'findOne' : 'find';
    $row    = static::conn()->$method(static::parse($where), $fields);

    ! empty($options['limit']) && $row->limit($options['limit']);
    ! empty($options['offset']) && $row->skip($options['offset']);

    //TODO: WTF with group?
    if ( ! empty($options['order']))
    {
      foreach ($options['order'] as $key => $val)
      {
        $options['order'][$key] = $val === DESC ? -1 : 1;
      }
      $row->order($options['order']);
    }

    return is_object($row) ? iterator_to_array($row) : $row;
  }

  // dynamic where
  private static function parse($test)
  {// TODO: implement Javascript filter callbacks...
    foreach ($test as $key => $val)
    {
      unset($test[$key]);

      if (is_keyword($key))
      {
        $test['$' . strtolower($key)] = $val;
      }
      elseif (strpos($key, '/_or_/'))
      {
        $test['$or'] = array();

        foreach (explode('_or_') as $one)
        {
          $test['$or'] []= array($one => $val);
        }
      }
      elseif (preg_match('/^(.+?)(\s+(!=?|[<>]=|<>|NOT|R?LIKE)\s*|)$/', $key, $match))
      {
        switch ($match[2])
        {// TODO: do testing!
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
            $test[$match[1]] = sprintf('/%s/gis', preg_quote($match[2], '/'));
          break;
          case 'LIKE';
            $test[$match[1]] = sprintf('/%s/gis', str_replace('\\\\\*', '.*?', preg_quote($match[2], '/')));
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
  final private static function conn()
  {
    static $conn = NULL;


    if (is_null($conn))
    {
      $database   = option('mongo.db');
      $dsn_string = option('mongo.dsn');

      $mongo    = $dsn_string ? new Mongo($dsn_string) : new Mongo;
      $database = $database ?: 'default';
      $conn     = $mongo->$database;
    }

    return $conn->{static::table()};
  }

  /**#@-*/
}

/* EOF: ./stack/library/app/base/model/drivers/mongdel.php */
