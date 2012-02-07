<?php

/**
 * Template markup library
 */

class taml extends prototype
{

  /**#@+
   * @ignore
   */

  // lambdas
  private static $fn = '(?<=[(,])\s*~\s*>(?=\b|$)';

  // quotes
  private static $qt = array(
                    '\\' => '<!--#BS#-->',
                    '"' => '<!--#QUOT#-->',
                    "'" => '<!--#APOS#-->',
                  );

  // code fixes
  private static $fix = array(
                    '/>\s*<\?/' => '><?',
                    '/\?>\s*<\//' => '?></',
                    '/\s*<\/pre>/s' => "\n</pre>",
                    '/<\?=\s*(.+?)\s*;?\s*\?>/' => '<?php echo \\1; ?>',
                    '/([(,])\s*([\w:-]+)\s*=>\s*/' => "\\1'\\2'=>",
                    '/<\?php\s+(?!echo\s+|\})/' => "\n<?php ",
                    '/\};?\s*else(?=if|\b)/' => '} else',
                    '/\s*<!--#PRE#-->\s*/s' => "\n",
                    '/^\s*\|(.*?)$/m' => '\\1',
                  );

  // open blocks
  private static $open = '(?:if|else(?:\s*if)?|while|switch|for(?:each)?)';

  // filter blocks
  private static $blocks = array();

  // content
  private static $source = NULL;

  // defaults
  protected static $defs = array(
    'indent' => 2,
  );

  /**#@-*/



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


    $php_file = TMP.DS.strtr($file, '\\/', '__');

    if (is_file($php_file)) {
      if (filemtime($file) > filemtime($php_file)) {
        unlink($php_file);
      }
    }


    if ( ! is_file($php_file)) {
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
   * @param  string Taml template
   * @return mixed
   */
  final public static function parse($text) {
    $code  = '';
    $stack = array();

    static::$source = $text;

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

      $code .= $key;

      $line  = static::escape(rtrim($line));

      $code .= $indent > $tab ? "=array(-1=>'$line')" : "[]='$line'";
      $code .= ";\n";
    }


    @eval($code);

    if (empty($out)) {
      return FALSE;
    }

    $out = static::compile($out);
    $out = static::fixate($out);

    return $out;
  }


  /**#@+
   * @ignore
   */

  // render markup tags
  final private static function markup($tag, $args, $text = '') {
    $merge = FALSE;

    foreach ($args as $i => $one) {
      if (is_array($one)) {
        $code = var_export($one, TRUE);
        $code = str_replace(",\n", ',', $code);
        $code = str_replace('array ', 'array', $code);
        $code = preg_replace("/\s*'([^']+)'\s*=>\s*/s", "'\\1'=>", $code);

        $args[$i] = $code;

        $merge = TRUE;
      } else {
        $args[$i] = "array({$args[$i]})";
      }
    }

    $args = join(',', $args);
    $hash = md5($tag . $args . ticks());

    $out  = $hash . tag($tag, '', $text);
    $merge && $args = "array_merge($args)";

    $repl = "<$tag<?php echo attrs($args); ?>";

    $args && $out = str_replace("$hash<$tag", $repl, $out);

    $out  = str_replace($hash, '', $out);

    return $out;
  }

  // variable interpolation
  final private static function value($match) {
    return sprintf('<?php echo %s; ?>', join(' ', static::tokenize($match[1])));
  }

  // compile lines
  final private static function compile($tree) {
    $open  = sprintf('/^\s*-\s*%s/', static::$open);
    $block = sprintf('/[-=].+?%s/', static::$fn);
    $out   = array();

    if ( ! empty($tree[-1])) {
      $sub[$tree[-1]] = array_slice($tree, 1);

      if (preg_match($block, $tree[-1])) {
        $sub[$tree[-1]] []= '- })';
      } elseif (preg_match($open, $tree[-1])) {
        $sub[$tree[-1]] []= '- }';
      }

      $out []= static::compile($sub);
    } else {
      foreach ($tree as $key => $value) {
        if ( ! is_scalar($value)) {
          continue;
        } elseif (preg_match($block, $value)) {
          $tree []= '- })';
        } elseif (preg_match($open, $value)) {
          $tree []= '- }';
        }
      }


      foreach ($tree as $key => $value) {
        $indent = strlen($key) - strlen(ltrim($key));

        if (is_string($value)) {
          $out []= static::line($value, '', $indent - static::$defs['indent']);
          continue;
        } elseif (substr(trim($key), 0, 3) === 'pre') {
          $value = join("\n", static::flatten($value));
          $value = preg_replace("/^\s{{$indent}}/m", '<!--#PRE#-->', $value);
        }

        $value = is_array($value) ? static::compile($value) : $value;
        $out []= static::line($key, $value, $indent);
      }
    }

    $out = join("\n", array_filter($out));
    $out = preg_replace('/\?>\s*<\?php\s*/', "\n", $out);

    return $out;
  }

  // parse single line
  final private static function line($key, $text = '', $indent = 0) {
    static $tags = NULL;

    $key  = static::unescape($key);
    $text = static::unescape($text);


    if (is_null($tags)) {
      $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
      $test = array_merge($test['empty'], $test['complete']);
      $tags = sprintf('(%s)', join('|', $test));
    }


    switch (substr($key, 0, 1)) {
      case '/';
        // <!-- ... -->
        return sprintf("<!--%s-->$text", trim(substr($key, 1)));
      break;
      case '<';
        // html
        return $key . $text;
      break;
      case '-';
        // php
        $key = substr($key, 1);
        $key = rtrim(join(' ', static::tokenize($key)), ';');
        $key = static::block($key);

        return "<?php $key ?>\n$text";
      break;
      case '=';
          // print
        $key = trim(substr($key, 1));
        $key = rtrim(join(' ', static::tokenize($key)), ';');
        $key = static::block($key, TRUE);

        return "<?php $key ?>$text";
      break;
      case ';';
        continue;
      break;
      default;
        $tag  = '';
        $args = array();

        // tag name
        preg_match(sprintf('/^%s(?=\b)/', $tags), $key, $match);

        if ( ! empty($match[0])) {
          $key = substr($key, strlen($match[0]));
          $tag = $match[1];
        }

        // attributes (raw)
        preg_match('/^[@#.][.\w@;:=-]+/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = substr($key, strlen($match[0]));
          $test = array();

          $args []= args(attrs($match[0]));
        }

        // attributes { hash => val }
        preg_match('/^\s*\{(.+?)\}/', $key, $match);

        if ( ! empty($match[0])) {
          $key    = str_replace($match[0], '', $key);
          $hash   = join('', static::tokenize($match[1]));

          $args []= $hash;
        }

        // output
        preg_match('/^\s*=.+?/', $key, $match);

        if ( ! empty($match[0])) {
          $key  = trim(substr(trim($key), 1));
          $text = "<?php echo $key; ?>$text";
        } elseif ( ! is_numeric($key)) {
          $text = trim($key) . $text;
        }

        $text && $text = "\n$text\n";

        $out  = ($tag OR $args) ? static::markup($tag ?: 'div', $args, $text) : $text;
        $out  = static::indent(trim($out));

        return $out;
      break;
    }
  }

  // parse blocks
  final private static function block($line, $echo = FALSE) {
    $suffix = ';';
    $prefix = $echo ? 'echo ' : '';

    if (preg_match(sprintf('/%s/', static::$fn), $line, $match)) {
      $suffix = '';
      $prefix = "\$_=get_defined_vars();$prefix";

      $line   = str_replace($match[0], 'function()', $line);
      $line  .= 'use($_){extract($_);unset($_);';
    } elseif (preg_match(sprintf('/^\s*%s/', static::$open), $line)) {
      $suffix = '{';
    }

    return "$prefix$line$suffix";
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
          case T_FOR; // for => $x | for="..." | for (...)
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
          case T_CLASS; // class="..."
          case T_FUNCTION; // blocks

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

  // apply fixes
  final private static function fixate($code) {
    $code = preg_replace(sprintf('/^\s{%d}/m', static::$defs['indent']), '', $code);
    $code = preg_replace_callback('/#\{(.+?)\}/', 'static::value', $code);
    $code = preg_replace(array_keys(static::$fix), static::$fix, $code);

    return $code;
  }

  // indentation
  final private static function indent($text, $max = 0) {
    $repl  = str_repeat(' ', $max ?: static::$defs['indent']);
    $test  = explode("\n", $text);
    $last  = array_pop($test);

    $text  = join("\n$repl", $test);
    $text .= "\n$last";

    return $text;
  }

  // errors
  final private static function error($i, $line, $desc = 'unknown', $file = '') {
    $error   = is_file($file) ? 'taml.error_file' : 'taml.error_line';
    $message = ln($error, array('line' => $i, 'text' => $line, 'name' => $file));
    $desc    = ln("taml.$desc");

    raise("$message ($desc)");
  }

  // protect chars
  final private static function escape($text, $rev = FALSE) {
    return $rev ? strtr(trim($text), array_flip(static::$qt)) : strtr($text, static::$qt);
  }

  // revert chars
  final private static function unescape($text) {
    return static::escape($text, TRUE);
  }

  /**#@-*/
}

/* EOF: ./library/taml/taml.php */
