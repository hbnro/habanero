<?php

/**
 * Conditional functions library
 */

/**
 * Is application root?
 *
 * @return boolean
 */
function is_root() {
  return URI === '/';
}


/**
 * Is ajax maded request?
 *
 * @return boolean
 */
function is_ajax() {
  if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {// intentionally native
    return FALSE;
  }

  return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}


/**
 * Is odd number?
 *
 * @param  scalar  Number
 * @return boolean
 */
function is_odd($test) {
  return $test & 1;
}


/**
 * Is even number?
 *
 * @param  scalar  Number
 * @return boolean
 */
function is_even($test) {
  return ! ($test & 1);
}


/**
 * Is mime type valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_mime($test) {
  return preg_match('/^[a-z]+\/[a-z0-9\+-]+$/', $test) > 0;
}


/**
 * Is numeric range valid?
 *
 * @param  scalar  Number
 * @param  integer Min value
 * @param  integer Max value
 * @return boolean
 */
function is_num($test, $min = NULL, $max = NULL) {
  if ( ! is_scalar($test)) {
    return FALSE;
  }
  elseif (func_num_args() == 1) {
    return is_numeric($test);
  }
  elseif (func_num_args() == 2) {
    return ! is_false(strpos($min, $test));
  }

  return ($test >= $min) && ($test <= $max);
}


/**
 * Is hex valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_hex($test) {
  return preg_match('/^(#|0x)?[a-fA-F0-9]{3,6}$/', $test) > 0;
}


/**
 * Is alpha only valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_alpha($test) {
  return preg_match('/^[\sa-zA-Z]+$/', $test) > 0;
}


/**
 * Is alpha numeric valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_alnum($test) {
  return preg_match('/^[\sa-z0-9A-Z]+$/', $test) > 0;
}


/**
 * Is upper case valid?
 *
 * @param  scalar  String
 * @param  integer Initial position
 * @param  integer Final position
 * @return boolean
 */
function is_upper($test, $offset = 0, $length = 0) {
  if ($length > 0) {
    $test = substr($test, $offset, $length);
  }

  return preg_match('/^[A-Z]+$/', $test) > 0;
}


/**
 * Is lower case valid?
 *
 * @param  scalar  String
 * @param  integer Initial position
 * @param  integer Final position
 * @return boolean
 */
function is_lower($test, $offset = 0, $length = 0) {
  return ! is_upper($test, $length, $offset);
}


/**
 * Is associative array valid?
 *
 * @param  array   Array
 * @return boolean
 */
function is_assoc($set) {
  if ( ! is_array($set)) {
    return FALSE;
  }
  elseif (is_string(key($set))) {//FIX
    return TRUE;
  }

  foreach (array_keys($set) as $key) {
    if (is_num($key)) {
      return FALSE;
    }
  }

  return TRUE;
}


/**
 * Is time format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_time($test) {
  return preg_match('/^((0?[1-9]|1[012])(:[0-5]\d){0,2}([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$/', $test) > 0;
}


/**
 * Is date format valid?
 *
 * @param     scalar  String
 * @param     string  Format
 * @staticvar array   Regex bag
 * @return    boolean
 */
function is_date($test, $type = 'Ymd') {
  static $set = array(
            'y' => '[0-9][0-9]|[0-9][0-9]',
            'Y' => '[1][9][0-9][0-9]|[2][0-9][0-9][0-9]',
            'm' => '0[123456789]|10|11|12',
            'M' => 'Jan(?:uary)?|Feb(?:ruary)?|Ma(?:r(?:ch)?|y)|Apr(?:il)?|Ju(?:(?:ly?)|(?:ne?))|Aug(?:ust)?|Oct(?:ober)?|(?:Sep(?=\\b|t)t?|Nov|Dec)(?:ember)?',
            'd' => '[0-3](?:1|2)|[0-2][0-9]',
            'D' => '(?:Mon|Tues?|Wed(?:nes)?|Thu(?:rs)?|Fri|Sat(?:ur)?|Sun)(?:day)?',
          );


  $tmp = array();

  foreach (array_filter(preg_split('//', $type)) as $one) {
    if ( ! array_key_exists($one, $set)) {
      continue;
    }

    $tmp []= '(?:' . $set[$one] . ')';
  }

  $expr = sprintf('/^%s$/', join('\D*?', $tmp));

  return preg_match($expr, $test) > 0;
}


/**
 * Is datetime format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_datetime($test) {
  $set = explode(' ', $test);

  return is_time(array_pop($set)) && is_date(join(' ', $set));
}


/**
 * Is keyword valid?
 *
 * @param  scalar String
 * @return boolean
 */
function is_keyword($test) {
  return preg_match('/^(?:and|not|x?or)$/', $test) > 0;
}


/**
 * Is slug word valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_slug($test) {
  return ! match('%R', $test);
}


/**
 * Is money format valid?
 *
 * @param     scalar  String
 * @staticvar array   Regex bag
 * @return    boolean
 */
function is_money($test, $left = FALSE) {
  static $regex = array(
            '/^(?!\x{00a2})\p{Sc}?(?!0,?\d)(?:\d{1,3}(?:([\s,.])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?$/u',
            '/^(?!0,?\d)(?:\d{1,3}(?:([\s,.])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?(?<!\x{00a2})\p{Sc}?$/u',
          );

  $expr = $regex[(int) is_true($left)];

  if ( ! IS_UNICODE) {
    $expr = str_replace('\p{Sc}', '(?:£|¥|€|¢|\$)', $expr);
  }

  return preg_match($expr, $test) > 0;
}


/**
 * Is phone format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_phone($test) {
  return preg_match('/^\+?[0-9\(\)\-.,]{8,33}$/', $test) > 0;
}


/**
 * Is UUID format valid?
 *
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_uuid($test) {
  static $regex = NULL;


  if (is_null($regex)) {
    $alnum = '[A-Fa-f0-9]';
    $regex = "/{$alnum}{8}-{$alnum}{4}-{$alnum}{4}-{$alnum}{4}-{$alnum}{12}/";
  }

  return preg_match($regex, $test) > 0;
}


/**
 * Is word format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_word($test) {
  return preg_match('/^(?:[a-zA-Z0-9\._-](?=\s?\w|\b))+$/', $test) > 0;
}


/**
 * Is password format valid?
 *
 * @param  scalar  String
 * @param  string  Min length
 * @param  string  Max length
 * @return boolean
 */
function is_password($test, $min = 8, $max = 15) {
  $length = ((int) $min) . ',' . ((int) $max);

  return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{' . $length .'}$/', $test) > 0;
}


/**
 * Is email format valid?
 *
 * @param     scalar  String
 * @param     boolean Check multiple?
 * @param     boolean Use checkdnsrr()?
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_email($test, $multi = FALSE, $check = FALSE) {
  static $regex = '/^([\w\+\-:]+)(\.[\w\+\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i';


  $test = preg_split('/[,;\|]+/', (string) $test);

  if ( ! $multi && (sizeof($test) > 1)) {
    return FALSE;
  }
  elseif (empty($test)) {
    return FALSE;
  }


  foreach ($test as $value) {
    if ( ! preg_match($regex, $value)) {
      return FALSE;
    }
    elseif (is_true($check) && ! checkdnsrr(substr($value, strpos($value, '@') + 1), 'MX')) {
      return FALSE;
    }
  }

  return TRUE;
}


/**
 * Is URL format valid?
 *
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_url($test) {
  static $regex = '/^((?:[a-z]{2,7}:)?\/\/)([a-z0-9\-]{1,16}\.?)+([a-z]{2,6})?(:[0-9]{2,4})?\/?(\??.+)?$/i';

  return (strpos($test, 'data:') === 0) OR preg_match($regex, $test) > 0;
}


/**
 * Is local ip format valid?
 *
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_local($test = NULL) {
  static $regex = '/^(::|127\.|192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|localhost)/';

  if (is_url($test)) {
    $host = value($_SERVER, 'HTTP_HOST');
    $test = parse_url($test);

    if (isset($test['host']) && ($test['host'] !== $host)) {
      return FALSE;
    }
    return TRUE;
  }

  return preg_match($regex, $test ?: value($_SERVER, 'REMOTE_ADDR')) > 0;
}


/**
 * Is IPv4 format valid?
 *
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_ipv4($test) {
  static $regex = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

  return preg_match($regex, $test) > 0;
}


/**
 * Is IPv6 format valid?
 *
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_ipv6($test) {
  static $regex = NULL;


  if (is_null($regex)) {
    $regex = '/^([A-Fa-f0-9]{1,4}:){7}[A-Fa-f0-9]{1,4}$|'
           . '^[A-Fa-f0-9]{1,4}::([A-Fa-f0-9]{1,4}:){0,5}[A-Fa-f0-9]{1,4}$|'
           . '^([A-Fa-f0-9]{1,4}:){2}:([A-Fa-f0-9]{1,4}:){0,4}[A-Fa-f0-9]{1,4}$|'
           . '^([A-Fa-f0-9]{1,4}:){3}:([A-Fa-f0-9]{1,4}:){0,3}[A-Fa-f0-9]{1,4}$|'
           . '^([A-Fa-f0-9]{1,4}:){4}:([A-Fa-f0-9]{1,4}:){0,2}[A-Fa-f0-9]{1,4}$|'
           . '^([A-Fa-f0-9]{1,4}:){5}:([A-Fa-f0-9]{1,4}:){0,1}[A-Fa-f0-9]{1,4}$|'
           . '^([A-Fa-f0-9]{1,4}:){6}:[A-Fa-f0-9]{1,4}$/';
  }

  return preg_match($regex, $test) > 0;
}


/**
 * Is IP generic format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_ip($test) {
  return is_ipv4($test) OR is_ipv6($test);
}


/**
 * Is range ip valid?
 *
 * @param  scalar  String
 * @param  mixed   Array|Filter
 * @return boolean
 */
function is_range($test, array $ranges = array()) {
  if ( ! is_ip($test)) {
    return FALSE;
  }


  $tmp = array();
  $set = (array) $ranges;
  $par = explode('.', $test);

  foreach ($set as $test) {
    $check = 0;
    $parts = explode('.', $test);

    foreach ($parts as $i => $one) {
      $frags = explode(',', $one);

      foreach ($frags as $seg) {
        if (preg_match('/^([0-9]+)(?:-([0-9]+))$/', $seg, $match)) {// A-B
          if (is_num($par[$i], $match[1], $match[2])) {
            $check += 1;
          }
        }
        elseif (is_num($seg)) { // exactly
          if ($par[$i] == $seg) {
            $check += 1;
          }
        }
        elseif ($seg === '*') {// 0-255
          if (is_num($par[$i], 0, 255)) {
            $check += 1;
          }
        }
      }
    }

    $check = $check === 4 ?: FALSE;
    $tmp[$test] = $check;
  }

  if (sizeof($tmp) === array_sum($tmp)) {
    return TRUE;
  }

  return FALSE;
}


/**
 * Is Base64 format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_base64($test) {
  return ! preg_match('/[^a-zA-Z0-9\/\+=]/', $test);
}


/**
 * Is SHA1 format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_sha1($test) {
  return preg_match('/^[0-9a-f]{40}$/', $test) > 0;
}


/**
 * Is MD5 format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_md5($test) {
  return preg_match('/^[0-9a-f]{32}$/', $test) > 0;
}


/**
 * Is serialized string format valid?
 *
 * @param  scalar  String
 * @return boolean
 */
function is_serialized($test) {
  if ( ! is_string($test)) {// TODO: include WP url
    return FALSE;
  }

  if ($test == 'N;') {
    return TRUE;
  }
  elseif ( ! preg_match('/^([adObis]):/', $test, $match)) {
    return FALSE;
  }

  switch ($match[1]) {
    case 'a'; case 'O'; case 's';
    if (preg_match("/^{$match[1]}:[0-9]+:.*[;}]\$/s", $test)) {
        return TRUE;
      }
    break;
    case 'b'; case 'i'; case 'd';
      if (preg_match("/^{$match[1]}:[0-9\.E-]+;\$/", $test)) {
        return TRUE;
      }
    break;
    default: break;
  }

  return FALSE;
}


/**
 * Is JSON format valid?
 *
 * @param  scalar String
 * @link   http://webfreak.no/wp/2007/09/07/jsontest-for-mootools/
 * @return boolean
 */
function is_json($test) {
  return preg_match('/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u\s])+?$/', $test) > 0;
}


/**
 * Is today that?
 *
 * @param  mixed   String
 * @return boolean
 */
function is_today($test) {
  $time = is_timestamp($test) ? strtotime($test) : $test;

  if (date('Ymd') === date('Ymd', (int) $time)) {
    return TRUE;
  }

  return FALSE;
}


/**
 * Is timestamp format valid?
 *
 * @param  string  String
 * @return boolean
 */
function is_timestamp($test) {
  return $test && ( ! is_num($test) && strtotime($test)) ?: FALSE;
}


/**
 * Is UTF-8 format valid?
 *
 * @link      http://us3.php.net/manual/en/function.mb-check-encoding.php
 * @param     scalar  String
 * @staticvar string  RegExp
 * @return    boolean
 */
function is_utf8($test) {
  static $regex = NULL;


  if (is_null($regex)) {
    $regex = "/^([\x01-\x7F]+|([\xC2-\xDF][\x80-\xBF])|([\xE0-\xEF][\x80-\xBF][\x80-\xBF])"
           . "|([\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]))*\$/";
  }


  if (function_exists('mb_check_encoding')) {
    return mb_check_encoding($test, 'UTF-8');
  }

  return preg_match($regex, $string);
}


/**
 * Is CSS-NakedDay?
 *
 * @link   http://naked.dustindiaz.com/
 * @param  integer Specific day
 * @return boolean
 */
function is_naked_day($offset = 0) {
  $start = date('U', mktime(-12, 0, 0, 4, $offset, date('Y')));
  $end   = date('U', mktime(36, 0, 0, 4, $offset, date('Y')));

  $tick  = date('Z') * - 1;
  $now   = time() + $tick;

  if (($now >= $start) && ($now <= $end)) {
    return TRUE;
  }

  return FALSE;
}


/**
 * Is SSL valid?
 *
 * @return boolean
 */
function is_ssl() {
  if (isset($_SERVER['HTTPS'])) {
    if (strtolower($_SERVER['HTTPS']) == 'on') {
      return TRUE;
    }
    elseif ((int) $_SERVER['HTTPS'] > 0) {
      return TRUE;
    }
  }
  elseif (isset($_SERVER['SERVER_PORT']) && ((int) $_SERVER['SERVER_PORT'] == 443)) {
    return TRUE;
  }

  return FALSE;
}


/**
 * Is included file?
 *
 * @param  string  Filename
 * @return boolean
 */
function is_loaded($file) {
  return in_array(realpath($file), get_included_files());
}


/**
 * Is iterable value?
 *
 * @param  mixed  Object or array
 * @return boolean
 */
function is_iterable($test) {
  return ! is_callable($test) && (is_array($test) OR is_object($test));
}


/**
 * Is not null valid?
 *
 * @param  mixed  Expression
 * @return boolean
 */
function is_notnull($test) {
  foreach (func_get_args() as $one) {
    if (is_null($one)) {
      return FALSE;
    }
  }

  return TRUE;
}


/**
 * Is a empty string or array?
 *
 * @param  mixed  Expression
 * @return boolean
 */
function is_empty($test) {
  foreach (func_get_args() as $one) {
    if ( ! is_num($one) && empty($one)) {
      return TRUE;
    }
  }

  return FALSE;
}


/**
 * Is boolean true valid?
 *
 * @param  mixed   Expression
 * @return boolean
 */
function is_true($test) {
  if (func_num_args() == 1) {
    return $test === TRUE;
  }

  $args = func_get_args();

  foreach ($args as $one) {
    if (TRUE === $one) {
      return TRUE;
    }
  }

  return FALSE;
}


/**
 * Is boolean false true?
 *
 * @param  mixed   Expression
 * @return boolean
 */
function is_false($test) {
  if (func_num_args() == 1) {
    return $test === FALSE;
  }

  $args = func_get_args();

  foreach ($args as $one) {
    if (FALSE === $one) {
      return TRUE;
    }
  }

  return FALSE;
}


/**
 * Is locale safe?
 *
 * @param     string  Test string
 * @staticvar array   Locales
 * @return    boolean
 */
function is_locale($code) {
  static $set = NULL;


  if (is_null($set)) {
    $test = include  LIB.DS.'assets'.DS.'scripts'.DS.'locale_vars'.EXT;
    $set  = $test['locale'];
  }
  return in_array($code, $set);
}


/**
 * Is closure function?
 *
 * @param  mixed   Function callback
 * @return boolean
 */
function is_closure($test) {
  return is_object($test) && (get_class($test) === 'Closure');
}

/* EOF: ./framework/core/conditions.php */
