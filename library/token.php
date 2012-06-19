<?php

/**
 * Token related functions
 */

class token extends prototype
{

  /**
   * Tokens length
   *
   * @param  string Text
   * @param  mixed  Token
   * @return integer
   */
  final public static function len($text, $ord = 32) {
    return substr_count($text, char($ord)) + 1;
  }


  /**
   * Tokens average
   *
   * @param  string Text
   * @param  mixed  Token
   * @return integer
   */
  final public static function avg($text, $index = 0, $ord = 32) {
    $test = (array) static::get($text, $index, $ord, FALSE);
    return array_sum($test) / sizeof($test);
  }


  /**
   * Tokens sum
   *
   * @param  string Text
   * @param  mixed  Token
   * @return integer
   */
  final public static function sum($text, $index = 0, $ord = 32) {
    return array_sum((array) static::get($text, $index, $ord, FALSE));
  }


  /**
   * Retrieve single token or range
   *
   * @param  string  Text
   * @param  mixed   Index
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function get($text, $index = 0, $ord = 32, $join = TRUE) {
    $test = explode(char($ord), $text);

    if ( ! $index) {
      return $join ? join(char($ord), $test) : $test;
    } elseif (preg_match('/(\d+)-(\d*)/', $index, $match)) {
      if ( ! empty($match[2])) {
        $test = array_slice($test, $match[1] - 1, $match[2] - ($match[1] - 1));
      } else {
        $test = array_slice($test, $match[1] - 1);
      }
      return static::glue($join, $ord, $test);
    }

    $index = $index > 0 ? $index - 1:  $index;
    $index = $index < 0 ? sizeof($test) + $index : $index;

    return isset($test[$index]) ? $test[$index] : FALSE;
  }


  /**
   * Add single token
   *
   * @param  string  Text
   * @param  mixed   Value
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function add($text, $value = '', $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);

    array_splice($out, sizeof($out), 0, $value);

    return static::glue($join, $ord, $out);
  }


  /**
   * Search token
   *
   * @param  string  Text
   * @param  mixed   Value
   * @param  mixed   Token
   * @return integer
   */
  final public static function find($text, $value, $ord = 32) {
    return array_search($value, static::get($text, 0, $ord, FALSE)) + 1;
  }


  /**
   * Token exists?
   *
   * @param  string  Text
   * @param  mixed   Value
   * @param  mixed   Token
   * @return boolean
   */
  final public static function exists($text, $value, $ord = 32) {
    return in_array($value, static::get($text, 0, $ord, FALSE));
  }


  /**
   * Find and remove tokens
   *
   * @param  string  Text
   * @param  mixed   Value|Array
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function rm($text, $find, $ord = 32, $join = TRUE) {
    $out  = array();
    $find = (array) $find;

    foreach (static::get($text, 0, $ord, FALSE) as $one) {
      ! in_array($one, $find) && $out []= $one;
    }

    return static::glue($join, $ord, $out);
  }


  /**
   * Delete single token or range
   *
   * @param  string  Text
   * @param  mixed   Index
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function del($text, $index = 0, $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);

    if (preg_match('/(\d*)-(\d*)/', $index, $match)) {
      if (empty($match[1]) && ! empty($match[2])) {
        $out = array_splice($out, - $match[2]);
      } elseif ( ! empty($match[2])) {
        $out = array_splice($out, $match[1] - 1, $match[2] - ($match[1] - 1));
      }

      $out = array_splice($out, $match[1] - 1);
    } elseif (($index > 0) && ($index <= sizeof($out))) {
      unset($out[$index - 1]);
    }

    return static::glue($join, $ord, $out);
  }


  /**
   * Insert tokens
   *
   * @param  string  Text
   * @param  mixed   Array|Value
   * @param  mixed   Index
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function insert($text, $value = '', $index = 0, $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);

    ($index <> 0) && array_splice($out, $index, 0, $value);

    return static::glue($join, $ord, $out);
  }


  /**
   * Replace token
   *
   * @param  string  Text
   * @param  array   Replacements
   * @param  integer Offset
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function replace($text, array $repl, $offset = 0, $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);
    $len = sizeof($out);

    for ($i = $offset; $i < $len; $i += 1) {
      if ( ! ($tmp = array_shift($repl))) {
        break;
      }
      $out[$i] = $tmp;
    }
    return static::glue($join, $ord, $out);
  }


  /**
   * Set token value
   *
   * @param  string  Text
   * @param  mixed   Array|Value
   * @param  mixed   Range|Index
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function set($text, $value, $index = 0, $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);

    if (preg_match('/(\d*)-(\d*)/', $index, $match)) {
      if (empty($match[1]) && ! empty($match[2])) {
        $out = array_splice($out, - $match[2], $match[2], $value);
      } elseif ( ! empty($match[2])) {
        $out = array_splice($out, $match[1] - 1, $match[2] - ($match[1] - 1), $value);
      }

      $out = array_splice($out, $match[1] - 1, sizeof($out), $value);
    } elseif ($index > 0) {
      $out = array_splice($out, $index - 1, 1, $value);
    }

    return static::glue($join, $ord, $out);
  }


  /**
   * Retrieve token index by RegExp
   *
   * @param  string  Text
   * @param  string  Regex
   * @param  mixed   Token
   * @return integer
   */
  final public static function grep($text, $regex, $ord = 32) {
    $regex = sprintf('/%s/', str_replace('/', '\\/', $regex));

    foreach (static::get($text, 0, $ord, FALSE) as $key => $val) {
      if (@preg_match($regex, $val)) {
        return $key + 1;
      }
    }
  }


  /**
   * Retrieve token index by fnmatch
   *
   * @param  string Text
   * @param  mixed  Filter
   * @param  mixed  Index
   * @param  mixed  Token
   * @return mixed
   */
  final public static function match($text, $filter, $ord = 32) {
    foreach ( static::get($text, 0, $ord, FALSE) as $key => $val) {
      if (fnmatch($filter, $val)) {
        return $key + 1;
      }
    }
  }


  /**
   * Adjust token padding
   *
   * @param  string  Text
   * @param  mixed   Length
   * @param  mixed   Value
   * @param  mixed   Token
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function pad($text, $length, $value = '', $ord = 32, $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);
    $out = array_pad($out, $length, $value);

    return static::glue($join, $ord, $out);
  }


  /**
   * Sort tokens
   *
   * @param  string  Text
   * @param  mixed   Token
   * @param  mixed   Sort mode|Callback
   * @param  boolean Join again?
   * @return mixed
   */
  final public static function sort($text, $ord = 32, $mode = '', $join = TRUE) {
    $out = static::get($text, 0, $ord, FALSE);

    if (is_closure($mode)) {
      usort($out, $mode);
    } else {
      $dec = strlen($mode);

      while ($dec > 0) {
        switch (substr($mode, $dec -= 1, 1)) {
          case 'R';
            $out = array_reverse($out);
          break;
          case 'N';
            natsort($out);
          break;
          case 'n';
            natcasesort($out);
          break;
          case 's';
            shuffle($out);
          break;
          case 'r';
            rsort($out);
          break;
          default;
          break;
        }
      }

      ! $mode && sort($out);
    }

    return static::glue($join, $ord, $out);
  }



  /**#@+
   * @ignore
   */

  // join all items
  final private static function glue($join, $ord, $out) {
    return $join ? join(char($ord), $out) : $out;
  }

  /**#@-*/

}

/* EOF: ./library/token.php */
