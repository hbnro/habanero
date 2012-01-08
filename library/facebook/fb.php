<?php

/**
 * Facebook wrapper
 */

class fb extends prototype
{
  /**#@+
   * @ignore
   */

  // credentials
  private static $me = NULL;

  // defaults
  protected static $defs = array(
                      'api_key' => '',
                      'api_secret' => '',
                      'login_options' => array(
                        'canvas' => 0,
                        'fbconnect' => 0,
                        'scope' => 'email',
                      ),
                    );

  /**#@-*/


  /**
   * Delegate mising methods
   *
   * @param  string Method
   * @param  array  Arguments
   * @return mixed
   */
  final public static function missing($method, $arguments)
  {
    static $instance = NULL;


    if (is_null($instance))
    {
      $instance = new Facebook(array(
        'appId' => static::option('api_key'),
        'secret' => static::option('api_secret'),
        'cookie' => TRUE,
      ));
    }

    if (method_exists($instance, camelcase($method)))
    {
      return call_user_func_array(array($instance, camelcase($method)), $arguments);
    }
  }


  /**
   * Initialize connection
   *
   * @return void
   */
  final public static function init()
  {
    if ( ! request::is_ssl() && ! request::is_local()) {
      redirect('https://' . $_SERVER['HTTP_HOST'] . server('REQUEST_URI'));
    }


    $test = headers_list();

    if (array_key_exists('X-Facebook-User', $test))
    {
      static::$me = (array) json_decode($test['X-Facebook-User']);
    }

    if ( ! static::$me)
    {
      try {
        static::$me = static::api('/me');
      } catch (FacebookApiException $e) {
      }
    }
  }


  /**
   * URL for access
   *
   * @return string
   */
  final public static function login_url() {
    return static::get_login_url(static::$defs['login_options']);
  }


  /**
   * Facebook session
   *
   * @return boolean
   */
  final public static function is_logged() {
    return !! static::$me;
  }


  /**
   * User credentials
   *
   * @return array
   */
  final public static function me()
  {
    return static::$me;
  }
}

/* EOF: ./library/facebook/fb.php */
