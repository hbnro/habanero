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


    $fields = $this->props;

    unset($fields['_id']);

    if ($this->is_new())
    {
      if (array_key_exists('created_at', $fields))
      {
        $fields['created_at'] = $fields['modified_at'] = date('Y-m-d H:i:s');
      }

      if (static::conn()->insert($fields))
      {
        $last_record = static::conn()->findOne($fields, (array) '_id');
        $this->props['_id'] = $last_record['_id'];
        $this->new_record = FALSE;
      }
    }
    else
    {
      if (array_key_exists('modified_at', $fields))
      {
        $fields['modified_at'] = date('Y-m-d H:i:s');
      }

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

    $what = ! empty($options['select']) ? $options['select'] : static::columns();

// TODO: implement limit and offset (see skip() & limit())
    switch ($wich)
    {
      case 'first';
      case 'last';
        $options['limit'] = 1;
        $options['order'] = array(
          '_id' => $wich === 'first' ? 1 : -1,
        );

        $row = static::conn()->find($where, $what);

        return $row ? new static(iterator_to_array($row), FALSE, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = iterator_to_array(static::conn()->find($where, $what));

        while ($row = array_shift($res))
        {
          $out []= new static($row, FALSE, 'after_find');
        }
        return $out;
      break;
      default;
        array_unshift($args, $what);
      break;
    }

    $row = static::conn()->find(array(
      '_id' => $args,
    ), $what);

    return $row ? new static(iterator_to_array($row), FALSE, 'after_find') : FALSE;
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
      $row = static::conn()->find(array(
        substr($method, 8) => $arguments,
      ));

      return $row ? new static(iterator_to_array($row), FALSE, 'after_find') : FALSE;
    }
    elseif (strpos($method, 'count_by_') === 0)
    {
      return static::count(static::where(substr($method, 9), $arguments));
    }
    elseif (strpos($method, 'find_or_create_by_') === 0)
    {
      $test = static::where(substr($method, 18), $arguments);
      $res  = static::conn()->findOne($test);

      if ($res)
      {
        return new static($res, FALSE, 'after_find');
      }
      return static::create($test);
    }
    else
    {
      if (preg_match('/^find_(all|first|last)_by_(.+)$/', $method, $match))
      {
        return static::find($match[1], array(
          'where' => static::where($match[2], $arguments),
        ));
      }

      array_unshift($arguments, $method);

      return apply(get_called_class() . '::find', $arguments);
    }
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns()
  {
    return array_merge((array) '_id', static::$columns);
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



  /**#@+
   * @ignore
   */

  // dynamic where
  final private static function where($as, $are)
  {// TODO: implement Javascript filter callbacks...
    $as     = preg_split('/_and_/', $as);
    $length = max(sizeof($as), sizeof($are));

    return array_combine(array_slice($as, 0, $length), array_slice($are, 0, $length));
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
