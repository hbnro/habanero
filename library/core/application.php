<?php

/**
 * Application common library
 */

/**
 * Retrieve a single item from the global configuration
 *
 * @param  mixed Key or name
 * @param  mixed Default value
 * @return mixed
 */
function option($get, $or = FALSE)
{
  $from   = config();
  $output = value($from, $get, $or);

  return $output;
}


/**
 * Assign items to the global configuration
 *
 * @param     mixed Key or name|Array
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
 * Load a single library file
 *
 * @param     string Key or name
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


  if ( ! is_file($helper_path))
  {
    raise(ln('file_not_exists', array('name' => $helper_path)));
  }
  elseif (is_loaded($helper_path))
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
            'arguments'   => array(),
            'middleware'  => array(),
            'environment' => '',
          );
  
  
  if (defined('BEGIN'))
  {
    raise(ln('application_error'));
  }


  require LIB.DS.'core'.DS.'initialize'.EXT;


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

  $params = extend($defs, $params);
  
  $callback = $params['bootstrap'];
  
  $params['environment'] = $params['environment'] ?: option('environment', 'testing');
  
  foreach ((array) $params['middleware'] as $one)
  {
    if (is_closure($one))
    {//FIX
      $callback = call_user_func($one, $callback, $params['environment']);
    }
  }

  return call_user_func_array($callback, (array) $params['arguments']);
}


/**
 * Hook function utility
 *
 * @param     string  Key or name
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
    foreach ($set[$event] as $callback)
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
    array_unshift($set[$event], $bind);
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
  $var['route']     = IS_CLI ? @array_shift($_SERVER['argv']) : server(TRUE, server('REQUEST_URI'));
  
  // raw headers
  $var['headers']   = array();

  foreach (headers_list() as $one)
  {
    list($key, $val) = explode(':', $one);

    $var['headers'][$key] = trim($val);
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

  // app globals
  global $GLOBALS;
  $var['global'] = $GLOBALS;

  foreach ($missing = array('_SERVER', 'GLOBALS') as $one)
  {
    if (isset($var['global'][$one]))
    {
      unset($var['global'][$one]);
    }
  }
  
  
  // invoke custom handler
  trigger(__FUNCTION__, TRUE, $var);


  // output
  $type = IS_CLI ? 'txt' : 'html';
  $output = render(array(
    'partial' => LIB.DS.'assets'.DS.'views'.DS."raise.$type".EXT,
    'locals' => $var,
  ));
  
  $output = IS_CLI ? unents($output) : $output;

  render($output);
}


/**
 * Merge two ore more arrays
 *
 * @param  array Default array|...
 * @return array
 */
function extend($base)
{
  $test = array_filter(array_slice(func_get_args(), 1), 'is_assoc');

  foreach ($test as $set)
  {
    foreach ($set as $key => $val)
    {
      NULL !== $val && $base[$key] = $val;
    }
  }

  return $base;
}


/**
 * Anonymous functions
 *
 * @param  mixed Function callback
 * @return mixed
 */
function lambda($function)
{
  if ( ! is_closure($function))
  {
    raise(ln('failed_to_execute', array('callback' => dump($function))));
  }


  $args   = array_slice(func_get_args(), 1);
  $result = call_user_func_array($function, $args);

  return $result;
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


    $tokens = array(
      '/\\\\\*([a-z_][a-z\d_]*?)(?=\b)/i' => '(?<\\1>.+?)',
      '/\\\:([a-z_][a-z\d_]*?)(?=\b)/i' => '(?<\\1>[^\/]+?)',
      '/%s/' => '(?<=\W|^)(\w*[\d' . $latin . RFC_CHARS . ']+\w*)(?=\W|$)',
      '/%r/' => '([\d' . $latin . RFC_CHARS . ']+?)',
      '/%R/' => '[^\d' . $latin . RFC_CHARS . ']+',
      '/%d/' => '(?<=\D|^)(-?[0-9\.,]+?)(?=\D|$)',
      '/%l/' => '([\d' . $latin . ']+?)',
      '/%L/' => '[^\d' . $latin . ']+',
      '/%X/' => '\d' . $latin . RFC_CHARS,
      '/%x/' => '\d' . $latin,
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
 * @param  scalar Key or name|Expression
 * @param  mixed  Default value
 * @return mixed
 */
function value($from, $that = NULL, $or = FALSE)
{
  if ( ! is_iterable($from))
  {
    return $or;
  }
  elseif ($offset = strpos($that, '[]'))
  {//FIX
    return value($from, ($offset = strrpos($that, '[')) > 0 ? substr($that, 0, $offset) : '', $or);
  }
  elseif (preg_match_all('/\[([^\[\]]+)\]/U', $that, $matches) OR
         ($matches[1] = explode('.', $that)))
  {
    $key = ($offset = strrpos($that, '[')) > 0 ? substr($that, 0, $offset) : '';
    
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
 * Basic symbol tokenizer to deal with code
 *
 * @param  string String
 * @return array
 */
function tokenize($code)
{
  $sym = FALSE;
  $out = array();
  $set = token_get_all('<' . "?php $code");


  foreach ($set as $val)
  {
    if ( ! is_array($val))
    {
      $out []= $val;
    }
    else
    {
      switch ($val[0])
      { // intentionally on cascade
        case preg_match('/^(?:empty|array|list)$/', $val[1]) > 0;
        case function_exists($val[1]);
        case T_VARIABLE; // $var

        case T_BOOLEAN_AND; // &&
        case T_LOGICAL_AND; // and
        case T_BOOLEAN_OR; // ||
        case T_LOGICAL_OR; // or

        case T_CONSTANT_ENCAPSED_STRING; // "foo" or 'bar'
        case T_ENCAPSED_AND_WHITESPACE; // " $a "
        case T_PAAMAYIM_NEKUDOTAYIM; // ::
        case T_DOUBLE_COLON; // ::

        case T_LIST; // list()
        case T_ISSET; // isset()
        case T_OBJECT_OPERATOR; // ->
        case T_OBJECT_CAST; // (object)
        case T_DOUBLE_ARROW; // =>
        case T_ARRAY_CAST; // (array)
        case T_ARRAY; // array()

        case T_INT_CAST; // (int) or (integer)
        case T_BOOL_CAST; // (bool) or (boolean)
        case T_DOUBLE_CAST; // (real), (double), or (float)
        case T_STRING_CAST; // (string)
        case T_STRING; // "candy"

        case T_DEC; // --
        case T_INC; // ++
        case T_DNUMBER; // 0.12, etc.
        case T_LNUMBER; // 123, 012, 0x1ac, etc.
        case T_NUM_STRING; // "$x[0]"

        case T_IS_EQUAL; // ==
        case T_IS_GREATER_OR_EQUAL; // >=
        case T_IS_SMALLER_OR_EQUAL; // <=
        case T_IS_NOT_IDENTICAL; // !==
        case T_IS_IDENTICAL; // ===
        case T_IS_NOT_EQUAL; // != or <>
        
          $out []= $val[1];
        break;

        default;
        break;
      }
    }
  }
  return $out;
}

/* EOF: ./core/application.php */
