<?php

class model extends prototype
{

  private static $pk = NULL;
  private static $new = FALSE;

  public static $table = NULL;

  public static $attr_reader = array();
  public static $attr_writer = array();


  final public static function find($id, $what = ALL, $type = AS_ARRAY)
  {

    $res = db::select(self::$table, $what, array(self::$pk => $id));

    return db::fetch($res, $type);

  }


  /*
  TODO: implement

  find_by_?
  count_by_?
  find_or_create_by_?

  all?
  count?
  exists?

  first?
  last?

  callbacks*
  update*
  delete*
  insert*
  create*
  save*


  */


  final public static function init()
  {
    if ( ! self::$pk)
    {
      self::$table = self::$table ?: get_called_class();

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
