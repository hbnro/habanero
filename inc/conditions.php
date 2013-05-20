<?php

if (! function_exists('is_assoc')) {
  function is_assoc($test)
  {
    return array_keys($test) !== range(0, sizeof($test) - 1);
  }
}

function is_odd($test)
{
  return $test & 1;
}

function is_even($test)
{
  return ! ($test & 1);
}

function is_mime($test)
{
  return preg_match('/^[a-z]+\/[a-z0-9\+-]+$/', $test) > 0;
}

function is_image($test)
{
  return (function_exists('exif_imagetype') ? @exif_imagetype($test) : @getimagesize($test)) !== FALSE;
}

function is_num($test, $min, $max = NULL)
{
  if (func_num_args() == 2) {
    return strpos($min, $test) !== FALSE;
  }

  return ($test >= $min) && ($test <= $max);
}

function is_hex($test)
{
  return preg_match('/^(#|0x)?[a-fA-F0-9]{3,6}$/', $test) > 0;
}

function is_alpha($test)
{
  return preg_match('/^[\sa-zA-Z]+$/', $test) > 0;
}

function is_alnum($test)
{
  return preg_match('/^[\sa-z0-9A-Z]+$/', $test) > 0;
}

function is_upper($test, $offset = 0, $length = 0)
{
  if ($length > 0) {
    $test = substr($test, $offset, $length);
  }

  return preg_match('/^[A-Z]+$/', $test) > 0;
}

function is_lower($test, $offset = 0, $length = 0)
{
  return ! is_upper($test, $length, $offset);
}

if ( ! function_exists('is_time')) {
  function is_time($test)
  {
    return preg_match('/^((0?[1-9]|1[012])(:[0-5]\d){0,2}([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$/', $test) > 0;
  }
}

if ( ! function_exists('is_date')) {
  function is_date($test, $type = 'Ymd')
  {
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

    $expr = '/^' . join('\D*?', $tmp) . '$/';

    return preg_match($expr, $test) > 0;
  }
}

function is_datetime($test)
{
  $set = explode(' ', $test);

  return is_time(array_pop($set)) && is_date(join(' ', $set));
}

function is_slug($test)
{
  return ! preg_match('/[^\w-\/_.]/', $test);
}

function is_money($test, $left = FALSE)
{
  static $regex = array(
            '/^(?!\x{00a2})\p{Sc}?(?!0,?\d)(?:\d{1,3}(?:([\s,.])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?$/u',
            '/^(?!0,?\d)(?:\d{1,3}(?:([\s,.])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{2})?(?<!\x{00a2})\p{Sc}?$/u',
          );

  $expr = $regex[(int) $left];

  if (! IS_UNICODE) {
    $expr = str_replace('\p{Sc}', '(?:£|¥|€|¢|\$)', $expr);
  }

  return preg_match($expr, $test) > 0;
}

function is_phone($test)
{
  return preg_match('/^\+?[0-9\(\)\-.,]{8,33}$/', $test) > 0;
}

function is_uuid($test)
{
  static $regex = NULL;

  if (is_null($regex)) {
    $alnum = '[A-Fa-f0-9]';
    $regex = "/{$alnum}{8}-{$alnum}{4}-{$alnum}{4}-{$alnum}{4}-{$alnum}{12}/";
  }

  return preg_match($regex, $test) > 0;
}

function is_utf8($test)
{
  static $utf8_expr = "/^([\x01-\x7F]+|([\xC2-\xDF][\x80-\xBF])|([\xE0-\xEF][\x80-\xBF][\x80-\xBF])|([\xF0-\xF4][\x80-\xBF][\x80-\xBF][\x80-\xBF]))*\$/";

  if (function_exists('mb_check_encoding')) {
    return mb_check_encoding($test, 'UTF-8');
  }

  return preg_match($utf8_expr, $string);
}

function is_word($test)
{
  return preg_match('/^(?:[a-zA-Z0-9\._-](?=\s?\w|\b))+$/', $test) > 0;
}

function is_password($test, $min = 8, $max = 15)
{
  $length = ((int) $min) . ',' . ((int) $max);

  return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{' . $length .'}$/', $test) > 0;
}

if ( ! function_exists('is_email')) {
  function is_email($test, $multi = FALSE, $check = FALSE)
  {
    static $regex = '/^([\w\+\-:]+)(\.[\w\+\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i';

    $test = preg_split('/[,;\|]+/', (string) $test);

    if ( ! $multi && (sizeof($test) > 1)) {
      return FALSE;
    } elseif (empty($test)) {
      return FALSE;
    }

    foreach ($test as $value) {
      if ( ! preg_match($regex, $value)) {
        return FALSE;
      } elseif ($check && ! checkdnsrr(substr($value, strpos($value, '@') + 1), 'MX')) {
        return FALSE;
      }
    }

    return TRUE;
  }
}

function is_url($test)
{
  static $regex = '/^((?:[a-z]{2,7}:)?\/\/)([a-z0-9\-]{1,16}\.?)+([a-z]{2,6})?(:[0-9]{2,4})?\/?(\??.+)?$/i';

  return (strpos($test, 'data:') === 0) OR preg_match($regex, $test) > 0;
}

function is_ipv4($test)
{
  static $regex = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

  return preg_match($regex, $test) > 0;
}

function is_ipv6($test)
{
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

function is_ip($test)
{
  return is_ipv4($test) OR is_ipv6($test);
}

function is_range($test, array $ranges = array())
{
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
        } elseif (is_numeric($seg)) { // exactly
          if ($par[$i] == $seg) {
            $check += 1;
          }
        } elseif ($seg === '*') {// 0-255
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

function is_base64($test)
{
  return ! preg_match('/[^a-zA-Z0-9\/\+=]/', $test);
}

function is_sha1($test)
{
  return preg_match('/^[0-9a-f]{40}$/', $test) > 0;
}

function is_md5($test)
{
  return preg_match('/^[0-9a-f]{32}$/', $test) > 0;
}

function is_json($test)
{
  return preg_match('/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u\s])+?$/', $test) > 0;
}

function is_today($test)
{
  $time = is_timestamp($test) ? strtotime($test) : $test;

  if (date('Ymd') === date('Ymd', (int) $time)) {
    return TRUE;
  }

  return FALSE;
}

function is_timestamp($test)
{
  return $test && ( ! is_numeric($test) && strtotime($test)) ?: FALSE;
}

function is_notnull($test)
{
  foreach (func_get_args() as $one) {
    if (is_null($one)) {
      return FALSE;
    }
  }

  return TRUE;
}

function is_empty($test)
{
  foreach (func_get_args() as $one) {
    if ( ! is_numeric($one) && empty($one)) {
      return TRUE;
    }
  }

  return FALSE;
}
