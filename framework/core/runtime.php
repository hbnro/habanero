<?php

/**
 * Application common library
 */

/**
 * Add classy patch
 *
 * @param     mixed Array|Function callback
 * @staticvar array Patch stack
 * @return    void
 */
function rescue($with = NULL) {
  static $patch = array();


  if (func_num_args() === 0) {
    return $patch;
  }

  $patch []= $with;
}


/**
 * Load a single library file
 *
 * @param     string Identifier
 * @staticvar array  Helper bag
 * @return    void
 */
function import($lib) {
  bootstrap::enhance($lib);
}


/**
 * Application bootstrap wrapper
 *
 * @param  mixed Function callback
 * @return void
 */
function run(Closure $bootstrap) {
  bootstrap::execute($bootstrap);
}


/**
 * Load partial content
 *
 * @param  mixed Content file|Options hash
 * @param  mixed Partial?|Options hash
 * @param  array Options hash
 * @return mixed
 */
function render($content, $partial = FALSE, array $params = array()) {
  if (is_assoc($content)) {
    $params = array_merge($content, $params);
  } elseif ( ! isset($params['content'])) {
    $params['content'] = $content;
  }

  if (is_assoc($partial)) {
    $params = array_merge($partial, $params);
  } elseif ( ! isset($params['partial'])) {
    $params['partial'] = $partial;
  }


  $params = array_merge(array(
    'content' => '',
    'partial' => '',
    'output'  => '',
    'locals'  => array(),
  ), $params);

  if ( ! is_bool($params['partial'])) {
    $params['content'] = $params['partial'];
    $params['partial'] = TRUE;
  }


  if ( ! empty($params['output'])) {// intentionally plain response
    die($params['output']);
  } elseif ( ! is_file($params['content'])) {
    raise(ln('file_not_exists', array('name' => $params['content'])));
  }

  // curiously the last lambda render breaks! why?
  $output = function () {
    ob_start();

    extract(func_get_arg(1));
    require func_get_arg(0);

    return ob_get_clean();
  };

  $output = $output($params['content'], $params['locals']);

  if ($params['partial']) {
    return $output;
  }
  echo $output;
}


/**
 * Raise a user level exception
 *
 * @param  array Description or exception
 * @param  mixed Extra information
 * @return void
 */
function raise($message, $debug = NULL) {
  if (is_closure($message)) {// TODO: there is another way?
    return bootstrap::implement('raise', $message);
  }

  // invoke custom handler
  bootstrap::raise($message, $debug);
}


/**
 * Retrieve a single item from the global configuration
 *
 * @param  mixed Identifier
 * @param  mixed Default value
 * @return mixed
 */
function option($get, $or = FALSE) {
  return configure::get($get, $or);
}


/**
 * Assign items to the global configuration
 *
 * @param     mixed Identifier|Array
 * @param     mixed Default value
 * @staticvar array Config bag
 * @return    mixed
 */
function config($set = NULL, $value = NULL) {
  if (func_num_args() === 0) {
    return configure::all();
  } elseif ( ! is_null($value)) {
    configure::set($set, $value);
  } else {
    if ( ! is_assoc($set) && ! is_file($set)) {
      return configure::get($set);
    }
    configure::add($set);
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
function match($expr, $subject = NULL, array $constraints = array()) {
  static $tokens = NULL;


  if (is_null($tokens)) {
    $latin = '\pL';

    if ( ! IS_UNICODE) {
      $latin  = 'a-zA-Z€$';
      $latin .= 'âêîôûÂÊÎÔÛÄËÏÖÜäëïöü';
      $latin .= 'áéíóúÁÉÍÓÚñÑÙÒÌÈÀùòìèàŷŶŸÿ';
    }


    $chars  = preg_quote('$-_.+!*\'(),', '/');
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

  if (is_array($constraints)) {
    $test = array();

    foreach ($constraints as $item => $value) {
      if (is_num($as = preg_replace('/[^a-z\d_]/', '', $item))) {
        continue;
      }

      $item  = preg_quote($item, '/');
      $value = strtr($value, '/', '\\/');
      $expr  = str_replace($item, "(?<$as>$value)", $expr);
    }
  }


  $regex = preg_replace(array_keys($tokens), $tokens, $expr);

  if (func_num_args() === 1) {
    return "/$regex/";
  } elseif (@preg_match("/$regex/u", $subject, $matches)) {
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
function value($from, $that = NULL, $or = FALSE) {
  if ( ! is_iterable($from)) {
    return $or;
  } elseif (($from = (array) $from) && isset($from[$that])) {//FIX
    return $from[$that] ?: $or;
  } elseif (preg_match_all('/\[([^\[\]]*)\]/U', $that, $matches) OR
         ($matches[1] = explode('.', $that))) {
    $key = ($offset = strpos($that, '[')) > 0 ? substr($that, 0, $offset) : '';

    if ( ! empty($key)) {
      array_unshift($matches[1], $key);
    }

    $key   = array_shift($matches[1]);
    $get   = join('.', $matches[1]);
    $depth = sizeof($matches[1]);

    if (is_object($from) && isset($from->$key)) {
      $tmp = $from->$key;
    } elseif (is_array($from) && isset($from[$key])) {
      $tmp = $from[$key];
    } else {
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
function reflection($lambda) {
  if (is_array($lambda)) {
    list($class, $method) = $lambda;
    return new ReflectionMethod($class, $method);
  }

  if (is_string($lambda) && ! is_false(strpos($lambda, '::'))) {
    list($class, $method) = explode('::', $lambda);
    return new ReflectionMethod($class, $method);
  }

  if (method_exists($lambda, '__invoke')) {
    return new ReflectionMethod($lambda, '__invoke');
  }
  return new Reflectionfunction ($lambda);
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
function dump($var, $show = FALSE, $depth = 99) {
  static $repl = array(
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
          );


  if ( ! $depth) {
    return FALSE;
  }

  $limit     = func_num_args() > 3 ? func_get_arg(3) : 0;
  $tab       = str_repeat('  ', $limit);


  $arrow     = $show ? ' ' : ' => ';
  $separator = $show ? "\n" : ', ';
  $newline   = $show ? "\n" : ' ';

  $out       = array();


  if (is_null($var)) {
    $out []= 'NULL';
  } elseif (is_bool($var)) {
    $out []= is_true($var) ? 'TRUE' : 'FALSE';
  } elseif (is_scalar($var)) {
    $out []= strtr($var, $repl);
  } elseif (is_callable($var)) {
    $args = array();
    $code = reflection($var);

    foreach ($code->getParameters() as $one) {
      $args []= "\${$one->name}";
    }

    $out []= 'Args[ ' . join(', ', $args) . ' ]';
  } elseif (is_iterable($var)) {
    $width = 0;
    $test  = (array) $var;
    $max   = sizeof($test);

    if ( ! $show) {
      $tab = '';
    } else {
      foreach ($test as $key => $val) {
        $key = preg_replace('/^\W.*?\W/', '', $key);

        if (($cur = strlen($key)) > $width) {
          $width = $cur;
        }
      }
    }

    foreach ($test as $key => $val) {
      $key = preg_replace('/^\W.*?\W/', '', $key);

      $old = dump($val, FALSE, $depth - 1, $limit + 1);
      $pre = ! is_num($key) ? $key : str_pad($key, strlen($max), ' ', STR_PAD_LEFT);

      $out []= sprintf("$tab%-{$width}s$arrow", $pre) . $old;
    }
  }

  $class = is_object($var) ? get_class($var) : '';
  $type  = sprintf('#<%s%s!empty>', gettype($var), $class ? ":$class" : '');
  $out   = sizeof($out) ? (($str = join($separator, $out)) === '' ? $type : $str) : ($show ? $type : '');

  if (is_object($var) && ! $show) {
    $out = sprintf("{{$newline}%s$newline}(%s)", $out, get_class($var));
  } elseif (is_array($var) && ! $show) {
    $out = "[$newline$out$newline]";
  }


  if ($show && $limit <= 0) {
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
function ticks($start = NULL, $end = FALSE, $round = 4) {
  if (func_num_args() == 0) {
    return microtime(TRUE);
  } elseif (func_num_args() == 1) {
    $end = microtime(TRUE);
  }

  return round(max($end, $start) - min($end, $start), $round);
}

/* EOF: ./framework/core/runtime.php */
