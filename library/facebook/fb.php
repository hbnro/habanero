<?php

/**
 * Facebook wrapper
 */

class fb extends prototype
{
  /**#@+
   * @ignore
   */

  //
  private static $me = NULL;

  //
  private static $self = NULL;

  //
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


  final public static function missing($method, $arguments)
  {
    if (method_exists(static::instance(), camelcase($method)))
    {
      return call_user_func_array(array(static::instance(), camelcase($method)), $arguments);
    }
  }

  final public static function instance()
  {
    if (is_null(static::$self))
    {
      static::$self = new Facebook(array(
        'appId' => static::option('api_key'),
        'secret' => static::option('api_secret'),
        'cookie' => TRUE,
      ));
    }
    return static::$self;
  }

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

  final public static function login_url() {
    return static::get_login_url(static::$defs['login_options']);
  }

  final public static function is_logged() {
    return !! static::$me;
  }

  final public static function is_canvas()
  {
    return ! empty(static::$defs['login_options']['canvas']);
  }

  final public static function me()
  {
    return static::$me;
  }
}

/* EOF: ./library/facebook/fb.php */
