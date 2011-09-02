<?php

class model extends prototype
{

  private static $pk = NULL;
  private static $new = FALSE;

  public static $table = NULL;

  public static $attr_reader = array();
  public static $attr_writer = array();



  /*
  instanced object: row
  */

  final public function save()
  {
  }

  final public function update()
  {
  }

  final public function delete()
  {
  }

  final public function is_new()
  {
  }

  /*
  static object: builder
  */


  /*
  TODO: implement validation?

  is_valid?

  find_by_?
  count_by_?
  find all_by_?
  find_or_create_by_?

  all?
  count?
  exists?

  first?
  last?

  after_find!

  before_save!
  before_create!
  before_update!

  after_update!
  after_create!
  after_save!

  before_delete!
  after_delete!

  */



  /**#@+
   * @ignore
   */

  final private static function pk()
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

      if ( ! self::$pk)
      {
        die('missing primary_key');
      }
    }

    return self::$pk;
  }

  final private static function find_by_pk($id, $what = ALL, $type = AS_ARRAY)
  {

    $res = db::select(self::$table, $what, array(self::$pk => $id));

    return db::fetch($res, $type);

  }

  /**#@-*/
}
