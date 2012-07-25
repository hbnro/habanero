<?php

/**
 * Amazon S3 wrapper
 */

class as3 extends prototype
{
  /**#@+
   * @ignore
   */

  // loaded state
  private static $on = FALSE;

  // all buckets
  private static $list = array();

  // defaults
  protected static $defs = array(
                      'key' => '',
                      'secret' => '',
                      'bucket' => '',
                      'location' => FALSE,
                      'permission' => 'private',
                    );

  /**#@-*/



  /**
   * Compose URL
   *
   * @param  string  Path
   * @param  boolean HTTPS?
   * @return string
   */
  public static function url($path, $secure = FALSE) {
    static::init();

    $secure = $secure ? 's' : '';
    $path   = strtr($path, '\\', '/');

    return "http$secure://" . static::hostname() . "/$path";
  }


  /**
   * Retrieve buckets
   *
   * @return array
   */
  public static function buckets() {
    static::init();
    return static::$list;
  }


  /**
   * S3 wrapper
   *
   * @param  string Method
   * @param  array  Arguments
   * @return string
   */
  public static function missing($method, $arguments) {
    static::init();

    if (static::$on) {
      $method = camelcase($method);
      if (method_exists('S3', $method)) {
        return call_user_func_array(array('S3', $method), $arguments);
      }
    }

    raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
  }



  /**#@+
   * @ignore
   */

  // bucket host
  private static function hostname() {
    $bucket   = static::$defs['bucket'];

    $location = ! empty(static::$list[$bucket]) ? static::$list[$bucket] : (static::$defs['location'] ?: 'us');
    $location = $location <> 'us' ? $location : 's3';// its right?

    return "$bucket.$location.amazonaws.com";
  }

  // initialize and fetch
  private static function init() {
    if ( ! static::$on) {
      if (static::$defs['key'] && static::$defs['secret']) {
        static::$on = TRUE;

        static::set_auth(static::$defs['key'], static::$defs['secret']);

        foreach (static::list_buckets() as $one) {// TODO: consider cache?
          static::$list[$one] = strtolower(static::get_bucket_location($one));
        }

        ! static::$defs['bucket'] && static::$defs['bucket'] = strtr(basename(APP_PATH), '_', '-');
      }
    }
  }

  /**#@-*/
}

/* EOF: ./library/as3/as3.php */
