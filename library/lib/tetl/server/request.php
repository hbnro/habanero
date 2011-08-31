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
   *
   *
   *
   * @return void
   */
  final public static function all_headers()
  {
    if ( ! request::$headers)
    {
      foreach ($_SERVER as $key => $val)
      {
        if (substr($key, 0, 5) === 'HTTP_')
        {
          $key = strtolower(substr($key, 5));
          $key = camelcase($key, TRUE, '-');

          request::$headers[$key] = $val;
        }
      }
    }
    return request::$headers;
  }


  /**
   *
   *
   *
   * @return void
   */
  final public static function header($name, $default = FALSE)
  {
    $set = request::all_headers();

    return ! empty($set[$name]) ? $set[$name] : $default;
  }



  /**
   * PUT variable access
   */
  function put()
  {
    if ( ! request::is_put())
    {
      return FALSE;
    }


    $out = (string) @file_get_contents('php://input');

    if (request::header('content-type') === 'application/x-www-form-urlencoded')
    {
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
  function get($key, $or = FALSE)
  {
    return value($_GET, $key, $or);
  }


  /**
  * POST variable access
  *
  * @param  string Identifier
  * @param  mixed  Default value
  * @return mixed
  */
  function post($key, $or = FALSE)
  {
    return value($_POST, $key, $or);
  }


  /**
  * Upload variable access
  *
  * @param  string Identifier
  * @return mixed
  */
  function upload($key)
  {
    return value($_FILES, $key, array());
  }


  /**
  * Client address
  *
  * @return string
  */
  function address()
  {
    return is_callable('gethostbyaddr') ? gethostbyaddr(request::remote_ip()) : request::remote_ip();
  }


  /**
  * Remote port
  *
  * @return integer
  */
  function port()
  {
    return (int) server('REMOTE_PORT');
  }


  /**
  * User agent
  *
  * @return mixed
  */
  function agent()
  {
    return server('HTTP_USER_AGENT');
  }


  /**
  * HTTP method
  *
  * @return string
  */
  function method()
  {
    return server('REQUEST_METHOD');
  }


  /**
  * HTTP referer
  *
  * @param  string Valor por defecto
  * @return mixed
  */
  function referer($or = FALSE)
  {
    return server('HTTP_REFERER', $or);
  }


  /**
  * Common client IP
  *
  * @param  string Default value
  * @return string
  */
  function remote_ip($or = FALSE)
  {
    return server('HTTP_X_FORWARDED_FOR', server('HTTP_CLIENT_IP', server('REMOTE_ADDR', $or)));
  }


  /**
   * Is application root?
   *
   * @return boolean
   */
  function is_root()
  {
    return URI === '/';
  }


  /**
   * Is POST request?
   *
   * @return boolean
   */
  function is_post()
  {
    return request::method() === POST;
  }


  /**
   * Is GET request?
   *
   * @return boolean
   */
  function is_get()
  {
    return request::method() === GET;
  }


  /**
   * Is PUT request?
   *
   * @return boolean
   */
  function is_put()
  {
    return request::method() === PUT;
  }


  /**
   * Is DELETE request?
   *
   * @return boolean
   */
  function is_delete()
  {
    return request::method() === DELETE;
  }


  /**
   * There are files uploaded?
   *
   * @param  string  Key or name
   * @return boolean
   */
  function is_upload($key = NULL)
  {
    if (func_num_args() == 0)
    {
      return sizeof($_FILES) > 0;
    }


    $test = value($_FILES, $key);

    if ( ! empty($test['name'][0]) && $test['error'][0] == 0)
    {
      return TRUE;
    }
    elseif (is_array($test) && $test['error'] == 0)
    {
      return TRUE;
    }

    return FALSE;
  }


  /**
   * Is ajax maded request?
   *
   * @return boolean
   */
  function is_ajax()
  {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']))
    {
      return FALSE;
    }

    return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
  }


  /**
   * Is CSRF-free request valid?
   *
   * @staticvar string  Token
   * @return    boolean
   */
  function is_safe()
  {
    static $check = NULL,
           $_token = NULL;


    if (is_null($check))
    {
      global $_PUT; //FIX


      $check = ! empty($_SESSION['--csrf-token']) ? $_SESSION['--csrf-token'] : FALSE;

      if ($_token = value($_POST, '_token', value($_PUT, '_token')))
      {//FIX
        unset($_POST['_token']);
      }
    }


    global $_PUT;//FIX

    @list($old_time, $old_token) = explode(' ', $check);
    @list($new_time, $new_token) = explode(' ', $_token);

    if (((time() - $old_time) < 720) && ($old_token === $new_token))
    {// TODO: must be configurable?
      return TRUE;
    }
    return FALSE;
  }

}



/**
   *
   *
   *
   * @return void
   */
request::implement('dispatch', function(array $params = array())
{
  if (empty($params['to']) OR
   ! (is_callable($params['to']) OR
      is_file($params['to']) OR
      is_url($params['to'])))
  {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }


  @params(array_merge($params['defaults'], $params['matches']));

  dispatch($params);
});


/* EOF: ./lib/tetl/server/request.php */
