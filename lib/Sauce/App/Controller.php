<?php

namespace Sauce\App;

class Controller
{

  public static $status = 200;
  public static $headers = array();

  public static $view = array();
  public static $head = array();

  public static $title = '';
  public static $layout = 'default';

  public static $responds_to = array('html', 'json');


  public function __get($key)
  {
    if ( ! isset(static::$view[$key])) {
      $klass = get_called_class();
      throw new \Exception("Undefined local '$key' on '$klass' controller");
    }
    return static::$view[$key];
  }

  public function __set($key, $value)
  {
    static::$view[$key] = $value;
  }



  public static function as_json($data, array $params = array())
  {
    return array(200, array('Content-Type' => 'application/json'), json_encode($data));
  }

}
