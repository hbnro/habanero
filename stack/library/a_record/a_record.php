<?php

/**
 * MVC base model
 */

class a_record extends prototype
{

  /**#@+
   * @ignore
   */

  // model properties
  protected $props = array();

  // new record?
  protected $new_record = NULL;

  // valid record?
  protected $valid_record = NULL;

  // validation errors
  protected $error_list = array();

  // defaults
  protected static $defs = array(
                    'path' => APP_PATH,
                  );

  /**#@-*/


  // table name
  public static $table = NULL;

  // validation rules
  public static $validate = array();

  // scoped relationships
  public static $related_to = array();



  /**#@+
   * @ignore
   */

  // model constructor
  protected function __construct(array $params = array(), $method = NULL, $new = FALSE) {
    $this->new_record = (bool) $new;

    foreach (array_keys(static::columns()) as $key) {
      $this->props[$key] = ! empty($params[$key]) ? $params[$key] : NULL;
    }
    static::callback($this, $method);
  }

  // properties getter
  public function __get($key) {
    if ( ! array_key_exists($key, $this->props)) {
      if ($on = static::has_scope($key)) {
        return a_relation::match($this, $on);
      }
      raise(ln('ar.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    return $this->props[$key];
  }

  // properties setter
  public function __set($key, $value) {
    if ( ! array_key_exists($key, $this->props)) {
      raise(ln('ar.undefined_property', array('name' => $key, 'class' => get_called_class())));
    }
    $this->props[$key] = $value;
  }

  // scopes shortcut
  public function __call($method, $arguments) {
    $what = '';

    if ((substr($method, 0, 4) === 'all_') OR (substr($method, 0, 6) === 'count_')) {
      @list($what, $method) = explode('_', $method, 2);

      $test   = explode('_by_', $method);
      $method = array_shift($test);

      $test && $what .= '_by_' . array_shift($test);
    } elseif (preg_match('/^(first|last|create|build)_o[fn]_(.+?)$/', $method, $match)) {
      $test   = explode('_by_', $match[2]);
      $method = array_shift($test);
      $what   = $match[1];

      $test && $what .= '_by_' . array_shift($test);
    } elseif (preg_match('/^(.+?)_by_(.+?)$/', $method, $match)) {
      $method = $match[1];
      $what   = "find_by_$match[2]";
    }

    return call_user_func_array(array($this->$method, $what ?: 'all'), $arguments);
  }

  /**#@-*/



  /**
   * Retrieve record ID
   *
   * @return mixed
   */
  function id() {
    return $this->props[static::pk()];
  }


  /**
   * Is a fresh record?
   *
   * @return boolean
   */
  final public function is_new() {
    return $this->new_record;
  }


  /**
   * Is a valid record?
   *
   * @return boolean
   */
  final public function is_valid() {
    if ( ! static::$validate) {
      return TRUE;
    } elseif (is_null($this->valid_record)) {
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
  final public function errors() {
    return $this->error_list;
  }


  /**
   * Create row without saving
   *
   * @param  array Properties
   * @return model
   */
  final public static function build(array $params = array()) {
    $row = (object) $params;

    static::callback($row, 'before_create');

    return new static((array) $row, 'after_create', TRUE);
  }


  /**
   * Create row and save it
   *
   * @param  array   Properties
   * @param  boolean Skip validation?
   * @return model
   */
  final public static function create(array $params = array(), $skip = FALSE) {
    return static::build($params)->save($skip);
  }


  /**
   * There is a record?
   *
   * @return boolean
   */
  final public static function exists($params = array()) {
    return static::count($params) > 0;
  }


  /**
   * Retrieve table name
   *
   * @return array
   */
  final public static function table() {
    return static::$table ?: get_called_class();
  }



  /**#@+
   * @ignore
   */

  // super method fake!
  final protected static function super($method, $arguments) {
    if (in_array($method, array('first', 'last', 'all'))) {
      array_unshift($arguments, $method);

      return call_user_func_array(get_called_class() . '::find', $arguments);
    } elseif (preg_match('/^(build|create)_by_(.+)$/', $method, $match)) {
      return static::$match[1](static::merge($match[2], $arguments));
    }

    raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
  }

  // relationship scopes
  final protected static function has_scope($key) {
    if ( ! empty(static::$related_to[$key])) {
      return array_merge(array(
        'from' => $key,
        'fk' => static::pk(),
        'on' => static::table() . '_id',
      ), static::$related_to[$key]);
    }
  }

  // execute callbacks
  final protected static function callback($row, $method) {
    static::defined($method) && static::$method($row);
  }

  // make timestamps
  final protected static function stamp($fields, $new) {
    $current = date('Y-m-d H:i:s');

    if ($new && array_key_exists('created_at', $fields)) {
      $fields['created_at'] = $current;
    }

    if (array_key_exists('modified_at', $fields)) {
      $fields['modified_at'] = $current;
    }

    return $fields;
  }

  // merge fields
  final protected static function merge($as, array $are = array()) {
    if ( ! empty($are[0]) && is_assoc($are[0])) {
      return $are[0];
    }

    $as     = preg_split('/_and_/', $as);
    $length = min(sizeof($as), sizeof($are));

    $keys   = array_slice($as, 0, $length);
    $values = array_slice($are, 0, $length);

    return $keys && $values ? array_combine($keys, $values) : array();
  }

  /**#@-*/
}

/* EOF: ./stack/library/a_record/a_record.php */
