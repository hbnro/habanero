<?php

/**
 * MVC model
 */

class model extends prototype
{

  /**#@+
   * @ignore
   */

  // model properties
  private $_props = array();

  // new record?
  private $_new_record = NULL;

  /**#@-*/

//TODO: validations, relations?

  // table name
  public static $table = NULL;

  // default primary key
  public static $primary_key = NULL;



  /**#@+
   * @ignore
   */

  // model constructor
  public function __construct(array $params = array(), $create = FALSE, $method = NULL)
  {
    $this->_new_record = (bool) $create;

    foreach (array_keys(static::columns()) as $key)
    {
      $this->_props[$key] = ! empty($params[$key]) ? $params[$key] : NULL;
    }
    static::callback($this, $method);
  }

  // properties getter
  public function __get($key)
  {
    if ( ! array_key_exists($key, $this->_props))
    {
      raise(ln('mvc.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    return $this->_props[$key];
  }

  // properties setter
  public function __set($key, $value)
  {
    if ( ! array_key_exists($key, $this->_props))
    {
      raise(ln('mvc.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    $this->_props[$key] = $value;
  }

  /**#@-*/



  /**
   * Save row
   *
   * @return model
   */
  final public function save()
  {
    static::callback($this, 'before_save');

    if ($this->is_new())
    {
      $fields = $this->_props;

      unset($fields[static::pk()]);

      if (array_key_exists('created_at', $fields))
      {
        $fields['created_at'] = $fields['modified_at'] = date('Y-m-d H:i:s');
      }

      $this->_props[static::pk()] = db::insert(static::table(), $fields);
    }
    else
    {
      db::update(static::table(), $this->_props, array(
        static::pk() => $this->_props[static::pk()],
      ));
    }

    static::callback($this, 'after_save');

    return $this;
  }


  /**
   * Update row
   *
   * @return model
   */
  final public function update()
  {
    static::callback($this, 'before_update');


    $fields = $this->_props;

    unset($fields[static::pk()]);

    if (array_key_exists('modified_at', $fields))
    {
      $fields['modified_at'] = date('Y-m-d H:i:s');
    }


    db::update(static::table(), $fields, array(
      static::pk() => $this->_props[static::pk()],
    ));

    static::callback($this, 'after_update');

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
      static::pk() => $this->_props[static::pk()],
    ));

    static::callback($this, 'after_delete');

    return $this;
  }


  /**
   * Is a fresh record?
   *
   * @return boolean
   */
  final public function is_new()
  {
    return $this->_new_record;
  }


  /**
   * Create row without saving
   *
   * @param  array Properties
   * @return model
   */
  final public static function build(array $params = array())
  {
    $row   = (object) $params;

    static::callback($row, 'before_create');

    return new static((array) $row, TRUE, 'after_create');
  }


  /**
   * Create row and save it
   *
   * @param  array Properties
   * @return model
   */
  final public static function create(array $params = array())
  {
    return static::build($params)->save();
  }


  /**
   * There is a record?
   *
   * @return boolean
   */
  final public static function exists($params = array())
  {
    return static::count($params) > 0;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count($params = array())
  {
    return (int) db::result(db::select(static::table(get_called_class()), 'COUNT(*)', $params));
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
  final public static function missing($method, array $arguments = array())
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

      return call_user_func_array("static::find", $arguments);
    }
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns()
  {
    return db::columns(static::table());
  }


  /**
   * Retrieve table name
   *
   * @return array
   */
  final public static function table()
  {
    return static::$table ?: get_called_class();
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk()
  {
    if ( ! static::$primary_key)
    {
      foreach (static::columns() as $key => $one)
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

  // execute callbacks
  final private static function callback($row, $method)
  {
    static::defined($method) && static::$method($row);
  }

  // dynamic where
  final private static function where($as, $are)
  {
    return array_combine(preg_split('/_and_/', $as), $are);
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/mvc/model.php */
