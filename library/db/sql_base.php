<?php

/**
 * Core database functions
 */

/**#@+
  * @ignore
  */

class sql_base extends sql_raw
{
  // escaping for columns and tables
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

  // recursive string escaping
  final protected function fixate_string($test, $alone = FALSE) {
    if (is_array($test)) {
      if ($alone && sizeof($test) == 1) {
        $col = key($test);
        $val = $test[$col];

        if ( ! is_numeric($col)) {
          return $this->protect_names("$val.$col");
        } else {
          return $this->fixate_string($val, TRUE);
        }
      } else {
        return array_map(array($this, 'fixate_string'), $test);
      }
    } elseif (is_string($test)) {
      return "'" . $this->real_escape($test) . "'";
    }
    return $this->ensure_type($test);
  }

  // fields for SELECT
  final protected function build_fields($values) {
    $sql = array();

    foreach ((array) $values as $key => $val) {
      if (strlen(trim($val)) == 0) {
        continue;
      } elseif (is_numeric($key)) {
        $sql []= ' ' . $this->protect_names($val);
        continue;
      }
      $sql []= ' ' . $this->protect_names($key) . ' AS ' . $this->quote_string($val);
    }
    return join(",\n", $sql);
  }

  // values for INSERT/UPDATE
  final protected function build_values($fields, $insert = FALSE) {
    $sql    = array();
    $fields = (array) $fields;

    if ($insert) {
      $cols = array();

      foreach (array_keys($fields) as $one) {
        $cols []= $this->quote_string($one);
      }

      $sql []= '(' . join(', ', $cols) . ')';
      $sql []= "\nVALUES(";
    }


    $out   = array();
    $count = 0;
    $total = sizeof($fields);

    foreach ($fields as $key => $val) {
      if (is_numeric($key)) {
        $out []= $val;
      } else {
        $val = $this->fixate_string($val, TRUE);
        $val = is_numeric($val) ? $val : $val;

        if ($insert) {
          $out []= $val;
        } else {
          $out []= sprintf('%s = %s', $this->quote_string($key), $val);
        }
      }
    }

    $sql []= join(",\n", $out);

    if ($insert) {
      $sql []= ')';
    }

    return join('', $sql);
  }

  // dynamic WHERE building v2
  function build_where($test, $operator = 'AND') {
    $sql      = array();
    $operator = strtoupper($operator);

    foreach ($test as $key => $val) {
      if (is_numeric($key)) {
        if ( ! is_assoc($val)) {
          $raw = array_shift($val);
          if ($val && strpos($raw, '?')) {
            $sql []= $this->prepare($raw, $val);
          } else {
            array_unshift($val, $raw) && $sql []= join("\n", $val);
          }
        } else {
          $sql []= is_array($val) ? $this->build_where($val, $operator) : $val;
        }
      } elseif (is_keyword($key)) {
        $sql []= sprintf('(%s)', trim($this->build_where($val, strtoupper($key))));
      } elseif (preg_match('/_(?:and|or)_/i', $key, $match)) {
        $sub = array();
        foreach (explode($match[0], $key) as $one) {
          $sub[$one] = $val;
        }
        $sql []= sprintf('(%s)', $this->build_where($sub, strtoupper(trim($match[0], '_'))));
      } elseif (preg_match('/^(.+?)(?:\s+(!=?|[<>]=?|<>|NOT|R?LIKE)\s*)?$/', $key, $match)) {
        $sub = '';
        $key = $this->protect_names($match[1]);

        if (is_null($val)) {
          $sub = 'IS NULL';
        } else {
          $val = $this->fixate_string($val, FALSE);
          $sub = ! empty($match[2]) ? ($match[2] == '!' ? '!=' : $match[2]) : '=';
        }

        if (is_array($val) && (sizeof($val) > 1)) {
          $key  .= in_array($sub, array('!=', '<>')) ? ' NOT' : '';
          $sql []= " $key IN(" . join(', ', $val) . ")";
        } else {
          $val   = is_array($val) ? array_shift($val) : $val;
          $sql []= " $key $sub $val";
        }
      }
    }
    return join("\n$operator\n", $sql);
  }

  // hardcore SQL fixes!
  final protected function query_repare($test) {
    static $delete_expr = '/^\s*DELETE\s+FROM\s+(\S+)\s*$/is',
           $limit_expr = '/\s+LIMIT\s+(\d+)(?:\s*(?:,|\s+TO\s+)\s*(\d+))?\s*$/ei';

    if (method_exists($this, 'ensure_limit')) {
      $test = preg_replace($limit_expr, '$this->ensure_limit("\\1","\\2");', $test);
    }

    $test = preg_replace($delete_expr, 'DELETE FROM \\1 WHERE 1=1', $test);

    return $test;
  }

  // hardcore SQL sentence parsing
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
          if ($str !== FALSE) {
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
