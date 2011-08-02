<?php

/**
 * PostgreSQL database scheme
 */

sql::method('type', function()
{
  static $set = array(
            'CHARACTER' => 'string',
            'VARCHAR' => 'string',
            'CHAR' => 'string',
            'INT' => 'integer',
            'BIGINT' => 'integer',
            'SMALLINT' => 'integer',
            'BOOLEAN' => 'boolean',
            'DECIMAL' => 'numeric',
            'MONEY' => 'numeric',
            'ZONE' => 'numeric',
            'DOUBLE' => 'float',
            'REAL' => 'float',
            'BLOB' => 'binary',
          );
  
  return $set;
});

sql::method('raw', function()
{
  static $set = array(
            'primary_key' => 'SERIAL PRIMARY KEY',
            'string' => array('type' => 'CHARACTER varying', 'length' => 255),
          );
  
  return $set;
});

sql::method('begin', function()
{
  return sql::execute('BEGIN');
});

sql::method('commit', function()
{
  return sql::execute('COMMIT');
});

sql::method('rollback', function()
{
  return sql::execute('ROLLBACK');
});

sql::method('encoding', function($test)
{
  return sql::execute("SET NAMES '$test'");
});

sql::method('tables', function()
{
  $out = array();
  
  $sql = "SELECT tablename FROM pg_tables WHERE tablename "
       . "!~ '^pg_+' AND schemaname = 'public'";
  
  $old = sql::execute($sql);
  
  while ($row = sql::fetch_assoc($old))
  {
    $out []= $row['tablename'];
  }
  
  return $out;
});

sql::method('columns', function($test)
{
  $out = array();
  
  $sql = "SELECT DISTINCT "
       . "column_name, data_type AS t, character_maximum_length, column_default AS d,"
       . "is_nullable FROM information_schema.columns WHERE table_name='$test'";
  
  $old = sql::execute($sql);
  
  while ($row = sql::fetch_assoc($old))
  {
    if (preg_match('/^nextval\(.+$/', $row['d'], $id))
    {
      $row['d'] = NULL;
    }
    else
    {
      $row['d'] = trim(preg_replace('/::.+$/', '', $row['d']), "'");
    }
    
    $test     = explode(' ', $row['t']);
    $row['t'] = $test[0];

    $key  = array_shift($row);
    $type = array_shift($row);
    
    $out[$key] = array(
      'type' => $id ? 'PRIMARY_KEY' : strtoupper($type),
      'length' => (int) array_shift($row),
      'default' => trim(array_shift($row), "(')"),
      'not_null' => ! array_shift($row),
    );
  }
  
  return $out;
});

sql::method('limit', function($from, $to)
{
  return $to ? "\nLIMIT $to OFFSET $from" : "\nLIMIT $from\n";
});

sql::method('rename_table', function($from, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
});

sql::method('add_column', function($to, $name, $type)
{
  return sql::execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, db::field($type)));
});

sql::method('remove_column', function($from, $name)
{
  return sql::execute(sprintf('ALTER TABLE "%s" DROP COLUMN "%s" RESTRICT', $from, $name));
});

sql::method('rename_column', function($from, $name, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" RENAME COLUMN "%s" TO "%s"', $from, $name, $to));
});

sql::method('change_column', function($from, $name, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s', $from, $name, db::field($to)));
});

sql::method('add_index', function($to, $name, $column, $unique = FALSE)
{
  return sql::execute(sprintf('CREATE%sINDEX "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
});

sql::method('remove_index', function($name)
{
  return sql::execute(sprintf('DROP INDEX "%s"', $name));
});

sql::method('quotes', function($test)
{
  return '"' . $test . '"';
});

/* EOF: ./lib/db/schemes/pgsql.php */
