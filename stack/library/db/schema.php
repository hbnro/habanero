<?php

/**
 * Database scheme library
 */

/**
 * Initiate transaction
 *
 * @return boolean
 */
db::implement('begin', function () {
  return (boolean) sql::begin();
});


/**
 * Commit the current transaction
 *
 * @return boolean
 */
db::implement('commit', function () {
  return (boolean) sql::commit();
});


/**
 * Cancel current transaction
 *
 * @return boolean
 */
db::implement('rollback', function () {
  return (boolean) sql::rollback();
});


/**
 * Database import
 *
 * @param  string  Filepath
 * @param  boolean Treat as plain SQL?
 * @return mixed
 */
db::implement('import', function ($from, $raw = FALSE) {
  ob_start();

  $old  = include $from;
  $test = ob_get_clean();


  if ( ! is_array($old)) {
    if (is_true($raw)) {
      return array_map(array('sql', 'execute'), sql::query_parse($test));
    }
    return FALSE;
  }

  foreach ((array) $old as $key => $val) {
    if ( ! empty($val['scheme'])) {
      db::build($key, (array) $val['scheme']);
    }

    if ( ! empty($val['data'])) {
      foreach ((array) $val['data'] as $one) {
        db::insert($key, $one);
      }
    }
  }
});


/**
 * Database export
 *
 * @param  string  Filepath
 * @param  string  Simple filter
 * @param  boolean Export data?
 * @param  boolean Export as plain SQL?
 * @return array
 */
db::implement('export', function ($to, $mask = '*', $data = FALSE, $raw = FALSE) {
  $out = array();

  foreach (db::tables($mask) as $one) {
    foreach (db::columns($one) as $key => $val) {
      $out[$one]['scheme'][$key] = array(
        $val['type'],
        $val['length'],
        $val['default'],
      );
    }

    if (is_true($data)) {
      $result = db::select($one, ALL);
      $out[$one]['data'] = db::fetch_all($result, AS_ARRAY);
    }
  }

  if (is_true($raw)) {
    $old = array();

    foreach ($out as $key => $val) {
      $old []= db::build($key, $val['scheme']) . ';';

      if ( ! empty($val['data'])) {
        foreach ((array) $val['data'] as $one) {
          $keys   = sql::build_fields($key);
          $values = sql::build_values($one, TRUE);

          $old [] = sprintf("INSERT INTO\n%s\n%s;", $keys, $values);
        }
      }
    }
    $text = join("\n", $old);
  } else {
    $code = var_export($out, TRUE);
    $text = '<' . "?php return $code;";
  }

  write($to, $text);
});


/**
 * List all tables
 *
 * @param  string Simple filter
 * @return array
 */
db::implement('tables', function ($filter = '*') {
  $out  = array();
  $test = sql::tables();

  if ($filter === '*') {
    return $test;
  }


  foreach ($test as $one) {
    if (match($filter, $one)) {
      $out []= $one;
    }
  }

  return $out;
});


/**
 * List all columns from given table
 *
 * @param     string Table name
 * @staticvar array  Column conversion set
 * @return    array
 */
db::implement('columns', function ($of) {
  static $set = NULL;


  if (is_null($set)) {
    $set = sql::type();
  }

  $test = sql::columns($of);

  foreach ($test as $key => $val) {
    $default     = ! empty($set[$val['type']]) ? $set[$val['type']] : $val['type'];
    $val['type'] = strtolower($default);
    $test[$key]  = $val;
  }

  return $test;
});


/**
 * List all indexes from given table
 *
 * @param  string Table name
 * @return array
 */
db::implement('indexes', function ($of) {
  return sql::indexes($of);
});


/**
 * Column definition scheme
 *
 * @param     string  Generic type
 * @param     integer Max length value
 * @param     scalar  Default value
 * @staticvar array   SQL definition set
 * @return    string
 */
db::implement('field', function ($type, $length = 0, $default = NULL) {
  static $set = NULL;


  if (is_null($set)) {
    $set = sql::raw();
  }

  if (empty($type)) {
    return FALSE;
  } else {
    $test = is_string($type) && ! empty($set[$type]) ? $set[$type] : $type;
  }


  if (is_assoc($test)) {
    $test = array_merge(compact('length', 'default'), $test);

    $type    = ! empty($test['type']) ? $test['type'] : $type;
    $length  = ! empty($test['length']) ? $test['length'] : $length;
    $default = ! empty($test['default']) ? $test['default'] : $default;
  } elseif (is_array($test)) {
    @list($type, $length, $default) = $test;

    ! $length && ! empty($set[$type]['length']) && $length = $set[$type]['length'];

    if ( ! empty($set[$type])) {//FIX
      if (is_string($set[$type])) {
        return $set[$type];
      }
      $type = $set[$type]['type'];
    }
  } elseif ($test !== $type) {
    return $test;
  }

  $type  = strtoupper($type);
  $type .= $length > 0 ? sprintf('(%d)', $length) : '';

  if ( ! is_null($default)) {
    $type .= ($default ? ' NOT' : '') . ' NULL';
  }

  $type .= ' DEFAULT ' . (is_null($default) ? 'NULL' : sql::fixate_string($default));

  return $type;
});


/**
 * Table definition scheme
 *
 * @param  string Table name
 * @param  array  Table definition
 * @return string
 */
db::implement('build', function ($table, array $columns = array()) {
  $name = sql::names($table);

  $sql  = "CREATE TABLE $name";
  $sql .= "\n(\n";


  foreach ($columns as $key => $value) {
    $sql  .= sprintf(" %s %s,\n", sql::names($key), db::field($value));
  }

  $sql  = $columns ? substr($sql, 0, - 2) : '';
  $sql .= "\n)";

  return $sql;
});

/* EOF: ./stack/library/db/schema.php */
