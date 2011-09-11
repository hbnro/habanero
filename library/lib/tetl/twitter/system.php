<?php

/**
 * Twitter API
 */

class twitter extends prototype
{

  /**#@+
   * @ignore
   */

  // oauth resource
  private static $req = NULL;

  // connection status
  private static $connected = NULL;

  // username
  private static $screen_name = '';

  // uid
  private static $user_id = -1;

  // profile
  private static $data = array();

  // API urls
  private static $request_token_url = 'http://twitter.com/oauth/request_token';
  private static $access_token_url = 'http://twitter.com/oauth/access_token';
  private static $authorize_url = 'http://twitter.com/oauth/authorize';
  private static $api_url = 'http://api.twitter.com/1/';

  // defaults
  private static $defs = array(
                    'consumer_key' => '',
                    'consumer_secret' => '',
                    'token' => '',
                    'token_secret' => '',
                  );

  /**#@-*/



  /**
   * Set configuration
   *
   * @param  mixed Key|Hash
   * @param  mixed Value
   * @return void
   */
  final public static function setup($key, $value = '')
  {
    if (is_assoc($key))
    {
      static::$defs = array_merge($key, static::$defs);
    }
    elseif (array_key_exists($key, static::$defs))
    {
      static::$defs[$key] = $value;
    }
  }


  /**
   * Retrieve user credentials
   *
   * @return array
   */
  final public static function credentials()
  {
    if ( ! static::$data)
    {
      static::$data = static::api_call('account/verify_credentials');
    }
    return static::$data;
  }


  /**
   * Retrieve user name
   *
   * @return string
   */
  final public static function screen_name()
  {
    return static::$screen_name;
  }


  /**
   * Retrieve user id
   *
   * @return string
   */
  final public static function user_id()
  {
    return static::$user_id;
  }


  /**
   * There is a connection?
   *
   * @return boolean
   */
  final public static function is_logged()
  {
    if (is_null(static::$connected))
    {
      extract(static::$defs);

      static::$req = oauth_init($consumer_key, $consumer_secret, $token, $token_secret);

      if ($token = request::get('oauth_token'))
      {
        oauth_set(static::$req, $token);
        parse_str(oauth_exec(static::$req, static::$access_token_url), $test);
        session('twitter_auth', $test);

        oauth_set(static::$req, $test['oauth_token'], $test['oauth_token_secret']);
      }
      else
      {
        $test = session('twitter_auth');
      }


      if ( ! empty($test['oauth_token']) && ! empty($test['oauth_token']))
      {
        oauth_set(static::$req, $test['oauth_token'], $test['oauth_token_secret']);
      }

      ! empty($test['screen_name']) && static::$screen_name = $test['screen_name'];
      ! empty($test['user_id']) && static::$user_id = (string) $test['user_id'];

      static::$connected = static::$user_id > 0;
    }
    return static::$connected;
  }


  /**
   * Finalize session
   *
   * @return void
   */
  final public static function logout()
  {
    session('twitter_auth', NULL);
  }


  /**
   * Retrieve authorization URL
   *
   * @return mixed
   */
  final public static function authorization_url()
  {
    parse_str(oauth_exec(static::$req, static::$request_token_url), $test);

    if ( ! empty($test['oauth_token']))
    {
      return static::$authorize_url . '?oauth_token=' . $test['oauth_token'];
    }
  }


  /**
   * Execute API call
   *
   * @param  string Endpoint URL
   * @param  array  Request vars
   * @param  string Method
   * @return mixed
   */
  final public static function api_call($url, array $vars = array(), $method = GET)
  {
    if (static::is_logged())
    {
      $url  = ! is_url($url) ? rtrim(static::$api_url, '/') . "/$url.json" : $url;
      $test = oauth_exec(static::$req, $url, $vars, $method, TRUE);

      if (is_false(strpos($test, '"error"')))
      {
        $test = preg_replace('/(\w+)":(\d+)/', '\\1":"\\2"', $test);

        return json_decode($test);
      }
    }
  }

  /**
   * Primitive formatting links
   *
   * @link    http://www.snipe.net/2009/09/php-twitter-clickable-links/
   * @param     string Input string
   * @staticvar array  Replacements
   * @return    string
   */
  final public static function linkify($text)
  {
    static $set = array(// TODO: better unicode support?
              '/(\w{3,5}:\/\/([-\w\.]+)+(d+)?(\/([\w\/_\.]*(\?\S+)?)?)?)/' => '<a href="\\1">\\1</a>',
              '/(?<!\w)#([\wñáéíóú]+)(?=\b)/iu' => '<a href="http://twitter.com/search?q=%23\\1">#\\1</a>',
              '/(?<!\w)@(\w+)(?=\b)/u' => '<a href="http://twitter.com/\\1">@\\1</a>',
            );


    $text = preg_replace(array_keys($set), $set, $text);

    return $text;
  }


  /**
   * Current client status
   *
   * @return mixed
   */
  final public static function status_limit()
  {
    return static::api_call('account/rate_limit_status');
  }


  /**
   * Searching
   *
   * @param  string  Input string
   * @param  integer Limit
   * @return mixed
   */
  final public static function search_by($text, $limit = 20)
  {
    $limit > 0 && $data['rpp'] = $limit;

    $data['q'] = $text;

    return static::api_call('http://search.twitter.com/search.json', $data);
  }


  /**
   * Handle dynamically
   *
   * @param  string Method
   * @param  array  Arguments
   * @return mixed
   */
  final public static function missing($method, $arguments)
  {
    $type   = GET;
    $data   = array();
    $test   = array_pop($arguments);
    $params = array_pop($arguments);

    is_assoc($params) ? $data = $params : $params && $arguments []= $params;

    if (is_assoc($test))
    {
      $data = $test;
    }
    elseif ($test === POST)
    {
      $type = $test;
    }
    else
    {
      $test && $arguments []= $test;
    }


    $extra = join('/', $arguments);
    $url   = $method . ($extra ? "/$extra" : '');

    return static::api_call($url, $data, $type);
  }

}

/* EOF: ./lib/tetl/twitter/system.php */
