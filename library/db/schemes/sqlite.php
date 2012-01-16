<?php

/**
 * SQLite3 database scheme
 */

class sqlite_scheme extends sql_scheme
{
  protected $random = 'RANDOM()';

  protected $types = array(
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

  protected $raw = array(
              'primary_key' => 'INTEGER NOT NULL PRIMARY KEY',
              'string' => array('type' => 'VARCHAR', 'length' => 255),
              'timestamp' => array('type' => 'DATETIME'),
              'binary' => array('type' => 'BLOB'),
            );

  final protected function begin_transaction() {
    return $this->execute('BEGIN TRANSACTION');
  }

  final protected function commit_transaction() {
    return $this->execute('COMMIT TRANSACTION');
  }

  final protected function rollback_transaction() {
    return $this->execute('ROLLBACK TRANSACTION');
  }

  final protected function fetch_tables() {
    $out = array();
    $sql = "SELECT name FROM sqlite_master WHERE type = 'table'";
    $old = $this->execute($sql);

    while ($row = $this->fetch_assoc($old)) {
      $out []= $row['name'];
    }

    return $out;
  }

  final protected function fetch_columns($test) {
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

  final protected function fetch_indexes($test) {
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

  final protected function ensure_limit($from, $to) {
    return "\nLIMIT $from" . ($to ? ",$to\n" : "\n");
  }

  final protected function rename_table($from, $to) {
    return $this->execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
  }

  final protected function add_column($to, $name, $type) {
    return $this->execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, $this->a_field($type)));
  }

  final protected function remove_column($from, $name) {
    return $this->change_column($from, $name, NULL);
  }

  final protected function rename_column($from, $name, $to) {
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

  final protected function change_column($from, $name, $to) {
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

  final protected function add_index($to, $name, $column, $unique = FALSE) {
    return $this->execute(sprintf('CREATE%sINDEX IF NOT EXISTS "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
  }

  final protected function remove_index($name) {
    return $this->execute(sprintf('DROP INDEX IF EXISTS "%s"', $name));
  }

  final protected function quote_string($test) {
    return '"' . $test . '"';
  }
}

/* EOF: ./library/db/schemes/sqlite.php */
