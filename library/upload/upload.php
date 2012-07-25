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
                    's3' => array(),
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
    // reset
    static::$handle = NULL;
    static::$files  = array();
    static::$error  = array();


    $out = FALSE;

    if (static::use_s3()) {
      if ( ! array_key_exists(as3::option('bucket'), static::$s3->list)) {
        return static::set_error(UPLOAD_ERR_PATH);
      }
    } elseif ( ! is_dir(static::$defs['path'])) {
      return static::set_error(UPLOAD_ERR_PATH);
    }

    (func_num_args() <= 1) && $input = $_FILES;

    $set = static::fix_files(value($input, static::$defs['name'], array()));

    if (empty($set)) {
      return static::set_error(UPLOAD_ERR_NO_FILE);
    } elseif ( ! static::$defs['multiple'] && (sizeof($set['name']) > 1)) {
      return static::set_error(UPLOAD_ERR_MULTI);
    }


    foreach ($set['error'] as $i => $val) {
      if ($val > 0) {
        if ( ! static::$defs['skip_error'] OR ! $skip) {
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

      if ( ! $type) {
        return static::set_error(UPLOAD_ERR_TYPE);
      }


      $ext = FALSE;

      foreach ((array) static::$defs['extension'] as $one) {

        if (fnmatch($one, strtolower($set['name'][$i]))) {
          $ext = TRUE;
          break;
        }
      }

      if ( ! $ext) {
        return static::set_error(UPLOAD_ERR_EXT);
      }

      $name = preg_replace('/[^()\w.-]/', ' ', $set['name'][$i]);
      $name = preg_replace('/\s+/', '-', $name);
      $file = static::$defs['path'].DS.$name;

      if ( ! static::$defs['unique']) {
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
      $out []= ln('upload.' . static::$status[$one]);
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
    if (static::use_s3()) {// TODO: is enough?
      $bucket = as3::option('bucket');

      as3::put_object_file($from, $bucket, $to, as3::option('permission') ?: S3::ACL_PUBLIC_READ);

      return array_merge(as3::get_object_info($bucket, $to), array(
        'url' => as3::url($to, TRUE),
      ));
    } else {
      return move_uploaded_file($from, $to) OR copy($from, $to);
    }
  }

  // file check
  final private static function is_file($path) {
    if (static::use_s3()) {
      $test = get_headers(as3::url($path));
      return strpos(array_shift($test), '404 Not Found') !== FALSE ? FALSE : TRUE;
    } else {
      return is_file($path);
    }
  }

  // S3 check
  final private static function use_s3() {
    if (is_null(static::$s3)) {
      static::$s3 = new stdClass;
      static::$s3->enable = FALSE;

      if ( ! empty(static::$defs['s3'])) {
        as3::config(static::$defs['s3']);

        static::$s3->enable = TRUE;
        static::$s3->list   = as3::buckets();
      }
    }

    return ! empty(static::$s3->enable);
  }

  /**#@-*/
}

/* EOF: ./library/upload/upload.php */
