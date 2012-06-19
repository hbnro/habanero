<?php

/**
 * UserAgent detection library
 */

// hocus pocus...
class ua
{
  final public static function missing($method, $arguments) {
    static $set = NULL,
           $tmp = array();


    if (is_null($set)) {
      $set = include __DIR__.DS.'assets'.DS.'scripts'.DS.'user_agents'.EXT;
    }

    $ua = ! sizeof($arguments) ? value($_SERVER, 'HTTP_USER_AGENT') : array_pop($arguments);

    if ( ! isset($tmp[$ua])) {
      $out = array(
        'robot' => '',
        'mobile' => '',
        'browser' => '',
        'version' => '',
        'platform' => '',
        'is_browser' => FALSE,
        'is_mobile' => FALSE,
        'is_robot' => FALSE,
      );

      // platform
      foreach ($set['platforms'] as $key => $val) {
        if (strpos(strtolower($ua), $key) !== FALSE) {
          $out['platform'] = $val;
          break;
        }
      }


      // browser
      foreach ($set['browsers'] as $key => $val) {
        if (preg_match(sprintf('/%s.*?([0-9\.]+)/i', preg_quote($key, '/')), strtolower($ua), $match)) {
          $out['is_browser'] = TRUE;
          $out['is_robot'] = FALSE;
          $out['version'] = $match[1];
          $out['browser'] = $val;
          break;
        }
      }

      if (empty($out['browser'])) {
        $out['browser'] = preg_replace('/^([\w\s]+(?=\W)).*?$/', '\\1', $ua);
      }

      if (empty($out['version'])) {//FIX
        $regex = sprintf('/%s.*?([0-9\.]+)/i', preg_quote($out['browser'], '/'));

        if (preg_match($regex, $test, $match)) {
          $out['version'] = $match[1];
        }
      }


      // mobile
      foreach ($set['mobiles'] as $key => $val) {
        if (strpos(strtolower($ua), $key) !== FALSE) {
          $out['is_mobile'] = TRUE;
          $out['is_robot'] = FALSE;
          $out['mobile'] = $val;
          break;
        }
      }


      // robot
      foreach ($set['robots'] as $key => $val) {
        if (strpos(strtolower($ua), $key) !== FALSE) {
          $out['is_browser'] = FALSE;
          $out['is_mobile'] = FALSE;
          $out['is_robot'] = TRUE;
          $out['robot'] = $val;
          break;
        }
      }

      return $tmp[$ua] = $out;
    }

    if ( ! array_key_exists($method, $tmp[$ua])) {
      raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
    }
    return $tmp[$ua][$method];
  }
}

/* EOF: ./library/client/ua/ua.php */
