<?php

/**
 * Zip class
 */

class zip extends prototype
{

  /**#@+
   * @ignore
   */

  // directories cache
  private static $dir = '';

  // packed data
  private static $pack = '';

  // internal cursor
  private static $offset = 0;

  // number of paths
  private static $count = 0;

  // total files
  private static $num = 0;

  /**#@-*/



  /**
   * Information
   *
   * @param  string Zip file
   * @return mixed
   */
  final public static function info($file) {
    if ( ! is_file($file)) {
      return FALSE;
    }

    $out = array();
    $tmp = zip_open($file);

    while ($old = @zip_read($tmp)) {
      $name = zip_entry_name($old);
      $out[$name] = '';

      if (zip_entry_open($tmp, $old)) {
        $zipd = zip_entry_compressedsize($old);
        $size = zip_entry_filesize($old);

        $out[$name] = array(
            'compressed' => $zipd,
            'normal' => $size,
            'ratio' => $size ? round(100 - $zipd / $size * 100, 1) : -1,
        );

        zip_entry_close($old);
      }
    }
    @zip_close($tmp);
    return $out;
  }


  /**
   * Extract
   *
   * @param  string  Zip file
   * @param  string  Output path
   * @param  string  Filter
   * @return boolean
   */
  final public static function extract($file, $path, $filter = '*') {
    if ( ! is_file($file) OR ! is_dir($path)) {
      return FALSE;
    }

    $tmp = zip_open($file);

    while (FALSE !== ($old = zip_read($tmp))) {
      $name = basename(zip_entry_name($old));
      $new = $path.DS.zip_entry_name($old);

      if (zip_entry_open($tmp, $old, 'r')) {
        $dir = dirname("$new.x");

        ! is_dir($dir) && mkpath($dir);

        $size = zip_entry_filesize($old);

        if (($size <= 0 && ! strpos($name, '.')) OR (substr($new, -1) == '/')) {
          ! file_exists($new) && mkpath($new);
        } elseif (fnmatch($filter, $name) OR ! strpos($name, '.')) {
          is_dir($dir) && write($dir.DS.$name, zip_entry_read($old, $size));
        }
        zip_entry_close($old);
      }
    }
    zip_close($tmp);
    return TRUE;
  }


  /**
   * Exists?
   *
   * @param  string  Zip file
   * @param  string  Filter
   * @return mixed
   */
  final public static function exists($file, $filter = '') {
    if ( ! is_file($file)) {
      return FALSE;
    }

    $out = FALSE;
    $tmp = zip_open($file);

    while ($old = zip_read($tmp)) {
      $name = strtr(zip_entry_name($old), '\\', '/');
      if (fnmatch($filter, $name)) {
        $out = TRUE;
        break;
      }
    }
    zip_close($tmp);
    return $out;
  }


  /**
   * Export
   *
   * @param  string  Zip file
   * @param  boolean Return?
   * @return boolean
   */
  final public static function export($file = '', $return = FALSE) {
    if (empty($file)) {
      if ($return) {
        return static::zip_read();
      }
      echo static::zip_read();
    } elseif ( ! is_dir(dirname($file))) {
      return FALSE;
    }
    return write($file, static::zip_read());
  }


  /**
   * Add files
   *
   * @param  string  File|Path
   * @param  string  Filter
   * @param  boolean Recursive?
   * @param  string  Prefix (optional)
   * @return void
   */
  final public static function add($path, $filter = '*', $recursive = FALSE, $prefix = '') {
    if (is_file($path)) {
      static::add_file(basename($path), read($path));
    } elseif (is_dir($path)) {
      if ($tmp = dir2arr($path, $filter, ($recursive ? DIR_RECURSIVE : 0) | DIR_MAP)) {
        foreach ($tmp as $old) {
          $new = rtrim($path, '\\/').DS.$old;

          ( ! empty($path) && $path <> '/') && $new = str_replace($path, '', $old);

          $new = trim(strtr($new, '\\', '/'), '/');
          $new = ! empty($prefix) ? "$prefix/$new" : $new;

          if (is_file($old)) {
            static::add_file($new, read($old));
          } elseif ($recursive) {
            static::add_dir(rtrim($new, '\\/'));
          }
        }
      }
    }
  }


  /**
   * Clear buffer
   *
   * @return void
   */
  function clear() {
    static::$dir = NULL;
    static::$pack = NULL;
    static::$offset = 0;
    static::$count = 0;
    static::$num = 0;
  }



  /**#@+
   * @ignore
   */

  // binary packed data
  final private static function zip_read() {
    if (static::$count > 0) {
      $out = static::$pack . static::$dir . "\x50\x4b\x05\x06\x00\x00\x00\x00"
           . pack('v', static::$count) . pack('v', static::$count)
           . pack('V', strlen(static::$dir))
           . pack('V', strlen(static::$pack)) . "\x00\x00";
      return $out;
    }
  }

  // append directory
  final private static function add_dir($dir) {
    $path = rtrim(strtr($dir, '\\', '/'), '/') . '/';

    static::$pack .= "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00"
                  . pack('V', 0) . pack('V', 0) . pack('V', 0) . pack('v', strlen($path))
                  . pack('v', 0) . $path . pack('V', 0).pack('V', 0).pack('V', 0);

    static::$dir .= "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00"
                 . "\x00\x00\x00\x00" . pack('V',0)
                 . pack('V',0) . pack('V',0) . pack('v', strlen($path)) . pack('v', 0)
                 . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 16)
                 . pack('V', static::$offset) . $path;

    static::$offset = strlen(static::$pack);
    static::$count += 1;
  }

  // append file
  final private static function add_file($file, $data = NULL) {
    $crc32  = crc32($data);

    $gzdata = gzcompress($data);
    $gzdata = substr($gzdata, 2, -4);

    $final_size = strlen($gzdata);
    $real_size  = strlen($data);

    $old = rtrim(strtr($file, '\\', '/'), '/');

    static::$pack .= "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00\x00\x00\x00\x00"
                  . pack('V', $crc32) . pack('V', $final_size)
                  . pack('V', $real_size) . pack('v', strlen($old))
                  . pack('v', 0) . $old . $gzdata;

    static::$dir .= "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00\x00\x00\x00\x00"
                 . pack('V', $crc32) . pack('V', $final_size) . pack('V', $real_size)
                 . pack('v', strlen($old)) . pack('v', 0) . pack('v', 0)
                 . pack('v', 0) . pack('v', 0) . pack('V', 32)
                 . pack('V', static::$offset) . $old;

    static::$offset = strlen(static::$pack);
    static::$count += 1;
    static::$num   += 1;
  }

  /**#@-*/

}


/* EOF: ./library/zip.php */
