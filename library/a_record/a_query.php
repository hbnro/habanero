<?php

/**
 * Query scopes
 */

class a_query
{

  /**#@+
   * @ignore
   */

  // defaults
  private $defs =  array();

  // model class
  private $model = NULL;

  // avoid constructor
  private function __construct() {
  }

  // dynamic calls for retrieving
  public function __call($method, $arguments) {
    switch ($method) {
      case 'val';
        $out  = array();
        $data = $this->first()->fields();

        foreach ($arguments as $one) {
          ! empty($data[$one]) && $out []= $data[$one];
        }
        return sizeof($out) > 1 ? $out : end($out);
      break;
      case 'count'; case 'first'; case 'last'; case 'all';
        return call_user_func("$this->model::$method", $this->defs);
      break;
      case 'where'; case 'select'; case 'order'; case 'group'; case 'limit';
        $this->defs[$method] = sizeof($arguments) > 1 ? $arguments : array_shift($arguments);
        return $this;
      break;
      default;
        raise(ln('method_missing', array('class' => $this->model, 'name' => $method)));
      break;
    }
  }

  /**#@-*/



  /**
   * Query builder
   *
   * @param  string Model name
   * @param  string Option type
   * @param  array  Arguments
   * @return model
   */
  final public static function fetch($on, $what, array $params) {
    $chain = new static;
    $chain->model = $on;

    return $chain->$what($params);
  }

}

/* EOF: ./library/a_record/a_query.php */
