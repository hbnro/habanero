<?php

/**
 * Server library initialization
 */

call_user_func(function()
{
  require __DIR__.DS.'functions'.EXT;
  
  
  // root
  $url = array();
  
  $url['ORIG_PATH_INFO'] = FALSE;
  $url['REQUEST_URI']    = FALSE;
  $url['SCRIPT_URL']     = TRUE;
  $url['PATH_INFO']      = FALSE;
  $url['PHP_SELF']       = TRUE;

  foreach ($url as $key => $val)
  {
    if ( ! isset($_SERVER[$key]))
    {
      continue;
    }
    
    if (strpos($_SERVER[$key], INDEX) && is_false($val))
    {
      continue;
    }
    
    $url = $_SERVER[$key];
    break;
  }


  $base = array();
  
  $base['ORIG_SCRIPT_NAME'] = TRUE;
  #$base['SCRIPT_FILENAME'] = TRUE;
  $base['SCRIPT_NAME']      = TRUE;
  $base['PHP_SELF']         = FALSE;

  foreach ($base as $key => $val)
  {
    if ( ! isset($_SERVER[$key]))
    {
      continue;
    }
    
    if (strpos($_SERVER[$key], INDEX) && is_false($val))
    {
      continue;
    }
    
    $base = $_SERVER[$key];
    break;
  }



  // ----------------------------------------------------------------------------

  // site root
  $base = preg_replace(sprintf('/%s.*$/', preg_quote(INDEX)), '', $base);

  if (($root = server('DOCUMENT_ROOT')) <> '/')
  {
    $base = str_replace($root, '.', $base);
  }
  
  define('ROOT', strtr(str_replace(INDEX, '', $base), '\\./', '/'));


  if (option('query'))
  {
    $parts = '';
    
    // fallback
    foreach ($_GET as $key => $val)
    {
      if (substr($key, 0, 1) === '/')
      {
        unset($_GET[$key]);
        $parts = $key;
        break;
      }
    }
  }
  else
  {
    // URL cleanup
    $root   = preg_quote(ROOT, '/');
    $index  = preg_quote(INDEX, '/');
    $suffix = option('rewrite') ? preg_quote(option('suffix'), '/') : '';
    $parts  = preg_replace("/^(?:$root(?:$index)?)?|$suffix$/", '', array_shift(explode('?', $url)));
  }
  
  define('URI', '/' . trim($parts, '/'));


  if (empty($_SERVER['REQUEST_URI']))
  {//FIX
    $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
    $_SERVER['REQUEST_URI'] .= $query = server('QUERY_STRING') ? "?$query" : '';
  }
  
  
  // huh stop!
  if (headers_sent($file, $line))
  {
    raise(ln('headers_sent', array('script' => $file, 'number' => $line)));
  }

  
  // method override
  if ($_method = value($_POST, '_method'))
  {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_method);
    
    unset($_POST['_method']);
  }
  
  // CRSF token
  define('TOKEN', sprintf('%d %s', time(), sha1(salt(13))));
  
  ignore_user_abort(FALSE);
});

/* EOF: ./lib/tetl/server/initialize.php */