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
      self::$defs = array_merge($key, self::$defs);
    }
    elseif (array_key_exists($key, self::$defs))
    {
      self::$defs[$key] = $value;
    }
  }


  /**
   * Retrieve user credentials
   *
   * @return array
   */
  final public static function credentials()
  {
    if ( ! self::$data)
    {
      self::$data = self::api_call('account/verify_credentials');
    }
    return self::$data;
  }


  /**
   * Retrieve user name
   *
   * @return string
   */
  final public static function screen_name()
  {
    return self::$screen_name;
  }


  /**
   * Retrieve user id
   *
   * @return string
   */
  final public static function user_id()
  {
    return self::$user_id;
  }


  /**
   * There is a connection?
   *
   * @return boolean
   */
  final public static function is_logged()
  {
    if (is_null(self::$connected))
    {
      extract(self::$defs);

      self::$req = oauth_init($consumer_key, $consumer_secret, $token, $token_secret);

      if ($token = request::get('oauth_token'))
      {
        oauth_set(self::$req, $token);
        parse_str(oauth_exec(self::$req, self::$access_token_url), $test);
        session('twitter_auth', $test);

        oauth_set(self::$req, $test['oauth_token'], $test['oauth_token_secret']);
      }
      else
      {
        $test = session('twitter_auth');
      }


      if ( ! empty($test['oauth_token']) && ! empty($test['oauth_token']))
      {
        oauth_set(self::$req, $test['oauth_token'], $test['oauth_token_secret']);
      }

      ! empty($test['screen_name']) && self::$screen_name = $test['screen_name'];
      ! empty($test['user_id']) && self::$user_id = (string) $test['user_id'];

      self::$connected = self::$user_id > 0;
    }
    return self::$connected;
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
    parse_str(oauth_exec(self::$req, self::$request_token_url), $test);

    if ( ! empty($test['oauth_token']))
    {
      return self::$authorize_url . '?oauth_token=' . $test['oauth_token'];
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
    if (self::is_logged())
    {
      $url  = ! is_url($url) ? rtrim(self::$api_url, '/') . "/$url.json" : $url;
      $test = oauth_exec(self::$req, $url, $vars, $method, TRUE);

      if ( ! preg_match('/"error":"(.+?)"/', $test, $match))
      {
        $test = preg_replace('/(\w+)":(\d+)/', '\\1":"\\2"', $test);

        return json_decode($test);
      }
    }
  }

  /**
   * Formatear enlaces para Twitter
   *
   * @link    http://www.snipe.net/2009/09/php-twitter-clickable-links/
   * @param   string $text Cadena
   * @staticvar array  Coleccion de expresiones
   * @return  string
   */
  final public static function linkify($text)
  {
    static $set = array(// TODO: unicode support?
              '/(\w{3,5}:\/\/([-\w\.]+)+(d+)?(\/([\w\/_\.]*(\?\S+)?)?)?)/' => '<a href="\\1">\\1</a>',
              '/(?<!\w)#([\wñáéíóú]+)(?=\b)/iu' => '<a href="http://twitter.com/search?q=%23\\1">#\\1</a>',
              '/(?<!\w)@(\w+)(?=\b)/u' => '<a href="http://twitter.com/\\1">@\\1</a>',
            );


    $text = preg_replace(array_keys($set), $set, $text);

    return $text;
  }


  /**
   * Actualizar perfil
   *
   * @param  array $set Opciones|Arreglo
   * @return mixed
   */
  final public static function profile(array $set = array())
  {
    return self::api_call('account/update_profile', $set, POST);
  }


  /**
   * Estadisticas
   *
   * @return mixed
   */
  final public static function status_limit()
  {
    return self::api_call('account/rate_limit_status');
  }


  /**
   * Estado individual
   *
   * @param  integer $id Identificador
   * @return mixed
   */
  final public static function status_show($id)
  {
    return self::api_call("statuses/show/$id");
  }


  /**
   * Menciones
   *
   * @param  integer $page Pagina
   * @param  integer $since Offset
   * @return mixed
   */
  final public static function status_mentions($page = 1, $since = 0)
  {
    $data['page'] = (int) $page;

    $since > 0 && $data['since_id'] = $since;

    return self::api_call('statuses/mentions', $data);
  }


  /**
   * Nueva actualizacion
   *
   * @param  string $text Cadena|Mensaje de estado
   * @return mixed
   */
  final public static function status_update($text)
  {
    return self::api_call('statuses/update', array(
          'status' => $text,
      ), POST);
  }


  /**
   * Eliminar estado
   *
   * @param  integer $id Identificador
   * @return mixed
   */
  final public static function status_destroy($id)
  {
    return self::api_call("statuses/destroy/$id", array(), POST);
  }


  /**
   * Informacion del usuario
   *
   * @param  string $user Nombre del usuario
   * @return mixed
   */
  final public static function user_show($user = '')
  {
    $user = $user ?: self::$screen_name;

    return self::api_call('users/show/' . urlencode($user));
  }


  /**
   * Seguir usuario
   *
   * @param  string $user Nombre del usuario
   * @return mixed
   */
  final public static function user_follow($user)
  {
    return self::api_call('friendships/create/' . urlencode($user), array(
        'follow' => 'true',
    ), POST);
  }


   /**
   * Dejar de seguir usuario
   *
   * @param  string $user Nombre del usuario
   * @return mixed
   */
  final public static function user_unfollow($user)
  {
    return self::api_call('friendships/destroy/' . urlencode($user), array(), POST);
  }


  /**
   * Lista de amigos (IDs)
   *
   * @param  string $user Nombre del usuario
   * @return mixed
   */
  final public static function friends_ids($user = '')
  {
    $out  = 'friends/ids';
    $out .= $user ? '/' . urlencode($user) : '';

    return self::api_call($out);
  }


  /**
   * Lista de seguidores (IDs)
   *
   * @param  string $user Nombre del usuario
   * @return void
   */
  final public static function followers_ids($user = '')
  {
    $out  = 'followers/ids';
    $out .= $user ? '/' . urlencode($user) : '';

    return self::api_call($out);
  }


  /**
   * Tendencias o #hash
   *
   * @return mixed
   */
  final public static function trends()
  {
    return self::api_call('trends');
  }


  /**
   * Buscar en la linea de tiempo
   *
   * @param  string  $text  Cadena a buscar
   * @param  integer $limit Limitar resultados
   * @return mixed
   */
  final public static function search_timeline($text, $limit = 20)
  {
    $limit > 0 && $data['rpp'] = $limit;

    $data['q'] = $text;

    return self::api_call('http://search.twitter.com/search.json', $data);
  }


  /**
   * Linea publica de tiempo
   *
   * @param  integer $since Offset
   * @return mixed
   */
  final public static function public_timeline($since = 0)
  {
    $data = array();

    $since && $data['since_id'] = $since;

    return self::api_call('statuses/public_timeline', $data);
  }


  /**
   * Linea de tiempo (amigos)
   *
   * @param  integer $since Offset
   * @param  string  $user  Nombre del usuario
   * @return mixed
   */
  final public static function friends_timeline($since = 0, $user = '')
  {
    $data = array();

    $since > 0 && $data['since'] = $since;

    $url  = 'statuses/friends_timeline';
    $url .= $user ? '/' . urlencode($user) : '';

    return self::api_call($url, $data);
  }


  /**
   * Linea de tiempo (home)
   *
   * @param  array Options hash
   * @return mixed
   */
  final public static function home_timeline(array $params = array())
  {
    return self::api_call('statuses/home_timeline', $params);
  }


  /**
   * Linea de tiempo (usuario)
   *
   * @param  integer $count Elementos
   * @param  integer $since Offset
   * @param  string  $user  Nombre del usuario
   * @return mixed
   */
  final public static function user_timeline($count = 20, $since = 0, $user = '')
  {
    $data = array();
    $data['count'] = (int) $count;

    $since > 0 && $data['since'] = $since;

    $url  = 'statuses/user_timeline';
    $url .= $user ? '/' . urlencode($user) : '';

    return self::api_call($url, $data);
  }


  /**
   * Mensajes directos
   *
   * @param  integer $since Offset
   * @param  integer $page  Pagina
   * @return mixed
   */
  final public static function direct_messages($since = 0, $page = 1)
  {
    $data['page'] = $page;

    $since > 0 && $data['since_id'] = $since;

    return self::api_call('direct_messages', $data);
  }


  /**
   * Mensajes directos enviados
   *
   * @param  integer $since Offset
   * @param  integer $page  Pagina
   * @return mixed
   */
  final public static function direct_messages_sent($since = 0, $page = 1)
  {
    $data['page'] = $page;

    $since > 0 && $data['since_id'] = $since;

    return self::api_call('direct_messages', $data);
  }


  /**
   * Nuevo mensaje directo
   *
   * @param  string $user Nombre del usuario
   * @param  string $text Mensaje
   * @return mixed
   */
  final public static function direct_message_new($user, $text)
  {
    return self::api_call('direct_messages/new', array(
        'text' => $text,
        'user' => $user,
    ), POST);
  }


  /**
   * Eliminar mensaje directo
   *
   * @param  integer $id Identificador
   * @return mixed
   */
  final public static function direct_message_destroy($id)
  {
    return self::api_call("direct_messages/destroy/$id", array(), POST);
  }

}

/* EOF: ./lib/tetl/twitter/system.php */
