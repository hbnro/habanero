<?php

/**
 * Session functions library
 */

/**#@+
  * Expiration values in secs
  */
define('NEVER', time() * 42);
define('YEARLY', 29030400);
define('MONTHLY', 2419200);
define('WEEKLY', 604800);
define('DAILY', 86400);
define('HOURLY', 3600);
define('NOW', - 300);
/**#@-*/

/**
 * Cookie variable access
 *
 * @param  string  Identifier
 * @param  mixed   Default value
 * @param  integer Expiration time
 * @return mixed
 */
function cookie($key, $value = NULL, $expires = NEVER) {
  if (func_num_args() === 1) {
    return value($_COOKIE, $key);
  }

  setcookie($key, $value, $expires > 0 ? time() + $expires : - 1, ROOT);
}


/**
 * Session variable access
 *
 * @param  string Identifier
 * @param  mixed  Default value
 * @param  array  Options
 * @return mixed
 */
function session($key, $value = '', array $option = array()) {
  $hash =  "--a-session$$key";

  if (func_num_args() === 1) {
    if ( ! is_array($test = value($_SESSION, $hash))) {
      return FALSE;
    } elseif (array_key_exists('value', $test)) {
      return $test['value'];
    }
    return FALSE;
  } elseif (is_string($hash) && ! is_num($hash)) {
    if (is_null($value) && isset($_SESSION[$hash])) {
      unset($_SESSION[$hash]);
    } else {
      if ( ! is_array($option)) {
        $option = array('expires' => (int) $option);
      }

      if ( ! empty($option['expires'])) {
        $plus = $option['expires'] < time() ? time() : 0;
        $option['expires'] += $plus;
      }

      $_SESSION[$hash] = $option;
      $_SESSION[$hash]['value'] = $value;
    }
  }
}


/**
 * Flash utility function
 *
 * @param     string Key io name
 * @param     mixed  Default value
 * @staticvar array  Vars bag
 * @return    void
 */
function flash($key = -1, $value = FALSE) {
  static $output = NULL,
         $set = array();


  if (func_num_args() <= 1) {
    if (isset($output[$key])) {
      return $output[$key];
    } elseif ( ! is_null($output) && ! func_num_args()) {
      return $output;
    }

    $output = array_filter((array) session('--flash-data'));

    session('--flash-data', array());

    return $output;
  }


  if (is_num($key)) {
    return FALSE;
  }

  if ( ! isset($set[$key])) {
    $set[$key] = $value;
  } else {
    $set[$key]   = (array) $set[$key];
    $set[$key] []= $value;
  }

  session('--flash-data', $set, array(
    'hops' => 1,
  ));
}

/* EOF: ./library/server/session.php */
