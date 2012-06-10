<?php

/**
 * FTP library
 */

if ( ! function_exists('ftp_connect')) {
  raise(ln('extension_missing', array('name' => 'FTP')));
}

class ftp extends prototype
{

  /**#@+
   * @ignore
   */

  // connection resource
  private static $res = '';

  // current path
  private static $path = '';

  // connection
  private static $isconn = FALSE;

  // options
  protected static $defs = array(
                    'host' => '',
                    'port' => '',
                    'user' => '',
                    'pass' => '',
                    'path' => '',
                    'relax' => FALSE,
                  );
  /**#@-*/



  /**
   * Connection
   *
   * @param  mixed   DSN|IP|Function callback|Force relax mode? (optional)
   * @param  boolean Force relax mode? (optional)
   * @return mixed
   */
  function connect($dsn_host = '', $relax = NULL) {
    if (is_closure($dsn_host)) {
      static::config($dsn_host);
      $dsn_host = '';
    } elseif (is_bool($dsn_host)) {
      $relax = $dsn_host;
      $dsn_host = '';
    }

    is_null($relax) && $relax = (bool) static::$defs['relax'];

    $dsn_host = static::build_dsn($dsn_host);

    if ( ! is_url($dsn_host)) {
      return FALSE;
    }

    $test = @parse_url($dsn_host);

    static::disconnect();

    if ( ! empty($test['scheme']) && ! empty($test['host'])) {
      if ($test['scheme'] <> 'ftp') {
        raise(ln('not_implemented', array('name' => strtoupper($test['scheme']))));
      } else {
        static::$res = ftp_connect($test['host'], ! empty($test['port']) ? $test['port'] : 21);

        $relax && ftp_pasv(static::$res, TRUE);


        $user = ! empty($test['user']) ? $test['user'] : 'anonymous';
        $pass = ! empty($test['pass']) ? $test['pass'] : '';

        static::$isconn = (boolean) @ftp_login(static::$res, $user, $pass);

        if ( ! empty($test['path'])) {
          static::chdir($test['path']);
        }
        return static::$isconn;
      }
    }
  }


  /**
   * Connection status
   *
   * @return boolean
   */
  function is_logged() {
    return (boolean) static::$isconn;
  }


  /**
   * Change directory
   *
   * @param  string  Path
   * @return boolean
   */
  function chdir($path) {
    if ( ! static::$isconn OR empty($path)) {
      return FALSE;
    }

    $out = @ftp_chdir(static::$res, static::realpath($path));

    if ($out !== FALSE) {
      return static::get_cwd();
    }

    return (boolean) $out;
  }


  /**
   * Make path
   *
   * @param  string  Path
   * @param  mixed   Permissions
   * @return boolean
   */
  function mkdir($path, $perms = 0755) {
    if ( ! static::$isconn OR empty($path)) {
      return FALSE;
    }

    $out = @ftp_mkdir(static::$res, static::realpath($path));

    if ( ! empty($perms)) {
      static::chmod(static::realpath($path), $perms);
    }
    return (boolean) $out;
  }


  /**
   * Create content
   *
   * @param  string  Path
   * @param  string  Content
   * @return boolean
   */
  function write($name, $content = '') {
    $old = TMP.DS.uniqid($name);

    if (write($old, $content)) {
      $out = static::upload($old, $name);
      @unlink($old);
      return $out;
    }

    return FALSE;
  }


  /**
   * Upload file
   *
   * @param  string  Local path
   * @param  string  Remote path
   * @param  mixed   Permissions
   * @return boolean
   */
  function upload($local, $remote = '', $perms = 0755) {
    if ( ! static::$isconn) {
      return FALSE;
    } elseif ( ! is_file($local)) {
      return FALSE;
    }

    ! $remote && $remote = basename($local);

    $out = @ftp_put(static::$res, static::realpath($remote), $local, FTP_BINARY);

    $perms && static::chmod(static::realpath($remote), $perms);

    return (boolean) $out;
  }


  /**
   * Rename files
   *
   * @param  string  Old
   * @param  string  New
   * @return boolean
   */
  function rename($old, $new)
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    $out = @ftp_rename(static::$res, static::realpath($old), static::realpath($new));

    return  (boolean) $out;
  }


  /**
   * Remove files
   *
   * @param  string  Path
   * @return boolean
   */
  function unlink($path)
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    $out = ftp_delete(static::$res, $path);

    return (boolean) $out;
  }


  /**
   * Delete directory
   *
   * @param  string  Path
   * @return boolean
   */
  function undir($path)
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    $path = rtrim($path, '/') . '/';
    $set  = static::list_files($path);

    if ( ! empty($set)) {
      foreach ($set as $one) {
        $tmp = basename($one);

        if ($tmp == '.' OR $tmp == '..') {
          continue;
        }

        if ( ! @ftp_delete(static::$res, "$path/$one")) {
          static::undir($one);
        }
      }
    }

    if (@ftp_rmdir(static::$res, $path)) {
      return TRUE;
    }

    return FALSE;
  }


  /**
   * Establish permissions
   *
   * @param  string  Path
   * @param  mixed   Permissions
   * @return boolean
   */
  function chmod($path, $perms = 0755)
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    $out = @ftp_chmod(static::$res, $perms, static::realpath($path));

    return (boolean) $out;
  }


  /**
   * File listing
   *
   * @param  string Path
   * @return mixed
   */
  function list_files($path = '')
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    if ( ! $path) {
      $path = static::$path;
    } else {
      $path = static::realpath($path);
    }

    return @ftp_nlist(static::$res, $path ?: '.');
  }


  /**
   * Current path
   *
   * @return mixed
   */
  function get_cwd()
  {
    if ( ! static::$isconn) {
      return FALSE;
    }

    static::$path = ftp_pwd(static::$res);

    return static::$path;
  }


  /**
   * Close connection
   *
   * @return void
   */
  final public static function disconnect()
  {
    if ( ! static::$isconn OR empty(static::$res)) {
      return FALSE;
    } elseif (ftp_close(static::$res)) {
      static::$isconn = FALSE;
    }
  }



  /**#@+
   * @ignore
   */

  // prepare defaults
  final private static function build_dsn($test) {
    $out = '';

    if ( ! empty($test)) {
      $out = is_ipv4($test) ? "ftp://$test" : $test;
    } else {
      extract(static::$defs);

      $out = "ftp://$user";
      $pass && $out .= ":$pass";

      $out .= "@$host";

      $port && $out .= ':' . ((int) $port);
    }
    return $out;
  }

  // resolve paths
  final private static function realpath($path = NULL) {
    if (substr($path, 0, 1) == '/') {
      return $path;
    }

    //TODO: handle ../ ?
    return static::$path . "/$path";
  }
  /**#@-*/

}

/* EOF: ./library/ftp.php */
