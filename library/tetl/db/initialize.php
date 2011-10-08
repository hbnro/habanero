<?php

/**
 * Database initialization
 */

call_user_func(function()
{
  define('ALL', '*');
  define('ASC', 'ASC');
  define('DESC', 'DESC');

  define('IS_NULL', NULL);
  define('NOT_NULL', "<> ''");

  define('AS_ARRAY', 'AS_ARRAY');
  define('AS_OBJECT', 'AS_OBJECT');

  i18n::load_path(__DIR__.DS.'locale', 'db');


  // default database adapter
  $dsn_string   = option('dsn');
  $dsn_default  = 'sqlite:' . APP_PATH.DS.'db.sqlite';

  $regex_string = '/^\w+:|scheme\s*=\s*\w+/';

  $dsn_string   = preg_match($regex_string, $dsn_string) ? $dsn_string : $dsn_default;


  $test = array('scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment');

  if (strrpos($dsn_string, ';'))
  {
    $set = array();

    $old = explode(';', $dsn_string);
    $old = array_map('trim', $old);

    foreach ($old as $one)
    {
      $new = explode('=', $one, 2);
      $key = trim(array_shift($new));

      $set[$key] = trim(join('', $new));
    }
  }
  else
  {
    $set = (array) @parse_url($dsn_string);
  }


  $parts = array();

  foreach ($test as $key)
  {
    $parts[$key] = ! empty($set[$key]) ? $set[$key] : '';
  }

  $scheme_file = $driver_file = '';


  if (class_exists('PDO') && option('pdo'))
  {
    if ( ! in_array($parts['scheme'], pdo_drivers()))
    {
      raise(ln('db.pdo_adapter_missing', array('name' => $parts['scheme'])));
    }
    $driver_file = __DIR__.DS.'drivers'.DS.'pdo'.EXT;
  }
  else
  {
    $driver_file = __DIR__.DS.'drivers'.DS.$parts['scheme'].EXT;
  }


  if ( ! is_file($driver_file))
  {
    raise(ln('db.database_driver_missing', array('adapter' => $parts['scheme'])));
  }


  $scheme_name = str_replace('mysqli', 'mysql', $parts['scheme']); // DRY
  $scheme_file = __DIR__.DS.'schemes'.DS.$scheme_name.EXT;

  if ( ! is_file($scheme_file))
  {
    raise(ln('db.database_scheme_missing', array('adapter' => $parts['scheme'])));
  }


  /**#@+
    * @ignore
    */

  require __DIR__.DS.'system'.EXT;
  require __DIR__.DS.'builder'.EXT;
  require __DIR__.DS.'schemata'.EXT;
  require __DIR__.DS.'migration'.EXT;

  require $driver_file;
  require $scheme_file;

  /**#@-*/


  define('DB_DSN', $dsn_string);
  define('DB_SCHEME', $parts['scheme']);
  define('DB_VERSION', sql::version(sql::connect($parts)));

  if (sql::defined('encoding'))
  {// TODO: sure dude?
    sql::encoding(preg_replace('/\W/', '', CHARSET));
  }
});

/* EOF: ./lib/tetl/db/initialize.php */
