<?php

/**
 * Relationship scopes
 */

class relation
{

  /**#@+
   * @ignore
   */

  // defaults
  private $defs =  array();

  // relation constructor
  public function __construct($scope, $model) {
    $this->defs = compact('scope', 'model');
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

}

/* EOF: ./stack/library/app/base/model/relation.php */
