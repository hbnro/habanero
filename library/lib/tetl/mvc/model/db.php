<?php

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
  final public function save($skip = FALSE)
  {
    static::callback($this, 'before_save');

    if ( ! $skip && ! $this->is_valid())
    {
      return FALSE;
    }


    $fields = $this->props;

    unset($fields[static::pk()]);

    if ($this->is_new())
    {
      if (array_key_exists('created_at', $fields))
      {
        $fields['created_at'] = $fields['modified_at'] = date('Y-m-d H:i:s');
      }

      $this->props[static::pk()] = db::insert(static::table(), $fields);
      $this->new_record = FALSE;
    }
    else
    {
      if (array_key_exists('modified_at', $fields))
      {
        $fields['modified_at'] = date('Y-m-d H:i:s');
      }

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
  final public function delete()
  {
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
  final public static function count($params = array())
  {
    return (int) db::result(db::select(static::table(), 'COUNT(*)', ! empty($params['where']) ? $params['where'] : $params));
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

    $what = ! empty($options['select']) ? $options['select'] : ALL;


    switch ($wich)
    {
      case 'first';
      case 'last';
        $options['limit'] = 1;
        $options['order'] = array(
          static::pk() => $wich === 'first' ? ASC : DESC,
        );

        $row = db::fetch(db::select(static::table(), $what, $where, $options), AS_ARRAY);

        return $row ? new static($row, FALSE, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = db::select(static::table(), $what, $where, $options);

        while ($row = db::fetch($res, AS_ARRAY))
        {
          $out []= new static($row, FALSE, 'after_find');
        }
        return $out;
      break;
      default;
        array_unshift($args, $what);
      break;
    }

    $row = db::fetch(db::select(static::table(), $what, array(
      static::pk() => $args,
    )), AS_ARRAY);

    return $row ? new static($row, FALSE, 'after_find') : FALSE;
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
      $row = db::fetch(db::select(static::table(), ALL, array(
        substr($method, 8) => $arguments,
      )), AS_ARRAY);

      return $row ? new static($row, FALSE, 'after_find') : FALSE;
    }
    elseif (strpos($method, 'count_by_') === 0)
    {
      return static::count(static::where(substr($method, 9), $arguments));
    }
    elseif (strpos($method, 'find_or_create_by_') === 0)
    {
      $test = static::where(substr($method, 18), $arguments);
      $res  = db::select(static::table(), ALL, $test);

      if (db::numrows($res))
      {
        return new static(db::fetch($res, AS_ARRAY), FALSE, 'after_find');
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
  {// TODO: implements caching for this...
    return array_keys(db::columns(static::table()));
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk()
  {
    if ( ! static::$primary_key)
    {// TODO: caching also saves a lot right here?
      foreach (db::columns(static::table()) as $key => $one)
      {
        if ($one['type'] === 'primary_key')
        {
          static::$primary_key = $key;

          break;
        }
      }

      if ( ! static::$primary_key)
      {
        raise(ln('mvc.primary_key_missing', array('model' => get_called_class())));
      }
    }

    return static::$primary_key;
  }



  /**#@+
   * @ignore
   */

  // dynamic where
  final protected static function where($as, $are)
  {
    $as     = preg_split('/_and_/', $as);
    $length = max(sizeof($as), sizeof($are));

    return array_combine(array_slice($as, 0, $length), array_slice($are, 0, $length));
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/mvc/model/db.php */
