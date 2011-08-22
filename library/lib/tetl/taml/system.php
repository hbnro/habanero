<?php

/**
 * Template markup library
 */

class taml extends prototype
{
  
  /**#@+
   * @ignore
   */
  
  // defaults
  private static $defs = array(
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
  final public static function setup($key, $value = '')
  {
    if (is_assoc($key))
    {
      taml::$defs += $key;
    }
    elseif (array_key_exists($key, taml::$defs))
    {
      taml::$defs[$key] = $value;
    }
  }
  
  
  /**
   * Render file
   *
   * @param  string Filepath
   * @param  array  Local vars
   * @return string
   */
  final public static function render($file, array $vars = array())
  {
    if ( ! is_file($file))
    {
      return FALSE;
    }
    
    
    $php_file = taml::$defs['path'].DS.md5($file).EXT;
    
    if (is_file($php_file))
    {
      if (filemtime($file) > filemtime($php_file))
      {
        unlink($php_file);
      }
    }
    
    
    if ( ! is_file($php_file))
    {
      $out = taml::parse(read($file), TRUE, $file);
      write($php_file, $out);
    }
    
    return render(array(
      'partial' => $php_file,
      'locals' => $vars,
    ));
  }
  
  
  /**
   * Parse markup
   *
   * @param  string  Expression
   * @param  boolean Return raw text?
   * @return mixed
   */
  final public static function parse($text, $raw = FALSE)
  {
    $code  = '';
    $stack = array();
    
    $test  = explode("\n", $text);
    $file  = func_num_args() > 2 ? func_get_arg(2) : '';
    
    
    foreach ($test as $i => $line)
    {
      $key    = '$out';
      $tab    = strlen($line) - strlen(ltrim($line));
      $next   = isset($test[$i + 1]) ? $test[$i + 1] : NULL;
      $indent = strlen($next) - strlen(ltrim($next));
      
      if ( ! trim($line))
      {
        continue;
      }
      elseif ($tab && ($tab % taml::$defs['indent']))
      {
        return taml::error($i, $line, 'bad_space', $file);
      }
      
      
      if ($indent > $tab)
      {
        $stack []= addslashes(trim($line));
      }
      
      foreach ($stack as $top)
      {
        $key .= "['$top']";
      }
      
      if ($indent < $tab)
      {
        $dec = $tab - $indent;
        
        while ($dec > 0)
        {
          array_pop($stack);
          $dec -= taml::$defs['indent'];
        }
      }
      
      $code .= "{$key} ";
      $line  = addslashes(trim($line));
      $code .= $line === end($stack) ? "= array()" : "[]= '$line'";
      $code .= ";\n";
    }
    
    
    @eval($code);
    
    if (empty($out))
    {
      return FALSE;
    }

    $out = taml::compile($out);
    $out = ents(unents($out));
    
    if (is_false($raw))
    {
      ob_start();
      @eval("?>$out");
      $out = ob_get_clean();
    }
    
    return $out;
  }
  
  
  
  /**#@+
   * @ignore
   */
  
  // errors
  final private static function error($i, $line, $desc = 'unknown', $file = '')
  {
    $error   = is_file($file) ? 'taml.error_file' : 'taml.error_line';
    $message = ln($error, array('line' => $i, 'text' => $line, 'name' => $file));
    $desc    = ln("taml.$desc");
    
    raise("$message ($desc)"); 
  }
  
  // compile lines
  final private static function compile($tree)
  {
    static $fix = array(
              '/\s*(?=[\r\n])/s' => '',
              '/\?>\s*<\?php/' => "\n",
              '/\?>\s*<\?=/' => "\necho",
              '/<([\w:-]+)([^<>]*)>\s*([^<>]+?)\s*<\/\\1>/' => '<\\1\\2>\\3</\\1>',
            );
   
    
    $out = array();
    
    foreach ($tree as $key => $value)
    {
      if (is_string($key))
      {
        if (preg_match('/^\s*-\s*(if|while|switch|for(?:each)?)/', $key, $match))
        {
          $tree["- end$match[1]"] = NULL;
        }
      }
    }
    
    
    foreach ($tree as $key => $value)
    {
      if (is_num($key))
      {
        $out []= taml::line($value);
        continue;
      }
      
      $out []= taml::line($key, is_array($value) ? taml::compile($value) : '');
    }
    
    $out = join("\n", array_filter($out));
    $out = preg_replace(array_keys($fix), $fix, $out);
    
    return $out;
  }

  // parse single line
  final private static function line($key, $text = '')
  {
    switch (substr($key, 0, 1))
    {
      case '!';
        return '<!DOCTYPE html>';
      break;
      case '@';
        $test   = explode(' ', strtr($key, '-', '_'), 2);
        $method = substr(array_shift($test), 1);
        
        if ( ! empty($method))
        {
          $args   = array_shift($test);
          
          $args = explode(' | ', join(' ', taml::tokenize($args)));
          $args = join(' , ', array_filter($args));
          
          return "<?= taml :: $method ( $args ); ?>$text";
        }
      break;
      case '/';
        // <!-- ... -->
        return sprintf("<!-- %s -->$text", trim(substr($key, 1)));
      break;
      case ';';
        // /* ... */
        return sprintf('<?php /* %s */ ?>', trim(substr($key, 1)));
      break;
      case '-';
        // php
        $key   = stripslashes(substr($key, 1));
        $key   = rtrim(join(' ', taml::tokenize(substr($key, 1))), ';');
        
        $close = preg_match('/^\s*(?:if|else(?:if)?|while|switch|for(?:each)?)/', $key) ? ':' : ';';
        
        return preg_replace('/^/m', '  ', "<?php $key$close ?>\n$text");
      break;
      case '=';
        // print
        $key = stripslashes(trim(substr($key, 1)));
        $key = rtrim(join(' ', taml::tokenize($key)), ';');
        
        return preg_replace('/^/m', '  ', "<?= $key; ?>$text");
      break;
      default;
        $tag  = '';
        $args = array();
        
        // tag name
        preg_match('/^:[\w:-]+/', $key, $match);
        
        if ( ! empty($match[0]))
        {
          $key = preg_replace('/^:[\w:-]+/', '', $key);
          $tag = substr($match[0], 1);
        }
        
        // attributes (raw)
        preg_match('/^[#.][.\w:-]+/', $key, $match);
        
        if ( ! empty($match[0]))
        {
          $key  = preg_replace('/^[#.][.\w:-]+/', '', $key);
          $args = args(attrs($match[0]));
        }
        
        // attributes (hash)
        preg_match('/\{([^{}]+)\}/', $key, $match);
        
        if ( ! empty($match[0]))
        {
          $key  = preg_replace('/\{([^{}]+)\}/', '', $key);
          $hash = join('', taml::tokenize($match[1]));
          
          preg_match_all('/([\w:-]+)=>(.+?)(?=,|$)/', $hash, $matches);
          
          foreach (array_keys($matches[0]) as $i)
          {
            $attrs = stripslashes($matches[2][$i]);
            $attrs = join(' ', taml::tokenize($attrs));
            
            $args[$matches[1][$i]] = "<?= $attrs; ?>";
          }
        }
        
        // output
        preg_match('/^\s*=.+?/', $key, $match);
        
        if ( ! empty($match[0]))
        {
          $key  = stripslashes(trim(substr(trim($key), 1)));
          $text = preg_replace('/^/m', '  ', "<?= $key; ?>$text");
        }
        else
        {
          $text .= stripslashes(trim($key));
        }
        
        $out = $tag ? tag($tag, $args, "\n$text\n") : $text;
        $out = preg_replace('/^/m', str_repeat(' ', taml::$defs['indent']), $out);
        
        return $out;
      break;
    }
  }
  
  // retrieve expression tokens
  final private static function tokenize($code)
  {
    static $expr = array(
              'array',
              'empty',
              'list',
            );
    
    
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
          case T_ELSE; // else
          case T_ELSEIF; // elseif
          case T_SWITCH; // switch
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

  /**#@-*/
}

/* EOF: ./lib/tetl/taml/system.php */
