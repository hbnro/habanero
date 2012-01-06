<?php

/**
 * Request handler
 */

class request extends prototype
{

  /**#@+
   * @ignore
   */

  // received headers
  private static $headers = array();

  /**#@-*/



  /**
   * Retrieve the headers
   *
   * @return array
   */
  final public static function all_headers() {
    if ( ! static::$headers) {
      foreach ($_SERVER as $key => $val) {
        if (substr($key, 0, 5) === 'HTTP_') {
          $key = strtolower(substr($key, 5));
          $key = camelcase($key, TRUE, '-');

          static::$headers[$key] = $val;
        }
      }
    }
    return static::$headers;
  }


  /**
   * Single header value
   *
   * @param  string Header
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function header($name, $default = FALSE) {
    $set = static::all_headers();

    return ! empty($set[$name]) ? $set[$name] : $default;
  }



  /**
   * PUT variable access
   *
   * @return mixed
   */
  final public static function put() {
    if ( ! static::is_put()) {
      return FALSE;
    }


    $out = (string) @file_get_contents('php://input');

    if (static::header('content-type') === 'application/x-www-form-urlencoded') {
      parse_str($out, $out);
    }
    return $out;
  }


  /**
  * GET variable access
  *
  * @param  string Identifier
  * @param  mixed  Default value
  * @return mixed
  */
  final public static function get($key = '', $or = FALSE) {
    return $key ? value($_GET, $key, $or) : $_GET;
  }


  /**
  * POST variable access
  *
  * @param  string Identifier
  * @param  mixed  Default value
  * @return mixed
  */
  final public static function post($key = '', $or = FALSE) {
    return $key ? value($_POST, $key, $or) : $_POST;
  }


  /**
  * Upload variable access
  *
  * @param  string Identifier
  * @return mixed
  */
  final public static function upload($key = '') {
    return $key ? value($_FILES, $key, array()) : $_FILES;
  }


  /**
  * Client address
  *
  * @return string
  */
  final public static function address() {
    return is_callable('gethostbyaddr') ? gethostbyaddr(static::remote_ip()) : static::remote_ip();
  }


  /**
  * Remote port
  *
  * @return integer
  */
  final public static function port() {
    return (int) server('REMOTE_PORT');
  }


  /**
  * User agent
  *
  * @return mixed
  */
  final public static function agent() {
    return server('HTTP_USER_AGENT');
  }


  /**
  * HTTP method
  *
  * @return string
  */
  final public static function method() {
    return server('REQUEST_METHOD');
  }


  /**
  * HTTP referer
  *
  * @param  string Valor por defecto
  * @return mixed
  */
  final public static function referer($or = FALSE) {
    return server('HTTP_REFERER', $or);
  }


  /**
  * Common client IP
  *
  * @param  string Default value
  * @return string
  */
  final public static function remote_ip($or = FALSE) {
    return server('HTTP_X_FORWARDED_FOR', server('HTTP_CLIENT_IP', server('REMOTE_ADDR', $or)));
  }


  /**
   * Is local ip format valid?
   *
   * @param     scalar  String
   * @staticvar string  RegExp
   * @return    boolean
   */
  final public static function is_local($test = NULL) {
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
   * Is application root?
   *
   * @return boolean
   */
  final public static function is_root() {
    return URI === '/';
  }


  /**
   * Is ajax maded request?
   *
   * @return boolean
   */
  final public static function is_ajax() {
    return server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
  }


  /**
   * Is POST request?
   *
   * @return boolean
   */
  final public static function is_post() {
    return static::method() === POST;
  }


  /**
   * Is GET request?
   *
   * @return boolean
   */
  final public static function is_get() {
    return static::method() === GET;
  }


  /**
   * Is PUT request?
   *
   * @return boolean
   */
  final public static function is_put() {
    return static::method() === PUT;
  }


  /**
   * Is DELETE request?
   *
   * @return boolean
   */
  final public static function is_delete() {
    return static::method() === DELETE;
  }


  /**
   * There are files uploaded?
   *
   * @param  string  Key or name
   * @return boolean
   */
  final public static function is_upload($key = NULL) {
    if (func_num_args() == 0) {
      return sizeof($_FILES) > 0;
    }


    $test = value($_FILES, $key);

    if ( ! empty($test['name'][0]) && $test['error'][0] == 0) {
      return TRUE;
    } elseif (is_array($test) && $test['error'] == 0) {
      return TRUE;
    }

    return FALSE;
  }


  /**
   * Is CSRF-free request valid?
   *
   * @staticvar string  Token
   * @return    boolean
   */
  final public static function is_safe() {
    static $check = NULL,
           $_token = NULL;


    if (is_null($check)) {
      $check  = defined('CHECK') ? CHECK : NULL;
      $_token = value($_SERVER, 'HTTP_X_CSRF_TOKEN');
    }


    @list($old_time, $old_token) = explode(' ', $check);
    @list($new_time, $new_token) = explode(' ', $_token);

    if (((time() - $old_time) < option('csrf.expires', 300)) && ($old_token === $new_token)) {
      return TRUE;
    }
    return FALSE;
  }

}


// default output
request::implement('dispatch', function (array $params = array()) {
  if (empty($params['to'])) {
    raise(ln('function_param_missing', array('name' => 'dispatch', 'input' => 'to')));
  } elseif ( ! (is_callable($params['to']) OR
              is_file($params['to']) OR
              is_url($params['to']))) {
    raise($params['to']);
  }


  @params(array_merge($params['defaults'], $params['matches']));

  dispatch($params);
});


/* EOF: ./library/server/request.php */
