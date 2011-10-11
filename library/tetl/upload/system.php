<?php

/**
 * Basic upload library
 */

/**#@+
 * Upload error constants
 */
define('UPLOAD_ERR_PATH', 9);
define('UPLOAD_ERR_MULTI', 10);
define('UPLOAD_ERR_MIN_SIZE', 11);
define('UPLOAD_ERR_MAX_SIZE', 12);
define('UPLOAD_ERR_TYPE', 13);
define('UPLOAD_ERR_EXT', 14);
/**#@-*/


class upload extends prototype
{

  /**#@+
   * @ignore
   */

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
  private static $defs = array(
                    'path' => TMP,
                    'name' => 'file',
                    'type' => '*/*',
                    'extension' => '*.*',
                    'min_size' => 1024,
                    'max_size' => 2097152,
                    'skip_error' => FALSE,
                    'multiple' => FALSE,
                    'unique' => FALSE,
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
      static::$defs = array_merge(static::$defs, $key);
    }
    elseif (array_key_exists($key, static::$defs))
    {
      static::$defs[$key] = $value;
    }
  }


  /**
   * Execute upload
   *
   * @param  boolean Override skip_error
   * @return boolean
   */
  final public static function validate($skip = FALSE)
  {
    // reset
    static::$handle = NULL;
    static::$files  = array();
    static::$error  = array();


    $out = FALSE;

    if ( ! is_dir(static::$defs['path']))
    {
      return static::set_error(UPLOAD_ERR_PATH);
    }


    $set = static::fix_files(value($_FILES, static::$defs['name'], array()));

    if (empty($set))
    {
      return static::set_error(UPLOAD_ERR_NO_FILE);
    }
    elseif (is_false(static::$defs['multiple']) && (sizeof($set['name']) > 1))
    {
      return static::set_error(UPLOAD_ERR_MULTI);
    }


    foreach ($set['error'] as $i => $val)
    {
      if ($val > 0)
      {
        if (is_false(static::$defs['skip_error'], $skip))
        {
          return static::set_error($val);
        }
        continue;
      }


      if ($set['size'][$i] > static::$defs['max_size'])
      {
        return static::set_error(UPLOAD_ERR_MAX_SIZE);
      }
      elseif ($set['size'][$i] < static::$defs['min_size'])
      {
        return static::set_error(UPLOAD_ERR_MIN_SIZE);
      }


      $type = FALSE;

      foreach ((array) static::$defs['type'] as $one)
      {
        if (match($one, $set['type'][$i]))
        {
          $type = TRUE;
          break;
        }
      }

      if (is_false($type))
      {
        return static::set_error(UPLOAD_ERR_TYPE);
      }


      $ext = FALSE;

      foreach ((array) static::$defs['extension'] as $one)
      {

        if (match($one, strtolower($set['name'][$i])))
        {
          $ext = TRUE;
          break;
        }
      }

      if (is_false($ext))
      {
        return static::set_error(UPLOAD_ERR_EXT);
      }


      $name = slug($set['name'][$i], '_');
      $file = static::$defs['path'].DS.$name;

      if ( ! is_true(static::$defs['unique']))
      {
        $new = ext($name, TRUE);
        $old = basename($name, $new);

        while (is_file($file))
        {
          $file  = static::$defs['path'].DS;
          $file .= uniqid($old);
          $file .= $new;
        }
      }


      if (move_uploaded_file($tmp = $set['tmp_name'][$i], $file) OR copy($tmp, $file)) //FIX
      {
        static::$files []= array(
          'file' => $file,
          'type' => $set['type'][$i],
          'size' => $set['size'][$i],
          'name' => basename($file),
        );

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
  final public static function have_files()
  {
    if (static::$handle = array_shift(static::$files))
    {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * Retrieve error stack
   *
   * @return array
   */
  final public static function error_list()
  {
    $out = array();

    foreach (static::$error as $one)
    {
      $out []= ln(sprintf('upload.%s', static::$status[$one]));
    }

    return $out;
  }


  /**
   * Retrieve filepath
   *
   * @return string
   */
  final public static function get_file()
  {
    return static::$handle['file'];
  }


  /**
   * Retrieve filesize
   *
   * @return integer
   */
  final public static function get_size()
  {
    return (int) static::$handle['size'];
  }


  /**
   * Retrieve filetype
   *
   * @return string
   */
  final public static function get_type()
  {
    return static::$handle['type'];
  }


  /**
   * Retrieve filename
   *
   * @return string
   */
  final public static function get_name()
  {
    return static::$handle['name'];
  }



  /**#@+
   * @ignore
   */

  // append error code to stack
  final private static function set_error($code)
  {
    static::$error []= $code;
  }

  // fixate multiple uploads
  final private static function fix_files($set)
  {
    $out = (array) $set;

    if (isset($out['name']) && ! is_array($out['name']))
    {
      $test = $out;
      $out  = array();

      foreach ($test as $key => $val)
      {
        $out[$key] []= $val;
      }
    }

    return $out;
  }

  /**#@-*/
}

/* EOF: ./library/tetl/upload/system.php */
