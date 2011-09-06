<?php

/**
 * Session initialization
 */

call_user_func(function()
{
  require __DIR__.DS.'functions'.EXT;


  // default session hanlder
  // http://php.net/session_set_save_handler

  session_set_save_handler(function ()
  {
    return TRUE;
  }, function ()
  {
    return TRUE;
  }, function ($id)
  {
    return read(TMP.DS."sess_$id");
  }, function ($id, $data)
  {
    return write(TMP.DS."sess_$id", $data);
  }, function ($id)
  {
    return @unlink(TMP.DS."sess_$id");
  }, function ($max)
  {
    foreach (dir2arr(TMP, "sess_*") as $one)
    {
      if ((filemtime(TMP.DS.$one) + $max) < time())
      {
        unlink(TMP.DS.$one);
      }
    }
  });


  // session conf
  if ( ! is_local())
  {
    session_set_cookie_params(86400, ROOT, '.' . server());
  }

  session_name('--a-' . preg_replace('/\W/', '-', phpversion()));
  // TODO: BTW with session_id()?
  session_start();


  // expires+hops
  foreach ($_SESSION as $key => $val)
  {
    if ( ! is_array($val) OR ! array_key_exists('value', $val))
    {
      continue;
    }

    if (isset($_SESSION[$key]['expires']) && (time() >= $val['expires']))
    {
      unset($_SESSION[$key]);
    }

    if (isset($_SESSION[$key]['hops']) && ($_SESSION[$key]['hops']-- <= 0))
    {
      unset($_SESSION[$key]);
    }
  }

  // TODO: CRSF token check?
  define('TOKEN', sprintf('%d %s', time(), sha1(salt(13))));
  define('CHECK', ! empty($_SESSION['--csrf-token']) ? $_SESSION['--csrf-token'] : NULL);

  $_SESSION['--csrf-token'] = TOKEN;
});

/* EOF: ./lib/tetl/session/initialize.php */
