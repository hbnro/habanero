<?php

/**
 * SQLite3 database scheme
 */

sql::implement('type', function () {
  static $set = array(
            'CHARACTER' => 'string',
            'NVARCHAR' => 'string',
            'VARCHAR' => 'string',
            'NCHAR' => 'string',
            'CLOB' => 'string',
            'INT' => 'integer',
            'TINYINT' => 'integer',
            'SMALLINT' => 'integer',
            'MEDIUMINT' => 'integer',
            'BIGINT' => 'integer',
            'INT2' => 'integer',
            'INT8' => 'integer',
            'REAL' => 'float',
            'DOUBLE' => 'float',
            'DECIMAL' => 'numeric',
            'BLOB' => 'binary',
          );

  return $set;
});

sql::implement('raw', function () {
  static $set = array(
            'primary_key' => 'INTEGER NOT NULL PRIMARY KEY',
            'string' => array('type' => 'VARCHAR', 'length' => 255),
            'timestamp' => array('type' => 'DATETIME'),
            'binary' => array('type' => 'BLOB'),
          );

  return $set;
});

sql::implement('begin', function () {
  return sql::execute('BEGIN TRANSACTION');
});

sql::implement('commit', function () {
  return sql::execute('COMMIT TRANSACTION');
});

sql::implement('rollback', function () {
  return sql::execute('ROLLBACK TRANSACTION');
});

sql::implement('tables', function () {
  $out = array();
  $sql = "SELECT name FROM sqlite_master WHERE type = 'table'";
  $old = sql::execute($sql);

  while ($row = sql::fetch_assoc($old)) {
    $out []= $row['name'];
  }

  return $out;
});

sql::implement('columns', function ($test) {
  $out = array();
  $sql = "PRAGMA table_info('$test')";
  $old = sql::execute($sql);

  while ($row = sql::fetch_assoc($old)) {
    preg_match('/^(\w+)(?:\((\d+)\))?.*?$/', strtoupper($row['type']), $match);

    $out[$row['name']] = array(
        'type' => $row['pk'] > 0 ? 'PRIMARY_KEY' : $match[1],
        'length' => ! empty($match[2]) ? (int) $match[2] : 0,
        'default' => trim($row['dflt_value'], "(')"),
        'not_null' => $row['notnull'] > 0,
    );
  }

  return $out;
});

sql::implement('indexes', function ($test) {
  $res = sql::execute("SELECT name,sql FROM sqlite_master WHERE type='index' AND tbl_name='$test'");

  $out = array();

  while ($one = sql::fetch_object($res)) {
    if (preg_match('/\((.+?)\)/', $one->sql, $match)) {
      $col = explode(',', preg_replace('/["\s]/', '', $match[1]));
      $out[$one->name] = array(
        'unique' => strpos($one->sql, 'UNIQUE ') !== FALSE,
        'column' => $col,
      );
    }
  }

  return $out;
});

sql::implement('limit', function ($from, $to) {
  return "\nLIMIT $from" . ($to ? ",$to\n" : "\n");
});

sql::implement('rename_table', function ($from, $to) {
  return sql::execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
});

sql::implement('add_column', function ($to, $name, $type) {
  return sql::execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, db::field($type)));
});

sql::implement('remove_column', function ($from, $name) {
  return sql::change_column($from, $name, NULL);
});

sql::implement('rename_column', function ($from, $name, $to) {
  $set = sql::columns($from);
  $old = $set[$name];

  sql::add_column($from, $to, array(
    $old['type'],
    $old['length'],
    $old['default'],
  ));

  sql::execute(sprintf('UPDATE "%s" SET "%s" = "%s"', $from, $to, $name));

  return sql::remove_column($from, $name);
});

sql::implement('change_column', function ($from, $name, $to) {
  $new = array();

  foreach (sql::columns($from) as $key => $val) {
    if ($key === $name) {
      if (is_array($to)) {
        $new[$key] = $to;
      }
      continue;
    }
    $new[$key] = array(
      $val['type'],
      $val['length'],
      $val['default'],
    );
  }

  sql::begin();

  sql::execute(db::build($old = uniqid($from), $new));
  sql::execute(sprintf('INSERT INTO "%s" SELECT "%s" FROM "%s"', $old, join('", "', array_keys($new)), $from));
  sql::execute(sprintf('DROP TABLE "%s"', $from));

  sql::rename_table($old, $from);

  return sql::commit();
});

sql::implement('add_index', function ($to, $name, $column, $unique = FALSE) {
  return sql::execute(sprintf('CREATE%sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
});

sql::implement('remove_index', function ($name) {
  return sql::execute(sprintf('DROP INDEX IF EXISTS "%s"', $name));
});

sql::implement('quotes', function ($test) {
  return '"' . $test . '"';
});

/* EOF: ./library/tetl/db/schemes/sqlite.php */
