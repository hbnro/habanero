<?php

/**
 * Chained scopes
 */

class a_chain
{

  /**#@+
   * @ignore
   */

  // model class
  private $model = NULL;

  // chained props
  private $scopes =  array();

  // avoid constructor
  private function __construct() {
  }

  // dynamic chaining
  public function __get($key) {
    if ($this->is_scope($this->model, $key)) {
      ! in_array($key, $this->scopes) && $this->scopes []= $key;
      return $this;
    }

    raise(ln('ar.undefined_property', array('name' => $key, 'class' => $this->model)));
  }

  // fetch with chained params
  public function __call($method, $arguments) {
    static $get = array('first', 'last', 'count', 'all');


    $model = $this->model;

    if (in_array($method, $get)) {
      $arguments []= $this->params();
      return $model::apply($method, $arguments);
    } elseif ($this->is_scope($model, $method)) {
      ! in_array($method, $this->scopes) && $this->scopes []= $method;
      return $model::all($this->params());
    }

    return call_user_func_array("$model::$method", $arguments);
  }

  /**#@-*/



  /**
   * Chain builder
   *
   * @param  string  Model name
   * @param  string  Property
   * @param  array   Arguments
   * @param  boolean Fetch?
   * @return mixed
   */
  final public static function fetch($model, $property = NULL, array $arguments = array(), $fetch = FALSE) {
    $obj = new static;
    $obj->model = $model;

    if ( ! $fetch) {
      $property && $obj->scopes []= $property;
      return $obj;
    }
    return call_user_func_array(array($obj, $property), $arguments);
  }


  /**#@+
   * @ignore
   */

  // chained scopes
  final protected function is_scope($model, $property) {
    return ! empty($model::$$property) && is_array($model::$$property);
  }

  // get options
  final protected function params() {
    $out   = array();
    $model = $this->model;

    foreach ($this->scopes as $one) {
      $out = array_merge_recursive($out, $model::$$one);
    }
    return $out;
  }

  /**#@-*/

}

/* EOF: ./library/a_record/a_chain.php */
