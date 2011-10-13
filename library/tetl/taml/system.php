<?php

/**
 * Template markup library
 */

class taml extends prototype
{

  /**#@+
   * @ignore
   */

  // custom tags
  private static $stack = array();

  // open blocks
  private static $open = '(?:if|else(?:\s*if)?|while|switch|for(?:each)?)';

  // defaults
  private static $defs = array(
    'default' => 'div',
    'prefix' => '',
    'indent' => 2,
    'path' => TMP,
  );

  /**#@-*/



  /**
   * Set configuration
   *
   * @param  mixed Key|Hash
   * @param  mixed Value
   * @return void
   */
  final public static function setup($key, $value = '') {
    if (is_assoc($key)) {
      static::$defs = array_merge(static::$defs, $key);
    } elseif (array_key_exists($key, static::$defs)) {
      static::$defs[$key] = $value;
    }
  }


  /**
   * Render file
   *
   * @param  string Filepath
   * @param  array  Local vars
   * @return string
   */
  final public static function render($file, array $vars = array()) {
    if ( ! is_file($file)) {
      return FALSE;
    }


    $php_file = static::$defs['path'].DS.md5($file).EXT;

    if (is_file($php_file)) {
      if (filemtime($file) > filemtime($php_file)) {
        unlink($php_file);
      }
    }


    if ( ! is_file($php_file)) {// intentionally hidden
      $out = static::parse(read($file), $file);
      write($php_file, $out);
    }

    return render($php_file, TRUE, array(
      'locals' => $vars,
    ));
  }


  /**
   * Parse markup
   *
   * @param     string  Taml template
   * @staticvar array   Replacement fixes
   * @return    mixed
   */
  final public static function parse($text) {
    static $fix = array(
              '/\s*<\?/' => '<?',
              '/\?>\s*<\//' => '?></',
              '/\s*(?=[\r\n])/s' => '',
              '/\s*<!--#CONCAT#-->/s' => '',
              '/\s*<!--#PRE#-->(\s*\|\s)?/m' => "\n",
              '/<([\w:-]+)([^<>]*)>[|\s]*([^<>]+?)\s*<\/\\1>/s' => '<\\1\\2>\\3</\\1>',
              '/<\?php\s+(?!echo\s+|\})/' => "\n<?php ",
              '/}\s*else\s*/s' => '} else ',
              '/><\?php/' => ">\n<?php",
              '/\s+\|\s/m' => "\n",
            );


    $code  = '';
    $stack = array();

    $test  = array_filter(explode("\n", $text));
    $file  = func_num_args() > 1 ? func_get_arg(1) : '';


    foreach ($test as $i => $line) {
      $key    = '$out';
      $tab    = strlen($line) - strlen(ltrim($line));
      $next   = isset($test[$i + 1]) ? $test[$i + 1] : NULL;
      $indent = strlen($next) - strlen(ltrim($next));

      if ( ! trim($line)) {
        continue;
      } elseif ($tab && ($tab % static::$defs['indent'])) {
        return static::error($i, $line, 'bad_space', $file);
      }


      if ($indent > $tab) {
        $stack []= substr(mt_rand(), 0, 7);
      }

      foreach ($stack as $top) {
        $key .= "['$top']";
      }

      if ($indent < $tab) {
        $dec = $tab - $indent;

        while ($dec > 0) {
          array_pop($stack);
          $dec -= static::$defs['indent'];
        }
      }

      $code .= "$key ";

      $line  = addslashes(rtrim($line));

      $code .= $indent > $tab ? "= array(-1 => '$line')" : "[]= '$line'";
      $code .= ";\n";
    }


    @eval($code);

    if (empty($out)) {
      return FALSE;
    }

    $out = ents(static::compile($out));
    $out = preg_replace(array_keys($fix), $fix, $out);
    $out = preg_replace_callback('/%\{([^{}]+?)\}/', 'taml::value', $out);

    return $out;
  }


  /**
   * Register custom tags
   *
   * @param  string Shortcut
   * @param  mixed  Remplacement
   * @return void
   */
  final public static function expand($tag, $with, array $args = array()) {
    static::$stack[$tag] = sprintf("%s$with(%s)", static::$defs['prefix'], trim(attrs($args)));
  }



  /**#@+
   * @ignore
   */

  // variable interpolation
  final private static function value($match) {
    return sprintf('<?php echo %s; ?>', join(' ', static::tokenize($match[1])));
  }

  // compile lines
  final private static function compile($tree) {
    $out  = array();
    $expr = sprintf('-\s+%s', static::$open);

    if ( ! empty($tree[-1])) {
      $key       = $tree[-1];
      $sub[$key] = array_slice($tree, 1);

      if (preg_match("/^\s*$expr/", $key)) {
        $sub[$key] []= '- }';
      }
      $out []= static::compile($sub);
    } else {
      foreach ($tree as $key => $value) {
        if ( ! is_scalar($value)) {
          continue;
        } elseif (preg_match("/^\s*$expr/", $value)) {
          $tree []= '- }';
        }
      }


      foreach ($tree as $key => $value) {
        if (is_string($value)) {
          $out []= static::line(trim($value));
          continue;
        } elseif (substr(trim($key), 0, 3) === 'pre') {
          $indent = strlen($key) - strlen(ltrim($key));
          $value  = tag('pre', '', join("\n", static::flatten($value)));
          $out  []= preg_replace("/^\s{{$indent}}/m", '<!--#PRE#-->', $value);
          continue;
        } elseif (preg_match('/^(\s*):(\w+)\s*$/', $key, $match)) {
          $out []= static::filter($match[2], $value, strlen($match[1]));
          continue;
        }

        $value = is_array($value) ? static::compile($value) : $value;
        $out []= static::line(trim($key), $value);
      }
    }

    $out = join("\n", array_filter($out));
    $out = preg_replace('/\?>\s*<\?php/', "\n", $out);

    return $out;
  }

  // filters
  final private static function filter($name, $value, $indent = 0) {
    $plain = trim(join("\n", static::flatten($value)));

    switch ($name) {
      case 'php';
        $value = sprintf('<?php %s ?>', $plain);
      break;
      case 'css';
        $value = tag('style', '', $plain);
      break;
      case 'cdata';
        $value = sprintf('<![CDATA[%s]]>', $plain);
      break;
      case 'javascript';
        $value = tag('script', '', $plain);
      break;
      default;
        raise(ln('taml.unknown_filter', array('name' => $name)));
      break;
    }
    return $value;
  }

  // parse single line
  final private static function line($key, $text = '') {
    static $tags = NULL;


    if (is_null($tags)) {
      $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
      $test = array_merge($test['empty'], $test['complete']);
      $tags = sprintf('(%s)', join('|', $test));
    }


    switch (substr($key, 0, 1)) {
      case '!';
        return $key === '!doctype' ? "<$key html>" : $key;
      break;
      case '/';
        // <!-- ... -->
        return sprintf("<!--%s-->$text", trim(substr($key, 1)));
      break;
      case '|';
        return sprintf("<!--#CONCAT#-->%s$text", substr($key, 1));
      break;case '<';
        // html
        return stripslashes($key . $text);
      break;
      case '-';
        // php
        $key   = stripslashes(substr($key, 1));
        $key   = rtrim(join(' ', static::tokenize($key)), ';');
        $close = preg_match(sprintf('/^\s*%s/', static::$open), $key) ? ' {' : '';

        return preg_replace('/^/m', '  ', "<?php $key$close ?>\n$text");
      break;
      case '=';
        // print
        $key = stripslashes(trim(substr($key, 1)));
        $key = rtrim(join(' ', static::tokenize($key)), ';');

        return preg_replace('/^/m', '  ', "<?php echo $key; ?>$text");
      break;
      default;
        $tag  = '';
        $args = array();

        $key  = strtr($key, static::$stack);
        $key  = preg_replace('/\)\s*\(/', ' ', $key);


        // tag name
        preg_match(sprintf('/^%s%s\b/', static::$defs['prefix'], $tags), $key, $match);

        if ( ! empty($match[0])) {
          $key = str_replace($match[0], '', $key);
          $tag = $match[1];
        }

        // attributes (raw)
        preg_match('/^[#.][.\w:-]+/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = str_replace($match[0], '', $key);
          $args = args(attrs($match[0]));
          $tag  = static::$defs['default'];
        }

        // attributes {hash => val}
        preg_match('/(?<!%)\{([^{}]+)\}/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = str_replace($match[0], '', $key);
          $hash = join('', static::tokenize($match[1]));

          @eval("\$args=array($hash);");
        }

        // other (hash="val")
        preg_match('/\(([^()]+)\)/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = str_replace($match[0], '', $key);
          $args = args(stripslashes($match[1]));
        }

        // output
        preg_match('/^\s*=.+?/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = stripslashes(trim(substr(trim($key), 1)));
          $text = preg_replace('/^/m', '  ', "<?php echo $key; ?>$text");
        } elseif ( ! is_numeric($key)) {
          $text = stripslashes(trim($key)) . $text;
        }


        $out = $tag ? tag($tag, $args, "\n$text\n") : $text;
        $out = preg_replace('/^/m', str_repeat(' ', static::$defs['indent']), $out);

        return $out;
      break;
    }
  }

  // retrieve expression tokens
  final private static function tokenize($code) {
    static $expr = array(
              'array',
              'empty',
              'list',
            );


    $sym = FALSE;
    $out = array();
    $set = token_get_all('<' . "?php $code");


    foreach ($set as $val) {
      if ( ! is_array($val)) {
        $out []= $val;
      } else {
        switch ($val[0]) { // intentionally on cascade
          case function_exists($val[1]);
          case in_array($val[1], $expr);
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
          case T_CONCAT_EQUAL; // .=
          case T_DIV_EQUAL; // /=
          case T_MUL_EQUAL; // *=
          case T_MINUS_EQUAL; // -=
          case T_PLUS_EQUAL; // +=

          case T_IF; // if
          case T_AS; // as
          case T_FOR; // for
          case T_FOREACH; // foreach
          case T_ELSE; // else case T_ELSEIF; // elseif
          case T_SWITCH; // switch
          case T_CASE; // case
          case T_BREAK; // break
          case T_DEFAULT; // default
          case T_WHILE; // while
          case T_ENDFOR; // endfor
          case T_ENDFOREACH; // endforeach
          case T_ENDIF; // endif
          case T_ENDSWITCH; // endswitch
          case T_ENDWHILE; // endwhile

            $out []= $val[1];
          break;

          default;
          break;
        }
      }
    }
    return $out;
  }

  // flatten array
  final private static function flatten($set, $out = array()) {
    foreach ($set as $one) {
      is_array($one) ? $out = static::flatten($one, $out) : $out []= $one;
    }
    return $out;
  }

  // errors
  final private static function error($i, $line, $desc = 'unknown', $file = '') {
    $error   = is_file($file) ? 'taml.error_file' : 'taml.error_line';
    $message = ln($error, array('line' => $i, 'text' => $line, 'name' => $file));
    $desc    = ln("taml.$desc");

    raise("$message ($desc)");
  }

  /**#@-*/
}

/* EOF: ./library/tetl/taml/system.php */
