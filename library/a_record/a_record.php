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

  // changed properties
  protected $changed = array();

  // new record?
  protected $new_record = NULL;

  // valid record?
  protected $valid_record = NULL;

  // validation errors
  protected $error_list = array();

  // mixin caching by class
  protected static $with = array();

  // internal caching by class
  protected static $cache = array();

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
  protected function __construct(array $params = array(), $method = NULL, $new = FALSE, array $el = array()) {
    $this->new_record = (bool) $new;

    foreach (array_keys(static::columns()) as $key) { // TODO: this is fine?
      $this->props[$key] = isset($params[$key]) ? $params[$key] : NULL;
    }

    if ( ! empty($el['set']['data'])) {
      $this->props[$el['set']['as']] = $el['set']['data'][$this->props[$el['set']['fk']]];
    }

    static::callback($this, $method);
  }

  // properties getter
  public function __get($key) {
    if ( ! in_array($key, static::$with)) {
      if ( ! array_key_exists($key, $this->columns())) {
        if ($on = static::has_relation($key)) {
          return a_relation::match($this, $on);
        }
        raise(ln('ar.undefined_property', array('name' => $key, 'class' => get_called_class())));
      }
    }
    return $this->props[$key];
  }

  // properties setter
  public function __set($key, $value) {
    if ( ! in_array($key, static::$with)) {
      if ( ! array_key_exists($key, $this->columns())) {
        raise(ln('ar.undefined_property', array('name' => $key, 'class' => get_called_class())));
      }
      ! in_array($key, $this->changed) && $this->changed []= $key;
    }
    $this->props[$key] = $value;
  }

  // scopes shortcut
  public function __call($method, $arguments) {
    $what  = '';
    $class = get_called_class();

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

  // plain fields
  public function __toString() {
    return dump($this->fields());
  }

  /**#@-*/



  /**
   * Retrieve record ID
   *
   * @return mixed
   */
  public function id() {
    return $this->props[static::pk()];
  }


  /**
   * Retrieve all columns
   *
   * @return array
   */
  final public function fields() {
    return $this->props;
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
   * The record has changed?
   *
   * @return boolean
   */
  final public function has_changed() {
    return ! empty($this->changed);
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
   * Update fields
   *
   * @param  array Values
   * @return self
   */
  final public function update(array $props = array()) {
    if ( ! empty($props)) {
      foreach ($props as $key => $value) {
        $this->$key = $value;
      }
    }

    $this->has_changed() && $this->save();

    return $this;
  }


  /**
   * Delete row
   *
   * @return mixed
   */
  final public function delete() {
    static::delete_all(array(
      static::pk() => $this->props[static::pk()],
    ));

    return $this;
  }


  /**
   * Filter columns
   *
   * @param  mixed Fields|...
   * @return model
   */
  final public static function get() {
    return a_query::fetch(get_called_class(), 'select', func_get_args());
  }


  /**
   * Filter values
   *
   * @param  array Fields
   * @return model
   */
  final public static function where(array $params) {
    return a_query::fetch(get_called_class(), 'where', $params);
  }


  /**
   * Find rows
   *
   * @param  mixed ID|Properties|...
   * @return mixed
   */
  final public static function find() {
    if ( ! func_num_args()) {
      return a_chain::fetch(get_called_class());
    }


    $args    = func_get_args();

    $wich    = array_shift($args);
    $params  = array_pop($args);

    $where   =
    $options = array();

    if (is_assoc($params)) {
      $options = (array) $params;
    } else {
      $args []= $params;
    }

    if ( ! empty($options['where'])) {
      $where = (array) $options['where'];
      unset($options['where']);
    }

    $what = array();

    if ( ! empty($options['select'])) {
      $what = (array) $options['select'];
      unset($options['select']);
    }

    return static::finder($wich, $what, $where, $options);
  }


  /**
   * Iteration blocks
   *
   * @param  mixed Options|Function callback
   * @param  mixed Function callback
   * @return void
   */
  final public static function each($params = array(), Closure $lambda = NULL) {
    $start = ticks();

    if (is_closure($params)) {
      $lambda = $params;
      $params = array();
    } elseif ( ! empty($params['block'])) {
      $lambda = $params['block'];
      unset($params['block']);
    }

    $get   = ! empty($params['select']) ? $params['select'] : array();
    $where = ! empty($params['where']) ? (array) $params['where'] : array();

    return static::block($get, $where, $params, $lambda);
  }


  /**
   * Eager load
   *
   * @param  mixed Options|Model
   * @return model
   */
  final public static function with($from) {
    $params = array();

    if (is_assoc($from)) {
      $params = $from;
    } else {
      $params['from'] = $from;
    }

    // TODO: make it multiple?
    $params = array_merge(array(
      'as' => $params['from'],
      'pk' => $params['from']::pk(),
      'fk' => $params['from'] . '_id',
      'with' => get_called_class(),
      #'select' => '*',
    ), $params);

    ! in_array($params['as'], static::$with) && static::$with []= $params['as'];

    return a_eager::on($params);
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
    } elseif (preg_match('/^(?:find_)?(all|first|last)_by_(.+)$/', $method, $match)) {
      return static::find($match[1], array(
        'where' => static::merge($match[2], $arguments),
      ));
    } elseif (preg_match('/^each_by_(.+)$/', $method, $match)) {
      return static::each(array(
        'block' => array_pop($arguments),
        'where' => static::merge($match[1], $arguments),
      ));
    }

    raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
  }

  // relationships
  final protected static function has_relation($key) {
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
  final protected static function stamp($changed, $fields, $new) {
    $props   = static::columns();
    $current = date('Y-m-d H:i:s');

    if ( ! $new) {
      foreach ($fields as $key => $val) {
        if ( ! in_array($key, $changed)) {
          unset($fields[$key]);
        }
      }
    } elseif (array_key_exists('created_at', $props)) {
      $fields['created_at'] = $current;
    }

    if (array_key_exists('modified_at', $props)) {
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

/* EOF: ./library/a_record/a_record.php */
