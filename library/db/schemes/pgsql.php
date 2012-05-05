<?php

/**
 * PostgreSQL database scheme
 */

/**#@+
 * @ignore
 */

class pgsql_scheme extends sql_scheme
{
  protected $random = 'RANDOM()';

  protected $types = array(
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

  protected $raw = array(
              'primary_key' => 'SERIAL PRIMARY KEY',
              'string' => array('type' => 'CHARACTER varying', 'length' => 255),
            );

  final public function rename_table($from, $to) {
    return $this->execute(sprintf('ALTER TABLE "%s" RENAME TO "%s"', $from, $to));
  }

  final public function add_column($to, $name, $type) {
    return $this->execute(sprintf('ALTER TABLE "%s" ADD COLUMN "%s" %s', $to, $name, $this->a_field($type)));
  }

  final public function remove_column($from, $name) {
    return $this->execute(sprintf('ALTER TABLE "%s" DROP COLUMN "%s" RESTRICT', $from, $name));
  }

  final public function rename_column($from, $name, $to) {
    return $this->execute(sprintf('ALTER TABLE "%s" RENAME COLUMN "%s" TO "%s"', $from, $name, $to));
  }

  final public function change_column($from, $name, $to) {
    return $this->execute(sprintf('ALTER TABLE "%s" ALTER COLUMN "%s" TYPE %s', $from, $name, $this->a_field($to)));
  }

  final public function add_index($to, $name, $column, $unique = FALSE) {
    return $this->execute(sprintf('CREATE%sINDEX "%s" ON "%s" ("%s")', $unique ? ' UNIQUE ' : ' ', $name, $to, join('", "', $column)));
  }

  final public function remove_index($name) {
    return $this->execute(sprintf('DROP INDEX "%s"', $name));
  }

  final protected function begin_transaction() {
    return $this->execute('BEGIN');
  }

  final protected function commit_transaction() {
    return $this->execute('COMMIT');
  }

  final protected function rollback_transaction() {
    return $this->execute('ROLLBACK');
  }

  final protected function set_encoding() {
    return $this->execute("SET NAMES 'UTF-8'");
  }

  final protected function fetch_tables() {
    $out = array();

    $sql = "SELECT tablename FROM pg_tables WHERE tablename "
         . "!~ '^pg_+' AND schemaname = 'public'";

    $old = $this->execute($sql);

    while ($row = $this->fetch_assoc($old)) {
      $out []= $row['tablename'];
    }

    return $out;
  }

  final protected function fetch_columns($test) {
    $out = array();

    $sql = "SELECT DISTINCT "
         . "column_name, data_type AS t, character_maximum_length, column_default AS d,"
         . "is_nullable FROM information_schema.columns WHERE table_name='$test'";

    $old = $this->execute($sql);

    while ($row = $this->fetch_assoc($old)) {
      if (preg_match('/^nextval\(.+$/', $row['d'], $id)) {
        $row['d'] = NULL;
      } else {
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
  }

  final protected function fetch_indexes($test) {
    $out = array();

    $sql = "select pg_get_indexdef(indexrelid) AS sql from pg_index where indrelid = '$test'::regclass";

    if (is_object($res = $this->execute($sql))) {
      while ($one = $res->fetchObject()) {
        if (preg_match('/CREATE(\s+UNIQUE|)\s+INDEX\s+(\w+)\s+ON.+?\((.+?)\)/', $one->sql, $match)) {
          $out[$match[2]] = array(
            'unique' => ! empty($match[1]),
            'column' => explode(',', preg_replace('/["\s]/', '', $match[3])),
          );
        }
      }
    }

    return $out;
  }

  final protected function ensure_limit($from, $to) {
    return $to ? "\nLIMIT $to OFFSET $from" : "\nLIMIT $from\n";
  }

  final protected function quote_string($test) {
    return '"' . $test . '"';
  }

  final protected function ensure_type($test) {
    if (is_bool($test)) {
      $test = $test ? 'TRUE' : 'FALSE';
    }
    return $test;
  }
}

/**#@-*/

/* EOF: ./library/db/schemes/pgsql.php */
