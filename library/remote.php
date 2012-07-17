<?php

/**
 * Remote utilities
 */

if ( ! is_callable('fsockopen')) {
  raise(ln('extension_missing', array('name' => 'Sockets')));
}

class remote extends prototype
{

  /**
   * Make post requests
   *
   * @link   http://www.php.net/manual/en/function.fsockopen.php#39868
   * @param  string Request location
   * @param  string Request params
   * @param  string Upload files
   * @param  mixed  GET|PUT|POST|DELETE
   * @return mixed
   */
  function to($url, array $args = array(), array $files = array(), $method = 'POST')
  {
    if ( ! is_url($url)) {
      return FALSE;
    }


    $test  = @parse_url($url);

    $path  = ! empty($test['path']) ? $test['path'] : '/';
    $path .= ! empty($test['query']) ? '?' . $test['query'] : '';
    $port  = ! empty($test['port']) ? $test['port'] : 80;

    $resource = fsockopen($test['host'], $test['scheme'] !== 'https' ? $port : 433);

    if ( ! is_resource($resource)) {
      return FALSE;
    }

    $bound  = uniqid('--post-boundary');
    $output = "--$bound";

    if ( ! empty($args)) {
      foreach ($args as $name => $value) {
        $output .= "\r\nContent-Disposition: form-data; name=\"" . slug($name) . '"';
        $output .= "\r\n\r\n$value\r\n--$bound";
      }
    }

    // upload
    if ( ! empty($files)) {
      foreach ((array) $files as $name => $set) {
        if ( ! is_file($set[0]) && ! is_url($set[0])) {
          continue;
        }

        $data = read($set[0]);
        $name = preg_replace('/[^\w.]/', '', is_numeric($name) ? $set[0] : $name);

        $output .= "\r\nContent-Disposition: form-data; name=\"" . $name . '"; filename="' . $set[0] . '"';
        $output .= "\r\nContent-Type: " . $set[1];
        $output .= "\r\n\r\n$data\r\n--$bound";
      }
    }

    $output .= "--\r\n\r\n";

    fputs($resource, "$method $path HTTP/1.0\r\n");

    fputs($resource, "Content-Type: multipart/form-data; boundary=$bound\r\n");
    fputs($resource, 'Content-Length: ' . strlen($output) . "\r\n");
    fputs($resource, "Connection: close\r\n\r\n");
    fputs($resource, "$output\r\n");


    $output = '';

    while( ! feof($resource)) {
      $output .= fgets($resource, 4096);
    }
    return $output;
  }


  /**
   * Dynamic calls
   *
   * @param  string Method
   * @param  array  Arguments
   * @return string
   */
  final public static function missing($method, $arguments) {
    static $allow = array('get', 'put', 'post', 'delete');


    if (in_array($method, $allow)) {
      @list($url, $args, $files) = $arguments;
      return static::to($url, $args ?: array(), $files ?: array(), strtoupper($method));
    }

    raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
  }

}

/* EOF: ./library/remote.php */
