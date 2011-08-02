<?php

/**
 * PDO database adapter
 */

if ( ! class_exists('PDO'))
{
  raise(ln('extension_missing', array('name' => 'PDO')));
}

/**#@+
 * @ignore
 */
define('RANDOM', 'RANDOM()');
define('DB_DRIVER', 'PDO');
/**#@-*/


sql::method('connect', function()
{
  static $object = NULL;
  
  
  if (is_null($object))
  {
    extract(func_get_arg(0));
    
    switch ($scheme)
    {
      case 'sqlite';
        $dsn_string = 'sqlite:' . str_replace('\\', '/', $host . $path);
      break;
      default;
        $dsn_string = "$scheme:host=$host;";
        
        if ($port > 0)
        {
          $dsn_string .= "port=$port;";
        }
        
        $database    = trim($path, '/');
        $dsn_string .= "dbname=$database;";
      break;
    }
    
    parse_str($query, $query);
    
    $object = new PDO($dsn_string, $user, $pass, $query);
  }
  
  return $object;
});

sql::method('version', function()
{
  $test = sql::connect()->getAttribute(PDO::ATTR_SERVER_VERSION);
  
  return $test['versionString'];
});

sql::method('execute', function($sql)
{
  if (preg_match('/^\s*(UPDATE|DELETE)\s+/', $sql))
  {
    return sql::connect()->exec($sql);
  }
  return sql::connect()->query($sql);
});

sql::method('escape', function($test)
{
  return substr(sql::connect()->quote($test), 1, -1);
});

sql::method('error', function()
{
  $test = sql::connect()->errorInfo();
  
  return $test[0] == '00000' ? FALSE : $test[2];
});

sql::method('result', function($res)
{
  return @array_shift(sql::fetch_assoc($res));
});

sql::method('fetch_assoc', function($res)
{
  return $res ? $res->fetch(PDO::FETCH_ASSOC) : array();
});

sql::method('fetch_object', function($res)
{
  return $res ? $res->fetch(PDO::FETCH_OBJ) : new stdClass;
});

sql::method('count_rows', function($res)
{
  if ( ! $res)
  {
    return FALSE;
  }
  
  $out = $res->rowCount();
  
  if (preg_match('/^\s*SELECT.+?FROM(.+?)$/is', $res->queryString, $match))
  {// http://www.php.net/manual/es/pdostatement.rowcount.php
    $tmp = sql::execute("SELECT COUNT(*) FROM $match[1]");
    $out = sql::result($tmp);
  }
  
  return (int) $out;
});

sql::method('affected_rows', function ($res)
{
  return $res ? (int) $res : FALSE;
});

sql::method('last_id', function()
{
  if (DB_SCHEME == 'pgsql')
  {// http://www.php.net/manual/en/pdo.lastinsertid.php#86178
    if (preg_match('/^\s*INSERT\s+INTO\s+(")?([^"]+)(?=\s|")/is', sql::connect()->last, $match))
    {
      $sql = "SELECT currval('{$match[2]}_id_seq') AS last_value";
      return (int) sql::result(sql::execute($sql));
    }
  }
  return sql::connect()->lastInsertId();
});

/* EOF: ./lib/db/drivers/pdo.php */
