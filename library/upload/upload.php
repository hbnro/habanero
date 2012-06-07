<?php

/**
 * Basic upload library
 */

class upload extends prototype
{

  /**#@+
   * @ignore
   */

  // S3 instance
  private static $s3 = NULL;

  // files pointer
  private static $handle = NULL;

  // files stack
  private static $files = array();

  // error stack
  private static $error = array();

  // error messages
  private static $status = array(
                    UPLOAD_ERR_OK => 'without_errors',
                    UPLOAD_ERR_INI_SIZE => 'max_size_ini_reached',
                    UPLOAD_ERR_FORM_SIZE => 'max_size_form_reached',
                    UPLOAD_ERR_PARTIAL => 'partial_upload_error',
                    UPLOAD_ERR_NO_FILE => 'file_not_selected',
                    UPLOAD_ERR_NO_TMP_DIR => 'temporary_path_missing',
                    UPLOAD_ERR_CANT_WRITE => 'write_file_error',
                    UPLOAD_ERR_EXTENSION => 'extension_file_not_allowed',
                    UPLOAD_ERR_PATH => 'destination_path_missing',
                    UPLOAD_ERR_MULTI => 'multi_upload_error',
                    UPLOAD_ERR_MIN_SIZE => 'min_size_error',
                    UPLOAD_ERR_MAX_SIZE => 'max_size_error',
                    UPLOAD_ERR_TYPE => 'filetype_error',
                    UPLOAD_ERR_EXT => 'extension_error',
                  );

  // defaults
  protected static $defs = array(
                    'path' => TMP,
                    'name' => 'file',
                    'type' => '*/*',
                    'extension' => '*.*',
                    'min_size' => 1024,
                    'max_size' => 2097152,
                    'skip_error' => FALSE,
                    'multiple' => FALSE,
                    'unique' => FALSE,
                    's3_key' => '',
                    's3_secret' => '',
                  );

  /**#@-*/



  /**
   * Execute upload
   *
   * @param  boolean Override skip_error
   * @param  array   Input files
   * @return boolean
   */
  final public static function validate($skip = FALSE, array $input = array()) {
    static::s3_init();

    // reset
    static::$handle = NULL;
    static::$files  = array();
    static::$error  = array();


    $out = FALSE;

    if ( ! static::is_dir(static::$defs['path'])) {
      return static::set_error(UPLOAD_ERR_PATH);
    }

    (func_num_args() <= 1) && $input = $_FILES;

    $set = static::fix_files(value($input, static::$defs['name'], array()));

    if (empty($set)) {
      return static::set_error(UPLOAD_ERR_NO_FILE);
    } elseif (is_false(static::$defs['multiple']) && (sizeof($set['name']) > 1)) {
      return static::set_error(UPLOAD_ERR_MULTI);
    }


    foreach ($set['error'] as $i => $val) {
      if ($val > 0) {
        if (is_false(static::$defs['skip_error'], $skip)) {
          return static::set_error($val);
        }
        continue;
      }


      if ($set['size'][$i] > static::$defs['max_size']) {
        return static::set_error(UPLOAD_ERR_MAX_SIZE);
      } elseif ($set['size'][$i] < static::$defs['min_size']) {
        return static::set_error(UPLOAD_ERR_MIN_SIZE);
      }


      $type = FALSE;

      foreach ((array) static::$defs['type'] as $one) {
        if (fnmatch($one, $set['type'][$i])) {
          $type = TRUE;
          break;
        }
      }

      if (is_false($type)) {
        return static::set_error(UPLOAD_ERR_TYPE);
      }


      $ext = FALSE;

      foreach ((array) static::$defs['extension'] as $one) {

        if (fnmatch($one, strtolower($set['name'][$i]))) {
          $ext = TRUE;
          break;
        }
      }

      if (is_false($ext)) {
        return static::set_error(UPLOAD_ERR_EXT);
      }

      $name = slug($set['name'][$i], '_', SLUG_STRICT);
      $file = static::$defs['path'].DS.$name;

      if ( ! is_true(static::$defs['unique'])) {
        $new = ext($name, TRUE);
        $old = basename($name, $new);

        while (static::is_file($file)) {
          $file  = static::$defs['path'].DS;
          $file .= uniqid($old);
          $file .= $new;
        }
      }


      if ($test = static::move_file($tmp = $set['tmp_name'][$i], $file))
      {
        static::$files []= array_merge(array(
          'info' => $test,
          'file' => $file,
          'type' => $set['type'][$i],
          'size' => $set['size'][$i],
          'name' => basename($file),
        ));

        $out = TRUE;
      }
    }

    return $out;
  }


  /**
   * Login pointer check
   *
   * @return boolean
   */
  final public static function have_files() {
    if (static::$handle = array_shift(static::$files)) {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * Retrieve error stack
   *
   * @return array
   */
  final public static function error_list() {
    $out = array();

    foreach (static::$error as $one) {
      $out []= ln(sprintf('upload.%s', static::$status[$one]));
    }

    return $out;
  }


  /**
   * Retrieve metainfo (S3)
   *
   * @return string
   */
  final public static function get_info() {
    return ! empty(static::$handle['info']) ? static::$handle['info'] : FALSE;
  }


  /**
   * Retrieve filepath
   *
   * @return string
   */
  final public static function get_file() {
    return static::$handle['file'];
  }


  /**
   * Retrieve filesize
   *
   * @return integer
   */
  final public static function get_size() {
    return (int) static::$handle['size'];
  }


  /**
   * Retrieve filetype
   *
   * @return string
   */
  final public static function get_type() {
    return static::$handle['type'];
  }


  /**
   * Retrieve filename
   *
   * @return string
   */
  final public static function get_name() {
    return static::$handle['name'];
  }


  /**
   * Callbacks
   *
   * @param  string Method
   * @param  array  Arguments
   * @return mixed
   */
  final public static function missing($method, $arguments) {
    static::s3_init();

    if (static::$s3) {
      $key    = substr($method, 4);
      $method = camelcase($method);

      if (method_exists(static::$s3, $method)) {
        return call_user_func_array(array(static::$s3, $method), $arguments);
      } elseif (isset(static::$s3->$key)) {
        return static::$s3->$key;
      }
    }

    raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
  }



  /**#@+
   * @ignore
   */

  // append error code to stack
  final private static function set_error($code) {
    static::$error []= $code;
  }

  // fixate multiple uploads
  final private static function fix_files($set) {
    $out = (array) $set;

    if (isset($out['name']) && ! is_array($out['name'])) {
      $test = $out;
      $out  = array();

      foreach ($test as $key => $val) {
        $out[$key] []= $val;
      }
    }

    return $out;
  }

  // filesystem upload
  final private static function move_file($from, $to) {
    if (static::$s3) {// TODO: is not enough?
      @list($bucket, $path) = explode(DS, strtr($to, '\\/', DS), 2);

      static::put_object_file($from, $bucket, $path, S3::ACL_PUBLIC_READ);

      return array_merge(static::get_object_info($bucket, $path), array(
        'url' => static::s3_url($to, TRUE),
      ));
    } else {
      return move_uploaded_file($from, $to) OR copy($from, $to);
    }
  }

  // file check
  final private static function is_file($path) {
    if (static::$s3) {
    return !! @file_get_contents(static::s3_url($path), 0, null, 0, 1);
    } else {
      return is_file($path);
    }
  }

  // dir check
  final private static function is_dir($path) {
    if (static::$s3) {
      @list($bucket) = explode(DS, strtr($path, '\\/', DS), 2);
      return array_key_exists($bucket, static::$s3->buckets);
    } else {
      return is_dir($path);
    }
  }

  // S3 check
  final private static function s3_init() {
    if (is_null(static::$s3)) {
      if (static::$defs['s3_key'] && static::$defs['s3_secret']) {
        /**
         * @ignore
         */
        require __DIR__.DS.'vendor'.DS.'S3'.EXT;

        static::$s3 = new S3(static::$defs['s3_key'], static::$defs['s3_secret']);
        static::$s3->buckets = array();

        foreach (static::list_buckets() as $one) {// TODO: consider cache?
          static::$s3->buckets[$one] = strtolower(static::get_bucket_location($one));
        }
      }
    }
  }

  final private static function s3_url($path, $secure = FALSE) {
    @list($bucket, $path) = explode(DS, strtr($path, '\\/', DS), 2);// TODO: HTTPs?

    $location = ! empty(static::$s3->buckets[$bucket]) ? static::$s3->buckets[$bucket] : 'us';
    $location = $location <> 'us' ? $location : 's3';// its right?
    $secure   = $secure ? 's' : '';

    return "http$secure://$bucket.$location.amazonaws.com/$path";
  }

  /**#@-*/
}

/* EOF: ./library/upload/upload.php */
