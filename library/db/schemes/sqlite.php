<?php

/**
 * SQLite3 database scheme
 */

/**#@+
 * @ignore
 */

class sqlite_scheme extends sql_scheme
{
  public $random = 'RANDOM()';

  public $types = array(
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

  public $raw = array(
              'primary_key' => 'INTEGER NOT NULL PRIMARY KEY',
              'string' => array('type' => 'VARCHAR', 'length' => 255),
              'timestamp' => 'DATETIME',
              'binary' => 'BLOB',
            );

  final public function rename_table($from, $to) {
    return $this->execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
  }

  final public function add_column($to, $name, $type) {
    return $this->execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, $this->a_field($type)));
  }

  final public function remove_column($from, $name) {
    return $this->change_column($from, $name, NULL);
  }

  final public function rename_column($from, $name, $to) {
    $set = $this->columns($from);
    $old = $set[$name];

    $this->add_column($from, $to, array(
      $old['type'],
      $old['length'],
      $old['default'],
    ));

    $this->execute(sprintf('UPDATE "%s" SET "%s" = "%s"', $from, $to, $name));

    return $this->remove_column($from, $name);
  }

  final public function change_column($from, $name, $to) {
    $new = array();

    foreach ($this->columns($from) as $key => $val) {
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

    $this->begin();

    $this->execute($this->build_table($old = uniqid($from), $new));
    $this->execute(sprintf('INSERT INTO "%s" SELECT "%s" FROM "%s"', $old, join('", "', array_keys($new)), $from));
    $this->execute(sprintf('DROP TABLE "%s"', $from));

    $this->rename_table($old, $from);

    return $this->commit();
  }

  final public function add_index($to, $name, $column, $unique = FALSE) {
    return $this->execute(sprintf('CREATE%sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
  }

  final public function remove_index($name) {
    return $this->execute(sprintf('DROP INDEX IF EXISTS "%s"', $name));
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

  final public function fetch_tables() {
    $out = array();
    $sql = "SELECT name FROM sqlite_master WHERE type = 'table'";
    $old = $this->execute($sql);

    while ($row = $this->fetch_assoc($old)) {
      $out []= $row['name'];
    }

    return $out;
  }

  final public function fetch_columns($test) {
    $out = array();
    $sql = "PRAGMA table_info('$test')";
    $old = $this->execute($sql);

    while ($row = $this->fetch_assoc($old)) {
      preg_match('/^(\w+)(?:\((\d+)\))?.*?$/', strtoupper($row['type']), $match);

      $out[$row['name']] = array(
          'type' => $row['pk'] > 0 ? 'PRIMARY_KEY' : $match[1],
          'length' => ! empty($match[2]) ? (int) $match[2] : 0,
          'default' => trim($row['dflt_value'], "(')"),
          'not_null' => $row['notnull'] > 0,
      );
    }

    return $out;
  }

  final public function fetch_indexes($test) {
    $res = $this->execute("SELECT name,sql FROM sqlite_master WHERE type='index' AND tbl_name='$test'");

    $out = array();

    while ($one = $this->fetch_object($res)) {
      if (preg_match('/\((.+?)\)/', $one->sql, $match)) {
        $col = explode(',', preg_replace('/["\s]/', '', $match[1]));
        $out[$one->name] = array(
          'unique' => strpos($one->sql, 'UNIQUE ') !== FALSE,
          'column' => $col,
        );
      }
    }

    return $out;
  }

  final public function ensure_limit($from, $to) {
    return "\nLIMIT $from" . ($to ? ",$to\n" : "\n");
  }

  final public function quote_string($test) {
    return '"' . $test . '"';
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

/* EOF: ./library/db/schemes/sqlite.php */
