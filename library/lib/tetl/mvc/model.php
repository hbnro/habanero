<?php

/**
 * MVC base model
 */

class model extends prototype
{

  /**#@+
   * @ignore
   */

  // mongdel properties
  protected $props = array();

  // new record?
  protected $new_record = NULL;

  // valid record?
  protected $valid_record = NULL;

  // validation errors
  protected $error_list = array();

  /**#@-*/


  // table name
  public static $table = NULL;

  // validation rules
  public static $validate = array();

  // simple relations
  public static $relations = array();



  /**#@+
   * @ignore
   */

  // model constructor
  protected function __construct(array $params = array(), $create = FALSE, $method = NULL)
  {
    $this->new_record = (bool) $create;

    foreach (array_keys(static::columns()) as $key)
    {
      $this->props[$key] = ! empty($params[$key]) ? $params[$key] : NULL;
    }
    static::callback($this, $method);
  }

  // properties getter
  public function __get($key)
  {
    if ( ! array_key_exists($key, $this->props))
    {
      raise(ln('mvc.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    return $this->props[$key];
  }

  // properties setter
  public function __set($key, $value)
  {
    if ( ! array_key_exists($key, $this->props))
    {
      raise(ln('mvc.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    $this->props[$key] = $value;
  }

  // relationships caller
  public function __call($method, array $arguments = array())
  {
    $what  = 'all';
    $where = array();

    if (substr($method, 0, 6) === 'count_')
    {
      $method = substr($method, 6);
      $what   = 'count';
    }
    elseif (preg_match('/^(first|last)_of_(\w+)$/', $method, $match))
    {
      $method = $match[2];
      $what   = $match[1];
    }


    if ( ! is_false(strpos($method, '_by_')))
    {
      $params = explode('_by_', $method, 2);

      $params && $method = array_shift($params);
      $params && $where = static::where($params[0], $arguments);
    }


    if ($test = static::fetch_relation($method))
    {
      $params = (array) array_shift($arguments);
      $params = array_merge(array(
        'where' => array_merge(array(
          $test['on'] => $this->props[$test['fk']],
        ), $where ?: array()),
      ), $params);


      $method = $what === 'count' ? $what : (is_true($test['has_many']) ? $what : 'first');

      return $test['from']::$method($params);
    }
    raise(ln('mvc.undefined_relationship', array('name' => $method, 'class' => get_called_class())));
  }

  /**#@-*/



  /**
   * Retrieve record ID
   *
   * @return mixed
   */
  function id()
  {
    return $this->props[static::pk()];
  }


  /**
   * Is a fresh record?
   *
   * @return boolean
   */
  final public function is_new()
  {
    return $this->new_record;
  }


  /**
   * Is a valid record?
   *
   * @return boolean
   */
  final public function is_valid()
  {
    if ( ! static::$validate)
    {
      return TRUE;
    }
    elseif (is_null($this->valid_record))
    {
      valid::setup(static::$validate);

      $this->valid_record = valid::done($this->props);
      $this->error_list   = valid::error();
    }
    return $this->valid_record;
  }


  /**
   * Retrieve validation errors
   *
   * @return array
   */
  final public function errors()
  {
    return $this->error_list;
  }


  /**
   * Create row without saving
   *
   * @param  array Properties
   * @return model
   */
  final public static function build(array $params = array())
  {
    $row = (object) $params;

    static::callback($row, 'before_create');

    return new static((array) $row, TRUE, 'after_create');
  }


  /**
   * Create row and save it
   *
   * @param  array   Properties
   * @param  boolean Skip validation?
   * @return model
   */
  final public static function create(array $params = array(), $skip = FALSE)
  {
    return static::build($params)->save($skip);
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
   * Retrieve table name
   *
   * @return array
   */
  final public static function table()
  {
    return static::$table ?: get_called_class();
  }



  /**#@+
   * @ignore
   */

  // relationships
  final private static function fetch_relation($key)
  {
    if ( ! empty(static::$relations[$key]))
    {
      return array_merge(array(
        'has_many' => FALSE,
        'from' => $key,
        'fk' => static::pk(),
        'on' => static::table() . '_id',
      ), static::$relations[$key]);
    }
  }

  // execute callbacks
  final protected static function callback($row, $method)
  {
    static::defined($method) && static::$method($row);
  }

  // make timestamps
  final protected static function stamp($fields, $new)
  {
    $current = date('Y-m-d H:i:s');

    if ($new && array_key_exists('created_at', $fields))
    {
      $fields['created_at'] = $current;
    }

    if (array_key_exists('modified_at', $fields))
    {
      $fields['modified_at'] = $current;
    }

    return $fields;
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/mvc/model.php */
