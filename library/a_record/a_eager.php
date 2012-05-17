<?php

/**
 * Eager loading
 */

class a_eager
{

  /**#@+
   * @ignore
   */

  // with options
  private $props = array();

  // avoid constructor
  private function __construct() {
  }

  // dynamic methods calling
  public function __call($method, $arguments) {
    if ( ! in_array($method, array('all', 'find', 'first', 'last', 'each'))) {
      return call_user_func_array("{$this->props['with']}::$method", $arguments);
    }


    $key    = FALSE;
    $params = array();

    foreach ($arguments as $i => $one) {
      if (is_assoc($one)) {
        $params = $one;
        $key = $i;
        break;
      }
    }

    $this->props['data'] = static::fetch($params);
    $params['set'] = $this->props;

    if (is_false($key)) {
      $method == 'each' ? array_unshift($arguments, $params) : $arguments []= $params;
    } else {
      $arguments[$key] = $params;
    }

    extract($this->props);
    return $with::apply($method, $arguments);
  }

  /**#@-*/


  /**
   * Eager setter
   *
   * @param  object Model
   * @param  array  Options
   * @return model
   */
  final public static function extend($obj, array $params) {
    if (is_object($obj) && ! empty($params['set'])) {
      extract($params['set']);
      $obj->$as = $data[$obj->$fk];
    }
    return $obj;
  }


  /**
   * Eager builder
   *
   * @param  array Options
   * @return model
   */
  final public static function on($params) {
    $load = new static;
    $load->props = $params;

    return $load;
  }



  /**#@+
   * @ignore
   */

  // result set fetching
  final private function fetch($params) {
    $out =
    $tmp = array();
    $get = $params;

    extract($this->props);

    $get['select'] = array($fk);

    $with::each($get, function ($row)
      use(&$out, $fk) {
      $out []= $row->$fk;
    });

    $out = array_unique($out);
    $set = compact('select');

    $set['where'][$pk] = $out;

    $from::each($set, function ($row)
      use(&$tmp) {
      $tmp[$row->id()] = $row;
    });

    return $tmp;
  }

  /**#@-*/
}

/* EOF: ./library/a_record/a_eager.php */
