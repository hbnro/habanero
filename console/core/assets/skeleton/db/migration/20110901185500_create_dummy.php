<?php

final class create_dummy extends migration
{
  final public static function up()
  {
    create_table('dummy', array('id' => 'primary_key'));

    add_column('dummy', 'title', 'string');
    add_column('dummy', 'body', 'text');

    // automatically add created_at, updated_at
  }

  final public static function change()
  {
    remove_column('dummy', 'body');
  }

  final public static function down()
  {
    drop_table('dummy');
  }
}
