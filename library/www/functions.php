<?php

/**
 * Basic server functions
 */

/**#@+
  * HTTP methods
  */
define('GET', 'GET');
define('PUT', 'PUT');
define('POST', 'POST');
define('DELETE', 'DELETE');
/**#@-*/

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
 * GET method shortcut
 *
 * @param  string Expression
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function get($path, $to, array $params = array()) {
  route("GET $path", $to, $params);
}


/**
 * PUT method shortcut
 *
 * @param  string Expression
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function put($path, $to, array $params = array()) {
  route("PUT $path", $to, $params);
}


/**
 * POST method shortcut
 *
 * @param  string Expression
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function post($path, $to, array $params = array()) {
  route("POST $path", $to, $params);
}


/**
 * DELETE method shortcut
 *
 * @param  string Expression
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function delete($path, $to, array $params = array()) {
  route("DELETE $path", $to, $params);
}


/**
 * Root shortcut
 *
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function root($to, array $params = array()) {
  route('/', $to, $params);
}


/**
 * Register routes
 *
 * @param  mixed Expression|Function callback
 * @param  mixed Function callback
 * @param  array Options hash
 * @return mixed
 */
function route($match, $to = NULL, array $params = array()) {
  if (is_assoc($match)) {
    $params = array_merge($match, $params);
  } elseif ( ! isset($params['match'])) {
    $params['match'] = $match;
  }

  if (is_assoc($to)) {
    $params = array_merge($to, $params);
  } elseif ( ! isset($params['to'])) {
    $params['to'] = $to;
  }


  foreach (array('GET', 'POST', 'PUT' , 'DELETE') as $method) {
    $key = strtolower($method);

    if ( ! empty($params[$key])) {
      $params['match'] = $method . ' ' . $params[$key];
    }
  }


  if (empty($params['match'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'match')));
  }


  $params['match'] = trim($params['match']);

  if (strpos($params['match'], ' ') === FALSE) {
    $params['match'] = 'GET ' . $params['match'];
  }

  routing::bind($params);
}


/**
 * Segments part
 *
 * @staticvar array Parts bag
 * @return    array
 */
function parts() {
  static $test = NULL;

  if ( ! is_array($test)) {
    $test = explode('/', trim(URI, '/'));


    foreach ($test as $key => $val) {
      $test[$key] = $val;
    }
  }
  return $test;
}


/**
 * Associative segments
 *
 * @param  integer Index key
 * @param  mixed   Default value
 * @return array
 */
function assoc($index = 1, $default = FALSE) {
  $set    = parts();
  $output = array();

  $index  = $index - 1;
  $length = sizeof($set);


  for (; $index < $length; $index += 2) {
    $value = isset($set[$index + 1]) ? $set[$index + 1] : $default;
    $output[$set[$index]] = $value;
  }

  return $output;
}


/**
 * Single segment
 *
 * @param  integer Index key
 * @param  mixed   Default value
 * @return string
 */
function segment($index = 1, $default = FALSE) {
  $set = parts();


  if ( ! $index) {
    return sizeof($set);
  } elseif ($index < 0) {
    $index = sizeof($set) + 1 + $index;
  }

  $output = ! empty($set[$index - 1]) ? $set[$index - 1] : $default;

  return $output;
}


/**
 * Server variable access
 *
 * @param  string  Identifier
 * @param  mixed   Default value
 * @param  boolean Use full scheme?
 * @return mixed
 */
function server($key = '', $default = FALSE, $complete = FALSE) {
  global $_SERVER;

  if (func_num_args() == 0) {
    $test = explode('.', $_SERVER['SERVER_NAME']);

    if ( ! empty($test[0]) && ($test[0] === 'www')) {
      array_shift($test);
    }

    return join('.', $test);
  } elseif ($key === TRUE) {
    $host = '';

    if ($complete) {
      $pre   = explode('/', $_SERVER['SERVER_PROTOCOL']);

      $host .= strtolower(array_shift($pre));
      $host .= ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '';
      $host .= ':';//FIX
    }

    $host .= '//' . strtolower($_SERVER['HTTP_HOST']);
    $host .= (int) $_SERVER['SERVER_PORT'] !== 80 ? ":$_SERVER[SERVER_PORT]" : '';
    $host .= $default !== FALSE ? $default : '';

    return $host;
  } elseif ( ! empty($_SERVER[$key])) {
    return strip_tags($_SERVER[$key]); //FIX?
  }

  return $default;
}


/**
 * Force file download
 *
 * @link   http://php.net/manual/en/function.fread.php
 * @param  string  Filepath
 * @param  string  Filename
 * @param  string  Mimetype
 * @param  integer Size limit
 * @return void
 */
function download($path, $name = '', $mime = '', $kbps = 24) {
  if (headers_sent($file, $line)) {
    raise(ln('headers_sent', array('script' => $file, 'number' => $line)));
  }

  if ( ! is_file($path)) {
    raise(ln('file_not_exists', array('name' => $file)));
  }


  $mime   = ! empty($mime) ? $mime : 'application/octet-stream';
  $name   = ! empty($name) ? $name : substr(md5(time()), 0, 7) . basename($path);
  $length = filesize($path);


  header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
  header(sprintf('Content-Length: %d', $length));
  header(sprintf('Content-Type: %s', $mime));

  header('Content-Transfer-Encoding: binary');
  header('Pragma: no-cache');
  header('Expires: 0');

  if (func_num_args() <= 3) {
    readfile($path);
    exit;
  }


  $range  = 0;

  if ($test = server('HTTP_RANGE')) {
    list($unit, $orig) = @explode('=', $test, 2);

    if ($unit == 'bytes') {
      list($range, $extra) = @explode(',', $orig, 2);
    } else {
      $range = 0;
    }
  }


  list($start, $end) = @explode('-', $range, 2);

  $end   = empty($end) ? $length - 1 : min(abs((int) $end), $length - 1);
  $start = empty($start) || ($end < abs((int) $start)) ? 0 : max(abs((int) $start), 0);

  if ($start > 0 || $end < ($length - 1)) {
    status(206);
  }

  header('Accept-Ranges: bytes');
  header("Content-Range: bytes $start-$end/$length");

  $tmp = fopen($path, 'rb');
  fseek($tmp, $start);

  while ( ! feof($tmp)) {
    if ($start >= $end) {
      break;
    }

    set_time_limit(0);

    $bytes  = 1024 * 8;
    $start += $bytes;

    echo fread($tmp, $bytes);

    flush();
  }

  fclose($tmp);
  exit;
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


  if (is_numeric($key)) {
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
  } elseif (is_string($hash) && ! is_numeric($hash)) {
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

/* EOF: ./library/www/functions.php */
