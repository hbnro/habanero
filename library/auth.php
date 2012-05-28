<?php

/**
 * Model-based auth
 */

class auth extends prototype
{

  /**#@+
   * @ignore
   */

  // login status
  private static $logged = NULL;

  // defaults
  protected static $defs = array(
                      'encode' => 'md5',
                      'model' => FALSE,
                      'user' => 'user',
                      'pass' => 'pass',
                      'salt' => 'salt',
                    );

  /**#@-*/



  /**
   * Retrieve login status
   *
   * @return boolean
   */
  final public static function is_logged() {
    if (is_null(static::$logged)) {
      static::$logged = (boolean) session('--auth-login');
    }
    return static::$logged;
  }


  /**
   * Single validation
   *
   * @return boolean
   */
  final public static function validate($username, $password) {
    if ($data = static::check($username, $password)) {
      return static::login($data);
    }
    return FALSE;
  }


  /**
   * Verify credentials
   *
   * @param  string  User name
   * @param  string  Password
   * @return boolean
   */
  final public static function check($username, $password) {
    if (static::$defs['model'] && $username && $password) {
      $password = static::hash($username, $password);
      $model    = static::$defs['model'];

      $where[static::$defs['user']] = $username;
      $where[static::$defs['pass']] = $password;

      if ($model::count(compact('where')) === 1) {
        $old = $model::first(compact('where'));
        return $old->fields();
      }
    }
    return FALSE;
  }


  /**
   * Create users
   *
   * @param  string  User name
   * @param  string  Password
   * @return boolean
   */
  final public static function insert($username, $password) {
    if (static::$defs['model']) {
      $salt  = salt(16);
      $model = static::$defs['model'];

      $model::create(array(
        static::$defs['user'] => $username,
        static::$defs['pass'] => static::hash(TRUE, $salt . $password),
        static::$defs['salt'] => $salt,
      ));
    }
  }


  /**
   * Retrieve session
   *
   * @return mixed
   */
  final public static function info() {
    return session('--auth-data') ?: FALSE;
  }


  /**
   * Destroy session
   *
   * @return void
   */
  final public static function logout() {
    static::clear();
  }



  /**#@+
   * @ignore
   */

  // retrieve login
  final private static function login($test) {
    session('--auth-login', TRUE);
    session('--auth-data', $test);
    static::$logged = TRUE;
    return TRUE;
  }

  // password hashing
  final private static function hash($user, $pass) {
    if ($salt = static::$defs['salt']) {
      if ( ! is_true($user) && static::$defs['model']) {
        $params = array(
          'where' => array(static::$defs['user'] => $user),
          'select' => $salt,
        );

        $model = static::$defs['model'];
        $test  = $model::first($params);
        $pass  = $test[static::$defs['salt']] . $pass;
      }
    }

    if (is_callable(static::$defs['encode'])) {
      return call_user_func(static::$defs['encode'], $pass);
    }
    return $pass;
  }

  // clear session
  final private static function clear() {
    session('--auth-login', NULL);
    session('--auth-data', NULL);
    static::$logged = FALSE;
  }

  /**#@-*/
}

/* EOF: ./library/auth.php */
