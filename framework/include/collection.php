<?php

/**
 * Array related functions
 */

/**
 * Generate dynamic range
 *
 * @link   http://www.php.net/manual/en/function.range.php
 * @param  mixed   Lower value|Range
 * @param  mixed   Higher value
 * @param  integer Step increment
 * @return array
 */
function ranges($low, $high = '', $step = 1) {
  $test = $high ? "$low-$high" : $low;
  $test = preg_replace('/[^0-9a-zA-Z.,\-]/', '', $test);
  $test = array_filter(explode(',', $test));

  $out  = array();
  $step = abs($step) ?: 1;


  foreach ($test as $one) {
    if (is_numeric($one) OR (strlen($one) === 1)) {
      $out []= $one;
    } elseif (is_string($one)) {
      $old = array_map('trim', explode('-', $one));

      $i   = array_shift($old);
      $c   = array_shift($old);
      $x   = 0;

      if ( ! $c OR ! $i) {
        continue;
      } elseif (is_alpha($i) OR is_alpha($c)) {
        $i  = ord($i);
        $c  = ord($c);
        $x += 1;
      }


      $y = $i <= $c ? 1 : -1;

      for (; $i * $y <= $c * $y; $i += $step * $y) {
        $out []= $x ? chr($i) : $i;
      }
    }
  }

  return $out;
}


/**
 * Build granular tree from array
 *
 * @param  array array  Tree
 * @param  array string Separator
 * @param  array string Key name
 * @param  array string Item title
 * @param  array string Item childs
 * @return array
 */
function tree($set, $sep = '. ', $key = 'id', $value = 'title', $items = 'childs') {
  $out = array();

  foreach ($set as $i => $item) {
    $x  = $i + 1;
    $id = ! empty($item[$key]) ? $item[$key] : $i;

    if ( ! empty($item[$value])) {
      $out[$id] = $x . $sep . $item[$value];

      if ( ! empty($item[$items]) && is_array($item[$items])) {
        $test = tree($item[$items], $sep, $key, $value, $items);

        foreach ($test as $k => $one) {
          $out[$k] = $x . $sep . $one;
        }
      }
    } else {
      $out[$id] = $x . $sep . $item;
    }
  }
  return $out;
}


/**
 * Build array grid
 *
 * @param  array   Values
 * @param  integer Columns
 * @param  integer Rows
 * @param  mixed   Function callback
 * @return array
 */
function grid($set, $cols = 5, $rows = 0, $callback = '') {
  $max =
  $inc = 0;
  $tmp =
  $out = array();

  if ($rows > 0) {
    $set = array_slice($set, 0, $cols * $rows);
  }


  foreach ($set as $val) {
    $tmp []= is_callable($callback) ? $callback($val) : $val;

    if (($inc += 1) >= $cols) {
      $out []= $tmp;

      $tmp   = array();
      $max  += 1;
      $inc   = 0;
    }
  }

  if ( ! empty($tmp)) {
    $out []= $tmp;
  }
  return $out;
}


/**
 * Wrap around array values
 *
 * @param  array   Values
 * @param  mixed   String|Function callback
 * @param  boolean Employ recursively?
 * @return array
 */
function wrap($set, $test = '%s', $recursive = FALSE) {
  foreach ($set as $key => $val) {
    if (is_array($val)) {
      $val = $recursive ? wrap($val, $test, $recursive) : $val;
    } else {
      $val = is_callable($test) ? $test($val) : sprintf($test, (string) $val);
    }
    $set[$key] = $val;
  }
  return $set;
}


/**
 * Map values
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function map($set, Closure $callback) {
  return array_values(kmap($set, $callback));
}


/**
 * Map values preserving keys
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function kmap($set, Closure $callback) {
  $out = array();

  foreach ($set as $key => $val) {
    $out[$key] = $callback($val);
  }

  return $out;
}


/**
 * Filter values
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function collect($set, Closure $callback) {
  return array_values(kcollect($set, $callback));
}


/**
 * Filter values preserving keys
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function kcollect($set, Closure $callback) {
  $out = array();

  foreach ($set as $key => $val) {
    if (is_array($val)) {
      $test = kcollect($val, $callback);

      if ( ! empty($test)) {
        $out[$key] = $test;
      }
    } elseif ($callback($val, $key)) {
      $out[$key] = $val;
    }
  }

  return $out;
}


/**
 * Filter out values
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function reject($set, Closure $callback) {
  return array_values(kreject($set, $callback));
}


/**
 * Filter out values preserving keys
 *
 * @param  array Values
 * @param  mixed Function callback
 * @return array
 */
function kreject($set, Closure $callback) {
  $out = array();

  foreach ($set as $key => $val) {
    if (is_array($val)) {
      $test = kreject($val, $callback);

      if ( ! empty($test)) {
        $out[$key] = $test;
      }
    } elseif ( ! $callback($val, $key)) {
      $out[$key] = $val;
    }
  }

  return $out;
}


/**
 * Join all nested array values
 *
 * @link   http://davidwalsh.name/flatten-nested-arrays-php
 * @param  array Values
 * @return array
 */
function flatten($array, $return = array()) {
  foreach ($array as $one) {
    if (is_array($one)) {
      $return = flatten($one, $return);
    } elseif($one) {
      $return []= $one;
    }
  }
  return $return;
}


/**
 * Fill keys with non numeric values
 *
 * @param  array Values
 * @return array
 */
function kfill($set) {
  $out = array();

  foreach ($set as $key => $val) {
    if ( ! is_numeric($val) && is_string($val)) {
      $out[$val] = $val;
    }
  }

  return $out;
}

/* EOF: ./framework/include/collection.php */
