<?php

/**
 * Relationship scopes
 */

class a_relation
{

  /**#@+
   * @ignore
   */

  // defaults
  private $defs =  array();

  // avoid constructor
  private function __construct() {
  }

  // delegate dynamic calls
  public function __call($method, $arguments) {
    if ( ! empty($arguments[0]) && is_array($arguments[0])) {
      $arguments[0][$this->defs['scope']['on']] = $this->defs['model']->id();
    } else {
      $test = explode('_by_', $method, 2);
      $oper = strpos($method, '_by_') ? 'and' : 'by';

      $method = "{$test[0]}_by_{$this->defs['scope']['on']}";

      ! empty($test[1]) && $method .= "_{$oper}_$test[1]";

      array_unshift($arguments, $this->defs['model']->id());
    }

    return call_user_func_array("{$this->defs['scope']['from']}::$method", $arguments);
  }

  /**#@-*/



  /**
   * Magic dates
   *
   * @param  model Instance
   * @param  array Options hash
   * @return model
   */
  final public static function match($model, array $params) {
    $prop = new static;

    $prop->defs['model'] = $model;
    $prop->defs['scope'] = $params;

    return $prop;
  }

}

/* EOF: ./stack/library/a_record/a_relation.php */
