<?php

namespace Sauce;

class Config
{

  private static $bag = array(
                    // defaults
                    'cache' => 'php',
                    'expires' => 300,
                    'rewrite' => FALSE,
                    'server_name' => '',
                    'subdomain' => '',
                    'domain' => '',
                    // prod + assets
                    'base_url' => '/',
                    'base_index' => 'index.php',
                  );



  public static function add(array $set)
  {
    foreach ($set as $key => $val) {
      static::$bag[$key] = $val;
    }
  }

  public static function set($key, $value = NULL)
  {
    static::$bag[$key] = $value;
  }

  public static function get($key, $default = FALSE)
  {
    return isset(static::$bag[$key]) ? static::$bag[$key] : $default;
  }

  public static function all()
  {
    return static::$bag;
  }

}
