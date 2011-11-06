<?php

/**
 * MySQL-core database scheme
 */

sql::implement('type', function () {
  static $set = array(
            'VARCHAR' => 'string',
            'LONGTEXT' => 'string',
            'TINYTEXT' => 'string',
            'INT' => 'integer',
            'TINYINT' => 'integer',
            'SMALLINT' => 'integer',
            'MEDIUM' => 'integer',
            'BIGINT' => 'integer',
            'NUMERIC' => 'numeric',
            'DECIMAL' => 'numeric',
            'YEAR' => 'numeric',
            'DOUBLE' => 'float',
            'BOOL' => 'boolean',
            'BINARY' => 'binary',
            'VARBINARY' => 'binary',
            'LONGBLOB' => 'binary',
            'MEDIUMBLOB' => 'binary',
            'TINYBLOB' => 'binary',
            'BLOB' => 'binary',
          );

  return $set;
});

sql::implement('raw', function () {
  static $set = array(
            'primary_key' => 'INT(11) DEFAULT NULL auto_increment PRIMARY KEY',
            'string' => array('type' => 'VARCHAR', 'length' => 255),
            'integer' => array('type' => 'INT', 'length' => 11),
            'timestamp' => array('type' => 'DATETIME'),
            'numeric' => array('type' => 'VARCHAR', 'length' => 16),
            'boolean' => array('type' => 'TINYINT', 'length' => 1),
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

sql::implement('encoding', function ($test) {
  return sql::execute("SET NAMES '$test'");
});

sql::implement('tables', function () {
  $out = array();
  $old = sql::execute('SHOW TABLES');

  while ($row = sql::fetch_assoc($old)) {
    $out []= array_pop($row);
  }

  return $out;
});

sql::implement('columns', function ($test) {
  $out = array();
  $old = sql::execute("DESCRIBE `$test`");

  while ($row = sql::fetch_assoc($old)) {
    preg_match('/^(\w+)(?:\((\d+)\))?.*?$/', strtoupper($row['Type']), $match);

    $out[$row['Field']] = array(
        'type' => $row['Extra'] == 'auto_increment' ? 'PRIMARY_KEY' : $match[1],
        'length' => ! empty($match[2]) ? (int) $match[2] : 0,
        'default' =>  $row['Default'],
        'not_null' => $row['Null'] <> 'YES',
    );
  }

  return $out;
});

sql::implement('indexes', function ($test) {
  $out = array();

  $res = sql::execute("SHOW INDEXES FROM `$test`");

  while ($one = sql::fetch_object($res)) {
    if ($one->Key_name <> 'PRIMARY') {
      if ( ! isset($out[$one->Key_name])) {
        $out[$one->Key_name] = array(
          'unique' => ! $one->Non_unique,
          'column' => array(),
        );
      }

      $out[$one->Key_name]['column'] []= $one->Column_name;
    }
  }

  return $out;
});

sql::implement('limit', function ($from, $to) {
  return "\nLIMIT {$from}" . ( ! empty($to) ? ",$to\n" : "\n");
});

sql::implement('rename_table', function ($from, $to) {
  return sql::execute(sprintf('RENAME TABLE `%s` TO `%s`', $from, $to));
});

sql::implement('add_column', function ($to, $name, $type) {
  return sql::execute(sprintf('ALTER TABLE `%s` ADD `%s` %s', $to, $name, db::field($type)));
});

sql::implement('remove_column', function ($from, $name) {
  return sql::execute(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $from, $name));
});

sql::implement('rename_column', function ($from, $name, $to) {
  static $map = array(
            '/^VARCHAR$/' => 'VARCHAR(255)',
            '/^INT(?:EGER)$/' => 'INT(11)',
          );


  $set = db::columns($from);

  $test = db::field($set[$name]['type'], $set[$name]['length']);
  $type = substr($test, 0, strpos($test, ' '));

  foreach ($map as $key => $val) {
    $type = preg_replace($key, $val, $type);
  }

  return sql::execute(sprintf('ALTER TABLE `%s` CHANGE `%s` `%s` %s', $from, $name, $to, $type));
});

sql::implement('change_column', function ($from, $name, $to) {
  return sql::execute(sprintf('ALTER TABLE `%s` MODIFY `%s` %s', $from, $name, db::field($to)));
});

sql::implement('add_index', function ($to, $name, $column, $unique = FALSE) {
  $query  = sprintf('CREATE%sINDEX `%s` ON `%s` (`%s`)', $unique ? ' UNIQUE ' : ' ', $name, $to, join('`, `', $column));

  return sql::execute($query);
});

sql::implement('remove_index', function ($name, $table) {
  return sql::execute(sprintf('DROP INDEX `%s` ON `%s`', $name, $table));
});

sql::implement('quotes', function ($test) {
  return "`$test`";
});

/* EOF: ./stack/library/db/schemes/mysql.php */
