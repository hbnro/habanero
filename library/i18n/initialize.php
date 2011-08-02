<?php

/**
 * Intialize translation backend
 */

lambda(function()
{
  // locales
  $out  = array();
  $lang = option('language', 'en');
  $test = explode(',', server('HTTP_ACCEPT_LANGUAGE'));
  
  
  $out[$lang] = 1;
  
  foreach ($test as $one)
  {
    $one = explode(';q=', $one);
    $out[$one[0]] = ! empty($one[1]) ? (float) $one[1] : 1;
  }
  
  arsort($out, SORT_NUMERIC);
  $lang = key($out);  

    
  define('LANG', $lang);
  
  @setlocale(LC_ALL, "$lang.UTF-8");

  require __DIR__.DS.'system'.EXT;
  
  load_path(__DIR__.DS.'locale');
});

/* EOF: ./i18n/initialize.php */
