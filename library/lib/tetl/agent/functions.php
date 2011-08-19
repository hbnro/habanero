<?php

/**
 * UserAgent detection library
 */

function client($ua = '')
{
  static $set = NULL,
         $defs = array(
            'robot' => '',
            'mobile' => '',
            'browser' => '',
            'version' => '',
            'platform' => '',
            'is_browser' => FALSE,
            'is_mobile' => FALSE,
            'is_robot' => FALSE,
          );
         

  if (is_null($set))
  {
    $set = include __DIR__.DS.'assets'.DS.'scripts'.DS.'user_agents'.EXT;
  }
  
  $out = $defs;
  $ua  = $ua ?: agent();
  
  
  // platform
  foreach ($set['platforms'] as $key => $val)
  {
    if ( ! is_false(strpos(strtolower($ua), $key)))
    {
      $out['platform'] = $val;
      break;
    }
  }
  

  // browser
  foreach ($set['browsers'] as $key => $val)
  {
    if (preg_match(sprintf('/%s.*?([0-9\.]+)/i', preg_quote($key, '/')), strtolower($ua), $match))
    {
      $out['is_browser'] = TRUE;
      $out['is_robot'] = FALSE;
      $out['version'] = $match[1];
      $out['browser'] = $val;
      break;
    }
  }

  if (empty($out['browser']))
  {
    $out['browser'] = preg_replace('/^([\w\s]+(?=\W)).*?$/', '\\1', $ua);
  }
  
  if (empty($out['version']))
  {//FIX
    $regex = sprintf('/%s.*?([0-9\.]+)/i', preg_quote($out['browser'], '/'));
    
    if (preg_match($regex, $test, $match))
    {
      $out['version'] = $match[1];
    }
  }


  // mobile
  foreach ($set['mobiles'] as $key => $val)
  {
    if ( ! is_false(strpos(strtolower($ua), $key)))
    {
      $out['is_mobile'] = TRUE;
      $out['is_robot'] = FALSE;
      $out['mobile'] = $val;
      break;
    }
  }
  

  // robot
  foreach ($set['robots'] as $key => $val)
  {
    if ( ! is_false(strpos(strtolower($ua), $key)))
    {
      $out['is_browser'] = FALSE;
      $out['is_mobile'] = FALSE;
      $out['is_robot'] = TRUE;
      $out['robot'] = $val;
      break;
    }
  }
  
  return $out;
}

/* EOF: ./lib/agent/functions.php */
