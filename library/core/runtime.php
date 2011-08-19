<?php

/**
 * Application common library
 */

/**
 * Load a single library file
 *
 * @param     string Identifier
 * @staticvar array  Helper bag
 * @return    void
 */
function uses($lib)
{
  static $set = array();

  
  $lib = strtr($lib, '_./', DS);
  
  if (in_array($lib, $set))
  {
    return FALSE;
  }

  $test   = (array) option('import_path', array());
  $test []= LIB.DS.'lib'.DS;


  foreach ($test as $dir)
  {
    $helper_path  = $dir.$lib;
    $helper_path .= is_dir($helper_path) ? DS.'initialize'.EXT : EXT;

    if (is_file($helper_path))
    {
      break;
    }
  }

  // fallback, do not use i18n...
  if (is_loaded($helper_path))
  {
    return FALSE;
  }

  /**
    * @ignore
    */
  require $helper_path;

  $set []= $lib;

  return TRUE;
}


/**
 * Application bootstrap wrapper
 *
 * @param     mixed   Function callback
 * @param     array   Options hash
 * @return    void
 */
function run(Closure $bootstrap, array $params = array())
{
  static $defs = array(
            'bootstrap'   => 'raise',
            'middleware'  => array(),
            'environment' => array(),
          );
  
  
  require_once LIB.DS.'core'.DS.'initialize'.EXT;
  
  if (defined('BEGIN'))
  {
    raise(ln('application_error'));
  }


  // start
  define('BEGIN', ticks());


  if (is_assoc($bootstrap))
  {
    $params += $bootstrap;
  }
  elseif ( ! isset($params['bootstrap']))
  {
    $params['bootstrap'] = $bootstrap;
  }

  $params += $defs;
  
  $callback = $params['bootstrap'];
  
  $params['environment'] = $params['environment'] ?: option('environment', 'testing');
  
  foreach ((array) $params['middleware'] as $one)
  {
    if (is_callable($one))
    {
      $callback = call_user_func($one, $callback);
    }
  }

  return call_user_func_array($callback, (array) $params['environment']);
}


/**
 * Hook function utility
 *
 * @param     string  Identifier
 * @param     mixed   Function callback|Apply hook?
 * @param     mixed   Optional function arguments
 * @staticvar array   Hook bag
 * @return    boolean
 */
function trigger($event, $bind, array $args = array())
{
  static $set = array();


  $test = func_get_args();

  if (sizeof($args) > 3)
  {
    $args = array_slice($test, 2);
  }


  if ( ! isset($set[$event]))
  {
    $set[$event] = array();
  }


  if (is_true($bind))
  {
    foreach (array_reverse($set[$event]) as $callback)
    {
      call_user_func_array($callback, $args);
    }
  }
  elseif (is_null($bind))
  {
    $set[$event] = array();
  }
  elseif ( ! is_callable($bind))
  {
    return FALSE;
  }
  else
  {
    $set[$event] []= $bind;
  }

  return TRUE;
}


/**
 * Raise a user level exception
 *
 * @param  array Description or exception
 * @return void
 */
function raise($message)
{
  if (is_closure($message))
  {
    return trigger(__FUNCTION__, $message);
  }
  
  
  $var   = array();
  $args  = func_get_args();
  $trace = array_slice(debug_backtrace(), 1);

  
  // finalize opened buffers
  while (ob_get_level())
  {
    ob_end_clean();
  }

  if ( ! empty($GLOBALS['--raise-message']))
  {
    $message = $GLOBALS['--raise-message'];
    unset($GLOBALS['--raise-message']);
  }

  
  foreach ($trace as $i => $on)
  {
    $type   = ! empty($on['type']) ? $on['type'] : '';
    $system = ! empty($on['file']) && strstr($on['file'], LIB) ?: FALSE;
    $prefix = ! empty($on['object']) ? get_class($on['object']) : ( ! empty($on['class']) ? $on['class'] : '');

    $call   = $prefix . $type . $on['function'];


    // - app source
    // + system source
    // ~ unknown source

    $format_str = ($true = ! empty($on['file'])) ? '%s %s#%d %s()' : '~ %4$s';
    $format_val = sprintf($format_str, is_true($system) ? '+' : '-', $true ? $on['file'] : '', $true ? $on['line'] : '', $call);

    $trace[$i]  = $format_val;
  }

  $var['message']   = dump($message);
  $var['backtrace'] = array_reverse($trace);
  $var['route']     = IS_CLI ? @array_shift($_SERVER['argv']) : value($_SERVER, 'REQUEST_URI');
  
  if ( ! IS_CLI)
  {
    // raw headers
    $var['headers']   = array();
  
    foreach (headers_list() as $one)
    {
      list($key, $val) = explode(':', $one);
  
      $var['headers'][$key] = trim($val);
    }
  }


  // system info
  $var['host'] = php_uname('n');
  $var['user'] = 'Unknown';

  foreach (array('USER', 'LOGNAME', 'USERNAME', 'APACHE_RUN_USER') as $key)
  {
    if ($one = @getenv($key))
    {
      $var['user'] = $one;
    }
  }


  // environment info
  $var['env'] = $_SERVER;

  foreach (array('PATH_TRANSLATED', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'argc', 'argv') as $key)
  {
    if (isset($var['env'][$key]))
    {
      unset($var['env'][$key]);
    }
  }

  foreach ((array) $var['env'] as $key => $val)
  {
    if (preg_match('/^(?:PHP|HTTP|SCRIPT)/', $key))
    {
      unset($var['env'][$key]);
    }
  }

  // invoke custom handler
  trigger(__FUNCTION__, TRUE, $var);


  // output
  $type     = IS_CLI ? 'txt' : 'html';
  $inc_file = LIB.DS.'assets'.DS.'views'.DS."raise.$type".EXT;
  
  $output = call_user_func(function()
    use($inc_file, $var)
  {
    extract($var);
    require $inc_file;
  }, $var);
  
  die($output);
}


/**
 * Retrieve a single item from the global configuration
 *
 * @param  mixed Identifier
 * @param  mixed Default value
 * @return mixed
 */
function option($get, $or = FALSE)
{
  return value(config(), $get, $or);
}


/**
 * Assign items to the global configuration
 *
 * @param     mixed Identifier|Array
 * @param     mixed Default value
 * @staticvar array Config bag
 * @return    mixed
 */
function config($set = NULL, $value = NULL)
{
  static $bag = array();


  if ( ! func_num_args())
  {
    return $bag;
  }
  elseif (is_assoc($set))
  {
    foreach ($set as $key => $value)
    {
      config($key, $value);
    }
  }
  elseif (func_num_args() === 1 && isset($bag[$set]))
  {
    return $bag[$set];
  }
  elseif (is_file($set))
  {
    $config = array();
    $test   = include $set;


    if (is_array($config))
    {
      if (is_array($test))
      {
        $config += $test;
      }
      $bag += $config;
    }
  }
  elseif ( ! is_num($set))
  {
    $bag[$set] = $value;
  }
}


/**
 * Common wildcard filter matching
 *
 * @param     string Expression
 * @param     string Input test
 * @param     array  Mixed rules
 * @staticvar array  Token bag
 * @return    mixed
 */
function match($expr, $subject = NULL, array $constraints = array())
{
  static $tokens = NULL;

  
  if (is_null($tokens))
  {
    $latin = '\pL';

    if ( ! IS_UNICODE)
    {
      $latin  = 'a-zA-Z€$';
      $latin .= 'âêîôûÂÊÎÔÛÄËÏÖÜäëïöü';
      $latin .= 'áéíóúÁÉÍÓÚñÑÙÒÌÈÀùòìèàŷŶŸÿ';
    }


    // TODO: sure thing?
    $chars  = preg_quote(RFC_CHARS, '/');
    
    $tokens = array(
      '/\\\\\*([a-z_][a-z\d_]*?)(?=\b)/i' => '(?<\\1>.+?)',
      '/\\\:([a-z_][a-z\d_]*?)(?=\b)/i' => '(?<\\1>[^\/]+?)',
      '/%s/' => '(?<=\W|^)(\w*[\d' . $latin . $chars . ']+\w*)(?=\W|$)',
      '/%r/' => '([\d' . $latin . $chars . ']+?)',
      '/%R/' => '[^\d' . $latin . $chars . ']+',
      '/%d/' => '(?<=\D|^)(-?[0-9\.,]+?)(?=\D|$)',
      '/%g/' => '([\d' . $latin . ']+?)',
      '/%G/' => '[^\d' . $latin . ']+',
      '/%l/' => '\d' . $latin . $chars,
      '/%L/' => '\d' . $latin,
      '/\\\\([\^|bws+$?[\]])/i' => '\\1',
      '/\\\\\*/' => '(.+?|())',
      '/\\\\\)/' => '|())',
      '/\\\\\(/' => '(?:',
    );
  }


  $expr = preg_quote($expr, '/');

  if (is_array($constraints))
  {
    $test = array();

    foreach ($constraints as $item => $value)
    {
      if (is_num($as = preg_replace('/[^a-z\d_]/', '', $item)))
      {
        continue;
      }

      $item  = preg_quote($item, '/');
      $value = strtr($value, '/', '\\/');
      $expr  = str_replace($item, "(?<$as>$value)", $expr);
    }
  }


  $regex = preg_replace(array_keys($tokens), $tokens, $expr);
  
  if (func_num_args() === 1)
  {
    return "/$regex/";
  }
  elseif (@preg_match("/$regex/u", $subject, $matches))
  {
    return $matches;
  }

  return FALSE;
}


/**
 * Variable interpolation access
 *
 * @param  array  Object or array
 * @param  scalar Identifier|Expression
 * @param  mixed  Default value
 * @return mixed
 */
function value($from, $that = NULL, $or = FALSE)
{
  if ( ! is_iterable($from))
  {
    return $or;
  }
  elseif (($from = (array) $from) && isset($from[$that]))
  {//FIX
    return $from[$that] ?: $or;
  }
  elseif (preg_match_all('/\[([^\[\]]*)\]/U', $that, $matches) OR
         ($matches[1] = explode('.', $that)))
  {
    $key = ($offset = strpos($that, '[')) > 0 ? substr($that, 0, $offset) : '';
    
    if ( ! empty($key))
    {
      array_unshift($matches[1], $key);
    }
    
    $key   = array_shift($matches[1]);
    $get   = join('.', $matches[1]);
    $depth = sizeof($matches[1]);

    if (is_object($from) && isset($from->$key))
    {
      $tmp = $from->$key;
    }
    elseif (is_array($from) && isset($from[$key]))
    {
      $tmp = $from[$key];
    }
    else
    {
      $tmp = $or;
    }

    $value = ! $depth ? $tmp : value($tmp, $get, $or);
    
    return $value;
  }
}


/**
 * Callback debug inspection
 *
 * @link   http://php.net/manual/en/class.reflectionfunction.php
 * @param  mixed Function callback
 * @return string
 **/
function reflection($lambda)
{
  if (is_array($lambda))
  {
    list($class, $method) = $lambda;
    return new ReflectionMethod($class, $method);
  }

  if (is_string($lambda) && ! is_false(strpos($lambda, ':')))
  {
    list($class, $method) = explode(':', $lambda);
    return new ReflectionMethod($class, $method);
  }

  if (method_exists($lambda, '__invoke'))
  {
    return new ReflectionMethod($lambda, '__invoke');
  }
  return new ReflectionFunction($lambda);
}


/**
 * Variable debug
 *
 * @param     mixed   Expression
 * @param     boolean Print?
 * @param     integer Recursion limit
 * @staticvar array   Replace set
 * @return    mixed
 */
function dump($var, $show = FALSE, $deep = 99)
{
  static $repl = array(
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
          );


  if ( ! $deep)
  {
    return FALSE;
  }

  $depth     = func_num_args() > 3 ? func_get_arg(3) : 0;
  $tab       = str_repeat('  ', $depth);


  $arrow     = is_true($show) ? ' ' : ' => ';
  $separator = is_true($show) ? "\n" : ', ';
  $newline   = is_true($show) ? "\n" : ' ';

  $out       = array();


  if (is_null($var))
  {
    $out []= 'NULL';
  }
  elseif (is_bool($var))
  {
    $out []= is_true($var) ? 'TRUE' : 'FALSE';
  }
  elseif (is_scalar($var))
  {
    $out []= strtr($var, $repl);
  }
  elseif (is_callable($var))
  {
    $args = array();
    $code = reflection($var);

    foreach ($code->getParameters() as $one)
    {
      $args []= "\${$one->name}";
    }

    $out []= 'Args[ ' . join(', ', $args) . ' ]';
  }
  elseif (is_iterable($var))
  {
    $width = 0;
    $test  = (array) $var;
    $max   = sizeof($test);

    if (is_false($show))
    {
      $tab = '';
    }
    else
    {
      foreach (array_keys($test) as $key)
      {
        if (($cur = strlen($key)) > $width)
        {
          $width = $cur;
        }
      }
    }

    foreach ($test as $key => $val)
    {
      $old   = call_user_func(__FUNCTION__, $val, FALSE, $deep - 1, $depth + 1);
      $pre   = ! is_num($key) ? $key : str_pad($key, strlen($max), ' ', STR_PAD_LEFT);

      $out []= sprintf("$tab%-{$width}s$arrow", $pre) . $old;
    }
  }

  $class = is_object($var) ? get_class($var) : '';
  $type  = sprintf('#<%s%s!empty>', gettype($var), $class ? ":$class" : '');
  $out   = sizeof($out) ? (($str = join($separator, $out)) === '' ? $type : $str) : (is_true($show) ? $type : '');

  if (is_object($var) && is_false($show))
  {
    $out = sprintf("{{$newline}$out$newline}(%s)", get_class($var));
  }
  elseif (is_array($var) && is_false($show))
  {
    $out = "[$newline$out$newline]";
  }
  
  
  if (is_true($show) && $depth <= 0)
  {
    $out = IS_CLI ? $out : htmlspecialchars($out);
    echo IS_CLI ? $out : "\n<pre>$out</pre>";
    return TRUE;
  }
  return $out;
}


/**
 * Benchmark ticker
 *
 * @param  float   Initial cue
 * @param  float   End cue
 * @param  integer Decimal
 * @return float
 */
function ticks($start = NULL, $end = FALSE, $round = 4)
{
  if (func_num_args() == 0) 
  {
    return microtime(TRUE);
  }
  elseif (func_num_args() == 1) 
  {
    $end = microtime(TRUE);
  }

  return round(max($end, $start) - min($end, $start), $round);
}

/* EOF: ./core/runtime.php */
