<?php

/**
 * Basic OAuth library
 */

if ( ! function_exists('curl_init')) {
  raise(ln('extension_missing', array('name' => 'cURL')));
}

// TODO: should be a standalone helper?

/**
 * Encoding constants
 */
define('MD5', 'md5');
define('SHA1', 'sha1');
/**#@-*/

/**
 * Initialize OAuth object
 *
 * @param  string Consumer key
 * @param  string Consumer secret
 * @param  string Auth user token
 * @param  string Auth user token secret
 * @return mixed
 */
function oauth_init($consumer_key = '', $consumer_secret = '', $token = '', $token_secret = '') {
  $R = new stdClass;

  $R->info = array();
  $R->token = $token;
  $R->token_secret = $token_secret;
  $R->consumer_key = $consumer_key;
  $R->consumer_secret = $consumer_secret;

  return $R;
}


/**
 * Prepare OAuth request
 *
 * @param  mixed  OAuth object
 * @param  string Request URL
 * @param  array  Request vars
 * @param  string Method
 * @param  string SHA1|MD5
 * @return array
 */
function oauth_parse($request, $url, $vars = array(), $method = GET, $callback = SHA1) {// TODO: improve?
  $data['oauth_version'] = '1.0';
  $data['oauth_timestamp'] = time();
  $data['oauth_signature_method'] = strtoupper("hmac-$callback");
  $data['oauth_consumer_key'] = $request->consumer_key;
  $data['oauth_nonce'] = md5(uniqid(mt_rand(), TRUE));
  $data['oauth_token'] = $request->token;

  foreach ($vars as $key => $val) {
    $vars[$key] = $val;
  }

  $test = array_merge($data, $vars);
  uksort($test, 'strcmp');

  $data['oauth_signature'] = oauth_encode(oauth_sign($request, $url, $test, $method, $callback));

  return array(
    'request' => $vars,
    'oauth' => $data,
  );
}


/**
 * Execute OAuth request
 *
 * @param  mixed  OAuth Object
 * @param  string Request url
 * @param  array  Request vars
 * @param  string Method
 * @return mixed
 */
function oauth_exec($request, $url, $vars = array(), $method = GET) {
  // normalize URL
  $parts  = @parse_url($url);
  $scheme = strtolower($parts['scheme']);
  $host   = strtolower($parts['host']);
  $port   = ! empty($parts['port']) ? (int) $parts['port'] : 80;
  $url    = "$scheme://$host";

  ($port > 0) && (($scheme === 'http') && ($port !== 80)) OR (($scheme === 'https') && ($port !== 443)) && $out .= ":$port";

  $url .= $parts['path'];

  @parse_str($parts['query'], $test);

  ! empty($test) && $vars = array_merge($vars, $test);

  $vars     = oauth_parse($request, $url, $vars, $method);
  $query    = str_replace('+', '%20', http_build_query($vars['request'], NULL, '&'));
  $headers  = array('Expect:');
  $resource = curl_init();


  // define method
  switch ($method) {
    case POST; // TODO: manage @uploads?
      ! empty($query) && curl_setopt($resource, CURLOPT_POSTFIELDS, trim($query, '='));

      curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($resource, CURLOPT_POST, TRUE);
    break;
    default;
      ! empty($query) && $url .= '?' . trim($query, '=');

      $method <> GET && curl_setopt($resource, CURLOPT_CUSTOMREQUEST, $method);
    break;
  }

  // request headers
  $oauth     = 'Authorization: OAuth realm="' . $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '"';
  $oauth    .= str_replace(' ', ',', attrs($vars['oauth']));
  $headers []= $oauth;

  curl_setopt($resource, CURLOPT_HTTPHEADER, $headers);

  // execute!
  curl_setopt($resource, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($resource, CURLOPT_URL, $url);

  $out = curl_exec($resource);

  $request->info = curl_getinfo($resource);
  $request->info['content_out'] = $out;

  return $out;
}


/**
 * Sign OAuth request
 *
 * @param  mixed  OAuth object
 * @param  string Request url
 * @param  array  Request vars
 * @param  string Method
 * @param  string SHA1|MD5
 * @return string
 */
function oauth_sign($request, $url, $vars = array(), $method = GET, $callback = SHA1) {
  $key  = oauth_encode($request->consumer_secret) . '&' . oauth_encode($request->token_secret);
  $old  = oauth_encode(str_replace('+', '%20', http_build_query($vars, NULL, '&')));
  $test = sprintf('%s&%s&%s', $method, oauth_encode($url), $old);

  if (function_exists('hash_hmac')) {
    $test = hash_hmac($callback, $test, $key, TRUE);
  }
  else
  {//TODO: fallback is still needed?
    if (strlen($key) > 64) {
      $key = pack('H*', $callback($key));
    }

    $key  = str_pad($key, 64, chr(0x00));
    $lpad = str_repeat(chr(0x36), 64);
    $rpad = str_repeat(chr(0x5c), 64);

    $hmac = pack('H*', $callback(($key ^ $lpad) . $test));
    $test = pack('H*', $callback(($key ^ $rpad) . $hmac));
  }
  return base64_encode($test);
}


/**
 * RFC3986 encoding
 *
 * @param  mixed Input string|Array
 * @return mixed
 */
function oauth_encode($test) {
  if (is_scalar($test)) {
    $test = str_replace('%7E', '~', rawurlencode($test));
  }
  elseif (is_array($test)) {
    $test = array_map(__FUNCTION__, $test);
  }
  return $test;
}


/**
 * Assign tokens
 *
 * @param  mixed  OAuth object
 * @param  string Token
 * @param  string Token secret
 * @return void
 */
function oauth_set($request, $token, $secret = NULL) {
  $request->token = $token;
  $request->token_secret = $secret;
}

/* EOF: ./library/tetl/twitter/oauth.php */
