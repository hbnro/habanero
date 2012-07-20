<?php

/**
 * Filesystem functions library
 */

/**#@+
 * Listing options
 */
define('DIR_RECURSIVE', 1);
define('DIR_EMPTY', 2);
define('DIR_SORT', 4);
/**#@-*/


/**
 * Remove a bunch of files and/or directories
 *
 * @param  string  Directory
 * @param  string  Simple filter
 * @param  boolean DIR_RECURSIVE|DIR_EMPTY
 * @return boolean
 */
function unfile($path, $filter = '*', $options = FALSE) {
  if (is_dir($path)) {
    $empty = ((int) $options & DIR_EMPTY) == 0 ? FALSE : TRUE;
    $test  = array_reverse(dir2arr($path, $filter, $options | DIR_SORT));

    foreach ($test as $one) {
      is_file($one) && @unlink($one);
      is_dir($one) && $empty && @rmdir($one);
    }

    $empty && @rmdir($path);

    return TRUE;
  }
}


/**
 * Retrieve the specified directory size
 *
 * @param  string  Directory
 * @param  boolean Employ recursively?
 * @return mixed
 */
function dirsize($of, $recursive = FALSE) {
  if (is_dir($of)) {
    $length = 0;
    $test   = array_filter(dir2arr($of, '*', $recursive ? DIR_RECURSIVE : 0), 'is_file');

    foreach ($test as $old) {
      $length += filesize($old);
    }

    return $length;
  }
}


/**
 * Retrieve a file collection from given path
 *
 * @param     string Directory
 * @param     mixed  Simple filter|Function callback
 * @param     mixed  DIR_RECURSIVE|DIR_SORT
 * @staticvar mixed  Empty paths
 * @staticvar mixed  Function callback
 * @return    mixed
*/
function dir2arr($from, $filter = '*', $options = 0) {
  $recursive = ((int) $options & DIR_RECURSIVE) == 0 ? FALSE : TRUE;
  $sort = ((int) $options & DIR_SORT) == 0 ? FALSE : TRUE;

  if ( ! $from && (($dir = dirname($filter)) <> '.')) {
    ($dir === DS) && $dir = '';

    return dir2arr($dir, basename($filter), $options);
  }


  $paths = glob($from.DS.'*', GLOB_ONLYDIR | ( ! $sort ? GLOB_NOSORT : 0));
  $files = glob($from.DS.$filter, GLOB_MARK | GLOB_BRACE | ( ! $sort ? GLOB_NOSORT : 0));

  if ($recursive) {
    foreach ($paths as $one) {
      $test  = dir2arr($one, $filter, $options);
      $files = array_merge($files, $test);
    }
  }

  return $files;
}


/**
 * Copy entire directories and files
 *
 * @param  string Origin path
 * @param  string Final path
 * @param  string Simple filter
 * @param  string Employ recursively?
 * @return mixed
 */
function cpfiles($from, $to, $filter = '*', $recursive = FALSE) {
  if (is_dir($from)) {
    ! is_dir($to) && mkpath($to);

    $options = ($recursive ? DIR_RECURSIVE : 0) | DIR_EMPTY;
    $test    = array_reverse(dir2arr($from, $filter, $options | DIR_SORT));

    foreach ($test as $file) {
      $new = str_replace(realpath($from), $to, $file);

      if ( ! file_exists($new)) {
        is_file($file) && copy($file, $new) && chmod($new, 0644);
        is_dir($file) && mkdir($new, 0755);
      }
    }
  }
}


/**
 * Create a directory recursively
 *
 * @param  string Path to create
 * @param  octal  Individual permissions
 * @return string
 */
function mkpath($dir, $perms = 0755) {
  $path  = strtr($dir, '\\/', DS.DS);

  if ( ! is_file($path) && ! is_dir($path)) {
    $test = explode(DS, $path);
    $path = '';

    foreach ($test as $one) {
      $path .= $one.DS;

      if (($path <> DS) && ! @is_dir($path)) {
        // http://www.php.net/manual/es/function.mkdir.php#96990
        mkdir(rtrim($path, DS), $perms);
        chmod($path, $perms);
      }
    }
  }
  return rtrim($path, DS);
}


/**
 * Find files through given path
 *
 * @param  string  Directory
 * @param  string  Simple filter
 * @param  boolean Employ recursively?
 * @param  integer Especific index
 * @return mixed
 */
function findfile($path, $filter = '*', $recursive = FALSE, $index = 0) {
  if (is_dir($path)) {
    $recursive = $recursive ? DIR_RECURSIVE : 0;
    $output    = array_filter(dir2arr($path, $filter, $recursive | DIR_SORT), 'is_file');

    if ($index > 0) {
      return isset($output[$index - 1]) ? $output[$index - 1] : FALSE;
    }

    return $output;
  }
}


/**
 * Atempt to read given file or URL
 *
 * @param  string Filepath|URL
 * @return mixed
 */
function read($path) {
  $output = FALSE;

  if (is_url($path)) {
    $test = @parse_url($path);

    $port  = ! empty($test['port']) ? $test['port'] : 80;
    $guri  = ! empty($test['path']) ? $test['path'] : '/';
    $guri .= ! empty($test['query']) ? "?$test[query]" : '';

    //$referer = server(TRUE, $_SERVER['REQUEST_URI'], TRUE);
    $agent   = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';


    if ($test['scheme'] === 'https') {
      $port = 433;
    }

    if (ini_get('allow_url_fopen')) {
      $output = file_get_contents($path);
    } elseif (function_exists('curl_init')) {
      $resource = curl_init();

      curl_setopt($resource, CURLOPT_URL, "$test[scheme]://$test[host]$guri");
      //curl_setopt($resource, CURLOPT_REFERER, $referer);
      curl_setopt($resource, CURLOPT_FAILONERROR, 1);
      curl_setopt($resource, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($resource, CURLOPT_PORT, $port);
      curl_setopt($resource, CURLOPT_TIMEOUT, 90);
      curl_setopt($resource, CURLOPT_USERAGENT, $agent);

      $output = curl_exec($resource);
    } elseif (function_exists('fsockopen')) {
      $resource = @fsockopen($test['host'], $port, $errno, $errstr, 90);

      if (is_resource($resource)) {
        fputs($resource, "GET $guri HTTP/1.0\r\n");
        fputs($resource, "Host: $test[host]\r\n");
        fputs($resource, "User-Agent: $agent\r\n");
        fputs($resource, "Accept: */*\r\n");
        fputs($resource, "Accept-Language: en-us,en;q=0.5\r\n");
        fputs($resource, "Accept-Charset: iso-8859-1,utf-8;q=0.7,*;q=0.7\r\n");
        fputs($resource, "Keep-Alive: 300\r\n");
        fputs($resource, "Connection: Keep-Alive\r\n");
        //fputs($resource, "Referer: $referer\r\n\r\n");

        $end = FALSE;

        while ( ! feof($resource)) {// http://www.php.net/manual/en/function.fsockopen.php#87144
          $tmp = @fgets($resource, 128);

          if ($tmp === "\r\n") {
            $end = TRUE;
          }

          if ($end) {
            $output .= $tmp;
          }
        }
        fclose($resource);
      }
    }
  } elseif (is_file($path)) {
    $output = file_get_contents($path);
  }


  if (substr($output, 0, 3) === "\xEF\xBB\xBF") {// TODO: possible BOM issue?
    $output = substr($output, 3);
  }

  return $output;
}


/**
 * Atempt to write a local file
 *
 * @param  string  Filepath
 * @param  string  File content
 * @param  integer Read/Write access type
 * @param  octal   Individual permissions
 * @return boolean
 */
function write($to, $content = '', $type = 0, $perms = 0644) {
  $output = FALSE;

  if (is_dir(dirname($to))) {
    if ( ! is_file($to)) {
      touch($to);
      chmod($to, $perms);
    }


    $old  = $type < 0 ? read($to) : '';
    $mode = $type > 0 ? 'a+b' : 'w+b';

    if ($tmp = fopen($to, is_string($type) ? $type : $mode)) {
      if (flock($tmp, LOCK_EX)) {
        fwrite($tmp, $content . $old);
        flock($tmp, LOCK_UN);
        $output = TRUE;
      }
      @fclose($tmp);
    }
  }

  return $output;
}


/**
 * Filename extension
 *
 * @param  string  Filename
 * @param  boolean Prefix dot?
 * @return mixed
 */
function ext($from, $dot = FALSE) {
  if (substr_count($from, '.') > 0) {
    $from = substr($from, strrpos($from, '.'));

    if ( ! $dot) {
      $from = substr($from, 1);
    }

    $output = explode('?', $from);

    return basename($output[0]);
  }

  return FALSE;
}


/**
 * Remove filename extension
 *
 * @param  string  Filename
 * @param  boolean Remove path?
 * @return string
 */
function extn($from, $base = FALSE) {
  if (($offset = strrpos($from, '.')) !== FALSE) {
    $from = substr($from, 0, $offset);
    $from = $base ? basename($from) : $from;
  }

  return $from;
}


/**
 * Bytes in human readable format
 *
 * @param     integer Length
 * @param     string  Format
 * @param     boolean Emply lowercase?
 * @staticvar array   Units array
 * @return    string
 */
function fmtsize($of = 0, $text = '%d %s', $lower = FALSE) {
  static $test = array('', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');


  $key  = 0;

  while ($of >= 1024) {
    $of   = $of / 1024;
    $key += 1;
  }

  $unit  = preg_replace('/^iB/', 'Bi', "$test[$key]iB");//FIX
  $unit  = $lower ? strtolower($unit) : $unit;

  $output = strtr($text, array(
    '%d' => floor($of),
    '%s' => $unit,
  ));

  return $output;
}


/**
 * Primitive MIME type
 *
 * @param     string Filename
 * @staticvar array  Types hash
 * @return    string
 */
function mime($of) {
  static $types = NULL;


  if (is_null($types)) {
    /**
     * @ignore
     */
    $types = include LIB.DS.'assets'.DS.'scripts'.DS.'mime_types'.EXT;
  }


  if (is_file($of)) {
    if (is_callable('finfo_open')) {
      return finfo_file(finfo_open(FILEINFO_MIME), $of);
    } elseif (is_callable('mime_content_type')) {
      return mime_content_type($of);
    }

    $data = read($of);

    if ( ! strncmp($data, "\xff\xd8", 2)) {
      return 'image/jpeg';
    } elseif ( ! strncmp($data, "\x89PNG", 4)) {
      return 'image/png';
    } elseif ( ! strncmp($data, "GIF", 3)) {
      return 'image/gif';
    }
  }


  $ext = ext($of) ?: $of;

  return ! empty($types[$ext]) ? $types[$ext] : 'application/octet-stream';
}

/* EOF: ./framework/include/filesystem.php */
