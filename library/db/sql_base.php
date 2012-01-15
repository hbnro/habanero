<?php

/**
 * Core database functions
 */

/**#@+
  * @ignore
  */

class sql_base
{

  final protected function protect_names($test) {
    static $callback = NULL;

    if (is_null($callback)) {
      $callback = function ($str) {
        return trim($str, "`\" \n\/'´");
      };
    }

    $set = array_map($callback, explode(',', $test));

    foreach ($set as $i => $val) {
      $test = array_map($callback, explode('.', $val));
      $char = substr($this->quote_string('x'), 0, 1);

      foreach ($test as $key => $val) {
        if (preg_match('/^[\sa-zA-Z0-9_-]+$/', $val)) {
          $val = trim($val, $char);//FIX
          $val = $char . $val . $char;

          $test[$key] = $val;
        }
      }
      $set[$i] = join('.', $test);
    }
    return join(', ', $set);
  }

  final protected function mix_columns($test, $value) {
    $set    = preg_split('/_(?:or|and)_/', $test);
    $length = sizeof($set);
    $output = array();

    $output []= "\n" . $this->build_where(array(
      $set[0] => $value,
    ));

    for ($i = 1; $i < $length; $i += 1) {
      $one  = $set[$i];
      $next = isset($set[$i + 1]) ? $set[$i + 1] : '';

      if ( ! is_keyword($one)) {
        continue;
      }

      $output []= strtoupper($one) . "\n";
      $output []= $this->build_where(array(
        $next => $value,
      ));
    }
    return " (" . join('', $output) . " )\n";
  }

  final protected function fixate_string($test, $alone = FALSE) {
    if (is_array($test)) {
      if (is_true($alone) && sizeof($test) == 1) {
        $col = key($test);
        $val = $test[$col];

        if ( ! is_num($col)) {
          return $this->protect_names("$val.$col");
        } else {
          return $this->fixate_string($val, TRUE);
        }
      } else {
        return array_map(array($this, 'fixate_string'), $test);
      }
    } elseif (is_string($test) && $test) {
      $test = "'" . $this->real_escape($test) . "'";
    } elseif (is_bool($test)) {
      $test = ($test ? 'TRUE' : 'FALSE');
    }
    return $test;
  }

  final protected function build_fields($values) {
    $sql = array();

    foreach ((array) $values as $key => $val) {
      if (strlen(trim($val)) == 0) {
        continue;
      } elseif (is_num($key)) {
        $sql []= ' ' . $this->protect_names($val);
        continue;
      }
      $sql []= ' ' . $this->protect_names($key) . ' AS ' . $this->protect_names($val);
    }
    return join(",\n", $sql);
  }

  final protected function build_values($fields, $insert = FALSE) {
    $sql    = array();
    $fields = (array) $fields;

    if (is_true($insert)) {
      $cols = array();

      foreach (array_keys($fields) as $one) {
        $cols []= $this->protect_names($one);
      }

      $sql []= '(' . join(', ', $cols) . ')';
      $sql []= "\nVALUES(";
    }


    $out   = array();
    $count = 0;
    $total = sizeof($fields);

    foreach ($fields as $key => $val) {
      if (is_num($key)) {
        $out []= $val;
      } else {
        $val = $this->fixate_string($val, TRUE);
        $val = is_num($val) ? $val : ($val ?: 'NULL');

        if (is_true($insert)) {
          $out []= $val;
        } else {
          $out []= sprintf('%s = %s', $this->protect_names($key), $val);
        }
      }
    }

    $sql []= join(",\n", $out);

    if (is_true($insert)) {
      $sql []= ')';
    }

    return join('', $sql);
  }

  final protected function build_where($test, $operator = 'AND') {
    if ( ! empty($test)) {
      $operator = strtoupper($operator);
      $test     = (array) $test;
      $length   = sizeof($test);

      $inc = $count = $sql = '';

      foreach ($test as $key => $val) {
        if (preg_match('/_(?:or|and)_/', $key)) {
          $sql .= "$operator\n";
          $sql .= $this->mix_columns($key, $val);

          $count += 1;
          continue;
        } elseif (is_keyword($key)) {
          $out  = $this->build_where($val, $key);
          $sql .= strtoupper($key) . "\n$out";

          $count += 1;
          continue;
        } elseif (($inc += 1) > 1) {
          $sql .= "$operator\n";
        }

        if (is_num($key)) {
          if (is_string($val)) {
            $sql .= "$val\n";
          } else {
            $sql .= $this->build_where($val, $operator);
          }
        } elseif (preg_match('/^(.+?)(?:\s+(!=?|[<>]=|<>|NOT|R?LIKE)\s*)?$/', $key, $match)) {
          $oper = '';
          $key  = $this->protect_names($match[1]);

          if (is_null($val)) {
            $oper = 'IS NULL';
          } else {
            $val = $this->fixate_string($val, FALSE);
            $oper = ! empty($match[2]) ? ($match[2] == '!' ? '!=' : $match[2]) : '=';
          }

          if ( ! empty($sql)) {
            $sql .= "$operator\n";
          }

          if (is_array($val) && (sizeof($val) > 1)) {
            $key .= in_array($oper, array('!=', '<>')) ? ' NOT' : '';
            $sql .= " $key IN(" . join(', ', $val) . ")\n";
          } else {
            $val = is_array($val) ? array_shift($val) : $val;
            $sql .= " $key $oper $val\n";
          }
        }
      }

      $sql = $count > 0 ? " (\n$sql )\n" : $sql;

      $sql = preg_replace('/(AND|OR)\s*(AND|OR)/s', '\\1', $sql);
      $sql = preg_replace('/(?<=\()\s*AND|OR\s*(?=\))/s', '', $sql);

      return $sql;
    }
  }

  final protected function query_repare($test) {
    static $rand_expr = '/RAND(?:OM)?\s*\(([^\(\)]*)\)/i',
           $delete_expr = '/^\s*DELETE\s+FROM\s+(\S+)\s*$/is';

    if (function_exists('sql_limit')) {
      $limit_expr = '/\s+LIMIT\s+(\d+)(?:\s*(?:,|\s+TO\s+)\s*(\d+))?\s*$/i';
      $test       = preg_replace_callback($limit_expr, function ($match) {
        return $this->ensure_limit($match[1], $match[2]);
      }, $test);
    }

    $test = preg_replace($delete_expr, 'DELETE FROM \\1 WHERE 1=1', $test);
    $test = preg_replace($rand_expr, $this->random, $test);

    return $test;
  }

  final protected function query_parse($test, $separator = 59) {
    $last = substr($separator, 0, 2);

    if ($last === '\t') {
      $separator = "\t";
    } elseif ($last === '\n') {
      $separator = "\n";
    } else {
      $separator = char($last);
    }

    $hash = uniqid('--sql-quote');
    $exep = preg_quote($separator, '/');

    $test = trim($test, $separator) . $separator;

    $test = str_replace("\\'", $hash, $test);
    $test = preg_replace("/{$exep}+/", $separator, $test);
    $test = preg_replace("/{$exep}\s*{$exep}/", $separator, $test);

    $query  = '';
    $length = strlen($test);

    $str = FALSE;
    $out = array();

    for ($i = 0; $i < $length; $i += 1) {
      $char = substr($test, $i, 1);

      switch ($char) {
        case $separator;
          if ( ! is_false($str)) {
            $query .= $char;
          } else {
            if (strlen(trim($query)) == 0) {
              continue;
            }
            $query = str_replace($exep, "\\'", $query);
            $out []= $query;
            $str   = FALSE;
            $query = '';
          }
        break;
        case "'";
          $str    = ! $str;
          $query .= $char;
        break;
        default;
          $query .= $char;
        break;
      }
    }
    return $out;
  }

}

/**#@-*/

/* EOF: ./library/db/sql_base.php */
