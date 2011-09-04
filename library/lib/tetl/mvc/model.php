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

  // columns definition
  private static $defs = array();

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
    $class = get_called_class();

    $this->_new_record = (bool) $create;

    foreach (array_keys($class::columns()) as $key)
    {
      $this->_props[$key] = ! empty($params[$key]) ? $params[$key] : NULL;
    }
    $class::callback($this, $method);
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
    $class = get_called_class();

    $class::callback($this, 'before_save');

    if ($this->is_new())
    {
      $fields = $this->_props;

      unset($fields[$class::pk()]);

      $this->_props[$class::pk()] = db::insert($class::table(), $fields);
    }
    else
    {
      db::update($class::table(), $this->_props, array(
        $class::pk() => $this->_props[$class::pk()],
      ));
    }

    $class::callback($this, 'after_save');

    return $this;
  }


  /**
   * Update row
   *
   * @return model
   */
  final public function update()
  {
    $class = get_called_class();

    $class::callback($this, 'before_update');


    $fields = $this->_props;

    unset($fields[$class::pk()]);

    db::update($class::table(), $fields, array(
      $class::pk() => $this->_props[$class::pk()],
    ));

    $class::callback($this, 'after_update');

    return $this;
  }


  /**
   * Delete row
   *
   * @return model
   */
  final public function delete()
  {
    $class = get_called_class();

    $class::callback($this, 'before_delete');

    db::delete($class::table(), array(
      $class::pk() => $this->_props[$class::pk()],
    ));

    $class::callback($this, 'after_delete');

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
   * Create row (not save)
   *
   * @param  array Properties
   * @return model
   */
  final public static function create(array $params = array())
  {
    $row   = (object) $params;
    $class = get_called_class();

    $class::callback($row, 'before_create');

    return new $class((array) $row, TRUE, 'after_create');
  }



  /**
   * There is a record?
   *
   * @return boolean
   */
  final public static function exists($params = array())
  {
    return self::count($params) > 0;
  }


  /**
   * Row count
   *
   * @return integer
   */
  final public static function count($params = array())
  {
    return (int) db::result(db::select(self::table(get_called_class()), 'COUNT(*)', $params));
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
    $class   = get_called_class();

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
          $class::pk() => $wich === 'first' ? ASC : DESC,
        );

        $row = db::fetch(db::select($class::table(), $what, $where, $options), AS_ARRAY);

        return $row ? new $class($row, FALSE, 'after_find') : FALSE;
      break;
      case 'all';
        $out = array();
        $res = db::select($class::table(), $what, $where, $options);

        while ($row = db::fetch($res, AS_ARRAY))
        {
          $out []= new $class($row, FALSE, 'after_find');
        }
        return $out;
      break;
      default;
        array_unshift($args, $what);
      break;
    }

    $row = db::fetch(db::select($class::table(), $what, array(
      $class::pk() => $args,
    )), AS_ARRAY);

    return $row ? new $class($row, FALSE, 'after_find') : FALSE;
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
    $class = get_called_class();

    if (strpos($method, 'find_by_') === 0)
    {
      $row = db::fetch(db::select($class::table(), ALL, array(
        substr($method, 8) => $arguments,
      )), AS_ARRAY);

      return $row ? new $class($row, FALSE, 'after_find') : FALSE;
    }
    elseif (strpos($method, 'count_by_') === 0)
    {
      return $class::count(array(
        substr($method, 9) => $arguments,
      ));
    }
    elseif (strpos($method, 'find_or_create_by_') === 0)
    {
      $res = db::select($class::table(), ALL, array(
        substr($method, 18) => $arguments,
      ));

      if (db::numrows($res))
      {
        return new $class(db::fetch($res, AS_ARRAY), FALSE, 'after_find');
      }

      $test = preg_split('/_(?:or|and)_/', substr($method, 18));
      $test = array_combine($test, $arguments);

      return $class::create($test)->save();
    }
    else
    {
      if (preg_match('/^find_(all|first|last)_by_(.+)$/', $method, $match))
      {
        return $class::find($match[1], array(
          'where' => array(
            $match[2] => $arguments,
          ),
        ));
      }

      array_unshift($arguments, $method);

      return call_user_func_array("$class::find", $arguments);
    }
  }


  /**
   * Retrieve columns
   *
   * @return array
   */
  final public static function columns()
  {
    if (empty(self::$defs[self::table()]))
    {// TODO: hackish, I guess
      self::$defs[self::table()] = db::columns(self::table());
    }
    return self::$defs[self::table()];
  }


  /**
   * Retrieve table name
   *
   * @return array
   */
  final public static function table()
  {
    return self::$table ?: get_called_class();
  }


  /**
   * Retrieve primary key
   *
   * @return array
   */
  final public static function pk()
  {
    if ( ! self::$primary_key)
    {
      foreach (self::columns() as $key => $one)
      {
        if ($one['type'] === 'primary_key')
        {
          self::$primary_key = $key;

          break;
        }
      }

      if ( ! self::$primary_key)
      {
        raise(ln('mvc.primary_key_missing', array('model' => get_called_class())));
      }
    }

    return self::$primary_key;
  }



  /**#@+
   * @ignore
   */

  // execute callbacks
  final private static function callback($row, $method)
  {
    self::defined($method) && self::$method($row);
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/mvc/model.php */
