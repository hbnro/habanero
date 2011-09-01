<?php

class model extends prototype
{

  private static $pk = NULL;

  private static $name = NULL;

  public static $table = NULL;


  final public static function find($id, $what = ALL, $type = AS_ARRAY)
  {

    $res = db::select(self::$name, $what, array(self::$pk => $id));

    return db::fetch($res, $type);

  }


  final public static function init()
  {
    if ( ! self::$name)
    {
      self::$name = ! empty(self::$table) ? self::$table : get_called_class();

      foreach (db::columns(self::$name) as $key => $one)
      {
        if ($one['type'] === 'primary_key')
        {
          self::$pk = $key;

          break;
        }
      }
    }
  }

}
