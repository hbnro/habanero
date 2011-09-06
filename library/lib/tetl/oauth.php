<?php

/**
 * Basic oAuth library
 */

if ( ! function_exists('curl_init'))
{
  raise(ln('extension_missing', array('name' => 'cURL')));
}

/**
 * Encoding constants
 */
defined('MD5') OR define('MD5', 'md5');
defined('SHA1') OR define('SHA1', 'sha1');
/**#@-*/


// TODO: should be a object instead of?


/**
 * Inicializar objeto OAuth
 *
 * @param  string $consumer_key  ConsumerKey
 * @param  string $consumer_secret ConsumerSecret
 * @param  string $token       AuthUserToken
 * @param  string $token_secret  AuthUserTokenSecret
 * @return mixed
 */
function oauth_init($consumer_key = '', $consumer_secret = '', $token = '', $token_secret = '')
{//--
  $R = new stdClass;

  $R->info = array();
  $R->token = $token;
  $R->token_secret = $token_secret;
  $R->consumer_key = $consumer_key;
  $R->consumer_secret = $consumer_secret;

  return $R;
}


/**
 * Preparar peticion OAuth
 *
 * @param  mixed  $request  Objeto OAuth
 * @param  string $url    URL de la peticion
 * @param  array  $vars   Argumentos de la peticion
 * @param  string $method   Metodo para la peticion
 * @param  string $callback Callback firma|SHA1|MD5
 * @param  string $version  Version OAuth
 * @return array
 */
function oauth_parse($request, $url, $vars = array(), $method = GET, $callback = SHA1, $version = '1.0')
{
  $data['oauth_version'] = $version;
  $data['oauth_timestamp'] = time();
  $data['oauth_signature_method'] = strtoupper("hmac-$callback");
  $data['oauth_consumer_key'] = $request->consumer_key;
  $data['oauth_nonce'] = md5(uniqid(mt_rand(), TRUE));
  $data['oauth_token'] = $request->token;

  foreach ($vars as $key => $val) $vars[$key] = $val;
  $test = array_merge($data, $vars);
  uksort($test, 'strcmp');

  $data['oauth_signature'] = oauth_encode(oauth_sign($request, $url, $test, $method, $callback));
  return array(
  'request' => $vars,
  'oauth' => $data,
  );
}


/**
 * Ejecutar llamada OAuth
 *
 * @param  mixed  $request Objeto OAuth
 * @param  string $url   URL de la peticion
 * @param  array  $vars  Argumentos de la peticion
 * @param  string $method  Metodo para la peticion
 * @return mixed
 */
function oauth_exec($request, $url, $vars = array(), $method = GET)
{
  // + normalizar URL
  $parts = @parse_url($url);
  $scheme = strtolower($parts['scheme']);
  $host = strtolower($parts['host']);
  $port = ! empty($parts['port'])? (int) $parts['port']: 80;

  $url = "$scheme://$host";
  if ($port > 0 && ($scheme === 'http' && $port !== 80) ||
          ($scheme === 'https' && $port !== 443)) $out .= ":$port";

  $url .= $parts['path'];
  @parse_str($parts['query'], $test);
  if ( ! empty($test)) $vars = array_merge($vars, $test);

  $vars = oauth_parse($request, $url, $vars, $method);
  $query = str_replace('+', '%20', http_build_query($vars['request'], NULL, '&'));
  $headers = array('Expect:');
  $resource = curl_init();


  // + definir metodo
  switch ($method)
  {
  case POST; // TODO: manage @uploads?
    if ( ! empty($query)) curl_setopt($resource, CURLOPT_POSTFIELDS, trim($query, '='));
    #die($query);
    curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($resource, CURLOPT_POST, TRUE);
  break;
  default;
    if ( ! empty($query)) $url .= '?' . trim($query, '=');
    if ($method != GET) curl_setopt($resource, CURLOPT_CUSTOMREQUEST, $method);
  break;
  }

  // + generar cabeceras
  $oauth = 'Authorization: OAuth realm="' . $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '"';
  $oauth .= str_replace(' ', ',', attrs($vars['oauth']));
  $headers []= $oauth;

  curl_setopt($resource, CURLOPT_HTTPHEADER, $headers);

  // + ejecutar URL
  curl_setopt($resource, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($resource, CURLOPT_URL, $url);

  $out = curl_exec($resource);
  $request->info = curl_getinfo($resource);
  $request->info['content_out'] = $out;
  return $out;
}


/**
 * Firmar peticion OAuth
 *
 * @param  mixed  $request  Objeto OAuth
 * @param  string $url    URL de la peticion
 * @param  array  $vars   Argumentos de la peticion
 * @param  string $method   Metodo para la peticion
 * @param  string $callback Callback firma|SHA1|MD5
 * @return string
 */
function oauth_sign($request, $url, $vars = array(), $method = GET, $callback = SHA1)
{
  $key = oauth_encode($request->consumer_secret) . '&' . oauth_encode($request->token_secret);
  $old = oauth_encode(str_replace('+', '%20', http_build_query($vars, NULL, '&')));
  $test = sprintf('%s&%s&%s', //--
      $method,
      oauth_encode($url),
      $old);

  if (function_exists('hash_hmac')) $test = hash_hmac($callback, $test, $key, TRUE);
  else
  {//--
    if (strlen($key) > 64) $key = pack('H*', $callback($key));

    $key = str_pad($key, 64, chr(0x00));
    $ipad = str_repeat(chr(0x36), 64);
    $opad = str_repeat(chr(0x5c), 64);

    $hmac = pack('H*', $callback(($key ^$ipad) . $test));
    $test = pack('H*', $callback(($key ^$opad) . $hmac));
  }
  return base64_encode($test);
}


/**
 * Codificacion RFC3986
 *
 * @param  mixed $test Variable
 * @return mixed
 */
function oauth_encode($test)
{
  if (is_scalar($test)) return str_replace('%7E', '~', rawurlencode($test));
  elseif (is_array($test)) $test = array_map(__FUNCTION__, $test);
  return $test;
}


/**
 * Asignar tokens
 *
 * @param  mixed  $request Objeto OAuth
 * @param  string $token   Token
 * @param  string $secret  Secreto
 * @return void
 */
function oauth_set($request, $token, $secret = NULL)
{
  $request->token = $token;
  $request->token_secret = $secret;
}

/* EOF: ./lib/tetl/oauth.php */
