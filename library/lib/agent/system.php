<?php

/**
 * UserAgent detection library
 */

class agent extends prototype
{
  
  /**#@+
   * @ignore
   */
  
  // fetched results
  protected static $data = array(
    'robot' => '',
    'mobile' => '',
    'browser' => '',
    'version' => '',
    'platform' => '',
    'is_browser' => FALSE,
    'is_mobile' => FALSE,
    'is_robot' => FALSE,
  );
  
  // list of properties
  protected static $set = array();
  
  // defined user agent
  protected static $ua = '';
  
  /**#@-*/
  
  
  
  /**
   * Fetch
   *
   * @param  string UserAgent name
   * @return array
   */
  function fetch($name = '')
  {
    $out = self::$data;
    
    if (empty(agent::$set))
    {
      agent::$set = include __DIR__.DS.'assets'.DS.'scripts'.DS.'user_agents'.EXT;
    }
    
    agent::$ua = (string) ($name ?: agent());
    
    
    // platform
    foreach (self::$set['platforms'] as $key => $val)
    {
      if ( ! is_false(strpos(strtolower(self::$ua), $key)))
      {
        $out['platform'] = $val;
        break;
      }
    }
    

    // browser
    $test = strtolower(self::$ua);
    
    foreach (self::$set['browsers'] as $key => $val)
    {
      if (preg_match(sprintf('/%s.*?([0-9\.]+)/i', preg_quote($key, '/')), $test, $match))
      {
        $out['is_browser'] = TRUE;
        $out['is_robot'] = FALSE;
        $out['version'] = $match[1];
        $out['browser'] = $val;
        break;
      }
    }

    if (empty(self::$data['browser']))
    {
      self::$data['browser'] = preg_replace('/^([\w\s]+(?=\W)).*?$/', '\\1', $test);
    }
    
    if (empty(self::$data['version']))
    {//FIX
      $regex = sprintf('/%s.*?([0-9\.]+)/i', preg_quote(self::$data['browser'], '/'));
      
      if (preg_match($regex, $test, $match))
      {
        $out['version'] = $match[1];
      }
    }
  

    // mobile
    foreach (self::$set['mobiles'] as $key => $val)
    {
      if ( ! is_false(strpos(strtolower(self::$ua), $key)))
      {
        $out['is_mobile'] = TRUE;
        $out['is_robot'] = FALSE;
        $out['mobile'] = $val;
        break;
      }
    }
    
  
    // robot
    foreach (self::$set['robots'] as $key => $val)
    {
      if ( ! is_false(strpos(strtolower(self::$ua), $key)))
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
  
}

/* EOF: ./lib/agent/system.php */
