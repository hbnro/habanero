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

        return $row ? new static(array_shift($row), FALSE, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = static::select($what, $where, $options);

        while ($row = array_shift($res))
        {
          $out []= new static($row, FALSE, 'after_find');
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

    return $row ? new static(array_shift($row), FALSE, 'after_find') : FALSE;
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
      $where = static::where(substr($method, 8), $arguments);
      $row   = static::conn()->findOne($where);

      return $row ? new static($row, FALSE, 'after_find') : FALSE;
    }
    elseif (strpos($method, 'count_by_') === 0)
    {
      return static::count(static::where(substr($method, 9), $arguments));
    }
    elseif (strpos($method, 'find_or_create_by_') === 0)
    {
      $test = static::where(substr($method, 18), $arguments);
      $res  = static::conn()->findOne($test);

      return $res ? new static($res, FALSE, 'after_find') : static::create($test);
    }
    elseif (preg_match('/^find_(all|first|last)_by_(.+)$/', $method, $match))
    {
      return static::find($match[1], array(
        'where' => static::where($match[2], $arguments),
      ));
    }


    if (in_array($method, get_class_methods(get_class())))
    {// TODO: why?
      return call_user_func_array("static::$method", $arguments);
    }

    array_unshift($arguments, $method);

    return apply(get_called_class() . '::find', $arguments);
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
    $row = static::conn()->find($where, $fields);

    ! empty($options['limit']) && $row->limit($options['limit']);
    ! empty($options['offset']) && $row->skip($options['offset']);

    return iterator_to_array($row);
  }

  // dynamic where
  final private static function where($as, $are)
  {// TODO: implement Javascript filter callbacks...
    $as   = preg_split('/_and_/', $as);
    $test = array_combine($as, $are);

    return $test;
  }

  // connection
  final private static function conn()
  {// TODO: improve connection and database?
    static $conn = NULL;


    if (is_null($conn))
    {
      $dsn_string = option('mongo.dsn');

      $conn = $dsn_string ? new Mongo($dsn_string) : new Mongo;
    }


    $database   = 'default';
    $collection = static::table();

    return $conn->$database->$collection;
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/mvc/model/mongo.php */