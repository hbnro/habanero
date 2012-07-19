<?php

/**
 * MySQL-core database scheme
 */

/**#@+
 * @ignore
 */

class mysql_scheme extends sql_scheme
{
  public $random = 'RAND()';

  public $types = array(
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

  public $raw = array(
              'primary_key' => 'INT(11) DEFAULT NULL auto_increment PRIMARY KEY',
              'string' => array('type' => 'VARCHAR', 'length' => 255),
              'integer' => array('type' => 'INT', 'length' => 11),
              'timestamp' => 'DATETIME',
              'numeric' => array('type' => 'VARCHAR', 'length' => 16),
              'boolean' => array('type' => 'TINYINT', 'length' => 1),
              'binary' => 'BLOB',
            );

  final public function rename_table($from, $to) {
    return $this->execute(sprintf('RENAME TABLE `%s` TO `%s`', $from, $to));
  }

  final public function add_column($to, $name, $type) {
    return $this->execute(sprintf('ALTER TABLE `%s` ADD `%s` %s', $to, $name, $this->a_field($type)));
  }

  final public function remove_column($from, $name) {
    return $this->execute(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $from, $name));
  }

  final public function rename_column($from, $name, $to) {
    static $map = array(
              '/^VARCHAR$/' => 'VARCHAR(255)',
              '/^INT(?:EGER)$/' => 'INT(11)',
            );


    $set  = $this->columns($from);
    $test = $this->a_field($set[$name]['type'], $set[$name]['length']);
    $type = substr($test, 0, strpos($test, ' '));

    foreach ($map as $key => $val) {
      $type = preg_replace($key, $val, $type);
    }

    return $this->execute(sprintf('ALTER TABLE `%s` CHANGE `%s` `%s` %s', $from, $name, $to, $type));
  }

  final public function change_column($from, $name, $to) {
    return $this->execute(sprintf('ALTER TABLE `%s` MODIFY `%s` %s', $from, $name, $this->a_field($to)));
  }

  final public function add_index($to, $name, $column, $unique = FALSE) {
    $query  = sprintf('CREATE%sINDEX `%s` ON `%s` (`%s`)', $unique ? ' UNIQUE ' : ' ', $name, $to, join('`, `', $column));
    return $this->execute($query);
  }

  final public function remove_index($name, $table) {
    return $this->execute(sprintf('DROP INDEX `%s` ON `%s`', $name, $table));
  }

  final public function begin_transaction() {
    return $this->execute('BEGIN TRANSACTION');
  }

  final public function commit_transaction() {
    return $this->execute('COMMIT TRANSACTION');
  }

  final public function rollback_transaction() {
    return $this->execute('ROLLBACK TRANSACTION');
  }

  final public function set_encoding() {
    return $this->execute("SET NAMES 'UTF-8'");
  }

  final public function fetch_tables() {
    $out = array();
    $old = $this->execute('SHOW TABLES');

    while ($row = $this->fetch_assoc($old)) {
      $out []= array_pop($row);
    }

    return $out;
  }

  final public function fetch_columns($test) {
    $out = array();
    $old = $this->execute("DESCRIBE `$test`");

    while ($row = $this->fetch_assoc($old)) {
      preg_match('/^(\w+)(?:\((\d+)\))?.*?$/', strtoupper($row['Type']), $match);

      $out[$row['Field']] = array(
          'type' => $row['Extra'] == 'auto_increment' ? 'PRIMARY_KEY' : $match[1],
          'length' => ! empty($match[2]) ? (int) $match[2] : 0,
          'default' =>  $row['Default'],
          'not_null' => $row['Null'] <> 'YES',
      );
    }

    return $out;
  }

  final public function fetch_indexes($test) {
    $out = array();

    $res = $this->execute("SHOW INDEXES FROM `$test`");

    while ($one = $this->fetch_object($res)) {
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
  }

  final public function ensure_limit($from, $to) {
    return "\nLIMIT {$from}" . ( ! empty($to) ? ",$to\n" : "\n");
  }

  final public function quote_string($test) {
    return "`$test`";
  }

  final public function ensure_type($test) {
    if (is_bool($test)) {
      $test = $test ? 1 : 0;
    } elseif (is_null($test)) {
      $test = 'NULL';
    }
    return $test;
  }
}

/**#@-*/

/* EOF: ./library/db/schemes/mysql.php */
