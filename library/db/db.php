<?php

/**
 * Database query related functions
 */

class db extends prototype
{

  private static $multi = array();

  private static $cached = array();

  final public static function connect($dsn_string) {
    if ( ! @array_key_exists($dsn_string, static::$multi)) {
      $dsn_default  = 'sqlite::memory:';
      $regex_string = '/^\w+:|scheme\s*=\s*\w+/';

      $dsn_string   = preg_match($regex_string, $dsn_string) ? $dsn_string : $dsn_default;
      $test         = explode('|', 'scheme|host|port|user|pass|path|query|fragment');

      if (strrpos($dsn_string, ';')) {
        $set = array();

        $old = explode(';', $dsn_string);
        $old = array_map('trim', $old);

        foreach ($old as $one) {
          $new = explode('=', $one, 2);
          $key = trim(array_shift($new));

          $set[$key] = trim(join('', $new));
        }
      } else {
        $set = (array) @parse_url($dsn_string);
      }


      $parts = array();

      foreach ($test as $key) {
        $parts[$key] = ! empty($set[$key]) ? $set[$key] : '';
      }

      $scheme_file = $driver_file = '';

      parse_str($parts['query'], $query);

      if (array_key_exists('pdo', $query)) {
        if ( ! in_array($parts['scheme'], pdo_drivers())) {
          raise(ln('db.pdo_adapter_missing', array('name' => $parts['scheme'])));
        }
        $driver_file = __DIR__.DS.'drivers'.DS.'pdo'.EXT;
      } else {
        $driver_file = __DIR__.DS.'drivers'.DS.$parts['scheme'].EXT;
      }


      if ( ! is_file($driver_file)) {
        raise(ln('db.database_driver_missing', array('adapter' => $parts['scheme'])));
      }


      $scheme_name  = str_replace('mysqli', 'mysql', $parts['scheme']); // DRY
      $scheme_file  = __DIR__.DS.'schemes'.DS.$scheme_name.EXT;
      $driver_class = extn($driver_file, TRUE) . '_driver';

      if ( ! is_file($scheme_file)) {
        raise(ln('db.database_scheme_missing', array('adapter' => $parts['scheme'])));
      }


      /**#@+
        * @ignore
        */
      ! is_loaded($scheme_file) && require $scheme_file;
      ! is_loaded($driver_file) && require $driver_file;
      /**#@-*/

      static::$multi[$dsn_string] = $driver_class::factory($parts);

      logger::debug("Connect: $dsn_string");
    }
    return static::$multi[$dsn_string];
  }
}


// primary connection
db::implement('missing', function ($method, array $arguments) {
  return call_user_func_array(array(db::connect(option('database.default')), $method), $arguments);
});

/* EOF: ./library/db/db.php */
