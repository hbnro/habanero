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


sql::implement('connect', function()
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

sql::implement('version', function()
{
  $test = sql::connect()->getAttribute(PDO::ATTR_SERVER_VERSION);
  
  return $test['versionString'];
});

sql::implement('execute', function($sql)
{
  if (preg_match('/^\s*(UPDATE|DELETE)\s+/', $sql))
  {
    return sql::connect()->exec($sql);
  }
  return sql::connect()->query($sql);
});

sql::implement('escape', function($test)
{
  return substr(sql::connect()->quote($test), 1, -1);
});

sql::implement('error', function()
{
  $test = sql::connect()->errorInfo();
  
  return $test[0] == '00000' ? FALSE : $test[2];
});

sql::implement('result', function($res)
{
  return @array_shift(sql::fetch_assoc($res));
});

sql::implement('fetch_assoc', function($res)
{
  return $res ? $res->fetch(PDO::FETCH_ASSOC) : array();
});

sql::implement('fetch_object', function($res)
{
  return $res ? $res->fetch(PDO::FETCH_OBJ) : new stdClass;
});

sql::implement('count_rows', function($res)
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

sql::implement('affected_rows', function ($res)
{
  return $res ? (int) $res : FALSE;
});

sql::implement('last_id', function()
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
