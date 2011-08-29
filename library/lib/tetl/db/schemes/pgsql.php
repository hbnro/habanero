<?php

/**
 * PostgreSQL database scheme
 */

sql::implement('type', function()
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

sql::implement('raw', function()
{
  static $set = array(
            'primary_key' => 'SERIAL PRIMARY KEY',
            'string' => array('type' => 'CHARACTER varying', 'length' => 255),
          );

  return $set;
});

sql::implement('begin', function()
{
  return sql::execute('BEGIN');
});

sql::implement('commit', function()
{
  return sql::execute('COMMIT');
});

sql::implement('rollback', function()
{
  return sql::execute('ROLLBACK');
});

sql::implement('encoding', function($test)
{
  return sql::execute("SET NAMES '$test'");
});

sql::implement('tables', function()
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

sql::implement('columns', function($test)
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

sql::implement('indexes', function($test)
{
  $out = array();

  $sql = "select pg_get_indexdef(indexrelid) AS sql from pg_index where indrelid = '$test'::regclass";
  $res = sql::execute($sql);

  while ($one = $res->fetchObject())
  {
    if (preg_match('/CREATE(\s+UNIQUE|)\s+INDEX\s+(\w+)\s+ON.+?\((.+?)\)/', $one->sql, $match))
    {
      $out[$match[2]] = array(
        'unique' => ! empty($match[1]),
        'column' => explode(',', preg_replace('/["\s]/', '', $match[3])),
      );
    }
  }

  return $out;
});

sql::implement('limit', function($from, $to)
{
  return $to ? "\nLIMIT $to OFFSET $from" : "\nLIMIT $from\n";
});

sql::implement('rename_table', function($from, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
});

sql::implement('add_column', function($to, $name, $type)
{
  return sql::execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, db::field($type)));
});

sql::implement('remove_column', function($from, $name)
{
  return sql::execute(sprintf('ALTER TABLE "%s" DROP COLUMN "%s" RESTRICT', $from, $name));
});

sql::implement('rename_column', function($from, $name, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" RENAME COLUMN "%s" TO "%s"', $from, $name, $to));
});

sql::implement('change_column', function($from, $name, $to)
{
  return sql::execute(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s', $from, $name, db::field($to)));
});

sql::implement('add_index', function($to, $name, $column, $unique = FALSE)
{
  return sql::execute(sprintf('CREATE%sINDEX "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
});

sql::implement('remove_index', function($name)
{
  return sql::execute(sprintf('DROP INDEX "%s"', $name));
});

sql::implement('quotes', function($test)
{
  return '"' . $test . '"';
});

/* EOF: ./lib/tetl/db/schemes/pgsql.php */
