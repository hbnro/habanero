<?php

/**
 * Utility functions library
 */

/**#@+
 * Slug transformation options
 */
define('SLUG_STRICT', 1);
define('SLUG_LOWER', 2);
define('SLUG_TRIM', 4);
/**#@-*/


/**
 * Retrieve the character at first position in the provided string
 *
 * @param  mixed  String
 * @return string
 */
function char($text)
{
  return ! is_num($text) ? substr((string) $text, 0, 1) : chr((int) $text);
}


/**
 * Make a string lowercase and non alphabetic charater to underscore
 *
 * @param  string  String
 * @param  boolean Use ucwords()?
 * @return string
 */
function underscore($text, $ucwords = FALSE)
{
  $text = plain(unents($text));

  if (is_true($ucwords)) 
  {
    $text = ucwords($text);
  }
  
  $text = strtr($text, ' ', '_');

  return preg_replace('/(^|\W)([A-Z])/e', '"\\1_".strtolower("\\2");', $text);
}


/**
 * Convert the specified string to camel case format
 *
 * @param  string  String
 * @param  boolean Use ucfirst()?
 * @param  string  Character separator
 * @return string
 */
function camelcase($text, $ucfirst = FALSE, $glue = '')
{
  static $repl = array(
            '/[^a-z0-9]|\s+/i' => ' ',
            '/\s([a-z])/ie' => '$glue.ucfirst("\\1")',
          );

  $text = preg_replace(array_keys($repl), $repl, underscore($text));

  if (is_true($ucfirst)) 
  {
    $text = ucfirst($text);
  }

  return $text;
}


/**
 * Unique hash
 *
 * @param     integer String length
 * @staticvar string  Charset
 * @return    string
 */
function salt($length = 8)
{
  static $chars = '@ABCD,EFGH.IJKL-MNOP=QRST~UVWX$YZab/cdef*ghij;klmn:opqr_stuv(wxyz)0123!4567|89{}';
  

  $length = (int) $length;

  if ($length > 32) $length = 32;

  $out = '';

  mt_srand(ticks() * time());

  do
  {
    $index = substr($chars, mt_rand(0, 79), 1);

    if ( ! strstr($out, $index)) 
    {
      $out .= $index;
    }

    $current = strlen($out);

  } while($current !== $length);

  return $out;
}


/**
 * Slugify string segments
 *
 * @param  string  Path|Route
 * @param  boolean Character separator
 * @param  mixed   SLUG_STRICT|SLUG_LOWER|SLUG_TRIM
 * @return string
 */
function slug($text, $glue = '-', $options = NULL)
{
  $strict = ((int) $options & SLUG_STRICT) == 0 ? FALSE : TRUE;
  $lower = ((int) $options & SLUG_LOWER) == 0 ? FALSE : TRUE;
  $trim = ((int) $options & SLUG_TRIM) == 0 ? FALSE : TRUE;
 
  
  $expr = $strict ? '\W+' : sprintf('[^%s\/]', substr(match('%X'), 1, -1));
  $text = preg_replace("/$expr/", $glue, plain(unents($text)));
  $text = $lower ? strtolower($text) : $text;
  
  if ($trim)
  {
    $char = preg_quote($glue, '/');
    $text = preg_replace("/$char+/", $glue, $text);
    $text = trim($text, $glue);
  }
  
  return $text;
}


/**
 * Remove punctuation characters
 *
 * @param     string  String
 * @param     boolean Magic regex
 * @staticvar array   Entities set
 * @return    string
 */
function plain($text, $special = FALSE)
{
  static $set = NULL,
         $rev = NULL;
         
         
  if (is_null($set))
  {
    $old  = $rev = array();
    $html = get_html_translation_table(HTML_ENTITIES);
    
    foreach ($html as $char => $ord)
    {
      if (ord($char) >= 192)
      {
        $char = utf8_encode($char);
        $key = substr($ord, 1, 1);

        $set[$char] = $key;

        if ( ! isset($old[$key]))
        {
          $old[$key] = (array) $key;
        }

        $old[$key] []= $char;
        $old[$key] []= $ord;
      }
    }
    
    foreach ($old as $key => $val)
    {
      $rev[$key] = '(?:' . join('|', $val) . ')';
    }
  }
  
  
  $text = strtr($text, $set);
  $text = is_true($special) ? strtr($text, $rev) : $text;
  
  return $text;
}


/**
 * Strips out some type of tags
 *
 * @param  string  String
 * @param  boolean Allow comments?
 * @return string
 */
function strips($text, $comments = FALSE)
{
  $out = preg_replace('/[<\{\[]\/*[^<\{\[!\]\}>]*[\]\}>]/Us', '', $text);
  $out = is_false($comments) ? strip_tags($out) : $out;
  
  return $out;
}


/**
 * Entity repair and escaping
 *
 * @param     mixed   String|Array
 * @param     boolean Escape tags?
 * @staticvar array   Hex replacements
 * @return    string
 */
function ents($text, $escape = FALSE)
{
  static $expr = array(
            '/(&#?[0-9a-z]{2,})([\x00-\x20])*;?/i' => '\\1;\\2',
            '/&#x([0-9a-f]+);?/ei' => 'chr(hexdec("\\1"))',
            '/(&#x?)([0-9A-F]+);?/i' => '\\1\\2;',
            '/&#(\d+);?/e' => 'chr("\\1")',
          );
  
  
  $hash = uniqid('--entity-backup');
  $text = preg_replace('/&([a-z0-9;_]+)=([a-z0-9_]+)/i', "{$hash}\\1=\\2", $text);

  $text = preg_replace(array_keys($expr), $expr, $text);
  $text = preg_replace('/&(#?[a-z0-9]+);/i', "{$hash}\\1;", $text);
  $text = str_replace(array('&', '\\', $hash), array('&amp;', '&#92;', '&'), $text);

  if (is_true($escape))
  {
    $text = strtr($text, array(
        '<' => '&lt;',
        '>' => '&gt;',
        '"' => '&quot;',
        "'" => '&#39;',
    ));
  }

  $text = preg_replace("/[\200-\237]|\240|[\241-\377]/", '\\0', $text);
  $text = preg_replace("/{$hash}(.+?);/", '&\\1;', $text);
  
  return $text;
}


/**
 * Revert entities
 *
 * @param     string String
 * @staticvar array  Entities set
 * @return    string
 */
function unents($text)
{
  static $set = NULL;

  if (is_null($set))
  {
    $set = get_html_translation_table(HTML_ENTITIES);
    $set = array_flip($set);
  }

  $text = preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&\\1;', $text);
  $text = preg_replace('/&#x([0-9a-f]+);/ei', 'chr(hexdec("\\1"))', $text);
  $text = preg_replace('/&#([0-9]+);/e', 'chr("\\1")', $text);
  $text = strtr(str_replace('&apos;', "'", $text), $set);

  return html_entity_decode($text);
}


/**
 * HTML generic tag
 *
 * @param   string  Tag name
 * @param   array   Attributes
 * @param   string  Inner text value
 * @param   boolean Self close tag?
 * @return  string
 */
function tag($name, array $args = array(), $text = '', $close = FALSE)
{
  static $set = NULL;
  
  
  if (is_null($set))
  {
    $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
    $set  = $test['empty'];
  }

  $attrs = attrs($args);
  
  if (is_true($close) OR in_array($name, $set))
  {
    return "<$name$attrs/>";
  }
  
  return "<$name$attrs>$text</$name>";
}


/**
 * Make a string of HTML attributes
 *
 * @param     mixed   Array|Object|Expression
 * @param     boolean Strictly HTML attributes?
 * @staticvar array   Global attributes set
 * @staticvar string  Selector regex
 * @return    string
 */
function attrs($args, $html = FALSE)
{
  static $global = NULL,
         $regex = '/(?:#([a-z_][\da-z_-]*))?(?:[\.,]?([\s\d\.,a-z_-]+))?(?:@([^"]+))?/i';
  
  if (is_null($global))
  {
    $test   = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
    $global = array_merge($test['global'], $test['events']);
    
    unset($global['data-*']);
    unset($global['aria-*']);
  }
  
  
  if (is_string($args))
  {
    preg_match_all($regex, $args, $match);


    $args = array();
    
    if ( ! empty($match[1][0]))
    {
      $args['id'] = $match[1][0];
    }
    
    if ( ! empty($match[2][0]))
    {
      $args['class'] = strtr($match[2][0], ',.', ' ');
    }
    
    if ( ! empty($match[3][0]))
    {
      foreach (explode('@', $match[3][0]) as $one)
      {
        $test = explode('=', $one);
        
        $key  = ! empty($test[0]) ? $test[0] : $one;
        $val  = ! empty($test[1]) ? $test[1] : $key;
        
        $args[$key] = $val;
      }
    }
  }

  
  $out  = array('');
  
  foreach ((array) $args as $key => $value)
  {
    $key = preg_replace('/\W/', '-', trim($key));
    
    if (is_true($html) && ! in_array($key, $global))
    {
      continue;
    }
    
    
    if (is_bool($value))
    {
      if (is_true($value))
      {
        $out []= $key;
      }
    }
    elseif (is_iterable($value))
    {
      if ($key === 'style')
      {//FIX
        $props = array();
        
        foreach ($value as $key => $val)
        {//TODO: deep chained props?
          $props []= $key . ':' . trim($val);
        }
        
        $out []= sprintf('style="%s"', join(';', $props));
      }
      else
      {
        foreach ((array) $value as $index => $test)
        {
          $out []= sprintf('%s-%s="%s"', $key, $index, trim($test));
        }
      }
    }
    elseif ( ! is_num($key) && is_scalar($value))
    {
      $out []= sprintf('%s="%s"', $key, ents($value, TRUE));
    }
  }
  
  $out = join(' ', $out);
  
  return $out;
}


/**
 * Retrieve params from attributes string
 *
 * @param     string String
 * @staticvar string Match regex
 * @return    array
 */
function args($text, $prefix = '')
{
  static $regex = '/(?:^|\s+)(?:([\w:-]+)\s*=\s*([\'"`]?)(.+?)\\2|[\w:-]+)(?=\s+|$)/';
  
  
  $out  = array();
  
  preg_match_all($regex, $text, $match);
  
  foreach ($match[1] as $i => $key)
  {
    if (empty($key))
    {
      $out []= trim($match[0][$i]);
      continue;
    }
    
    $val = ents($match[3][$i], TRUE);
    $key = strtolower($key);
    
    $out[$key] = $val;
  }
  
  return $out;
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
      $old   = lambda(__FUNCTION__, $val, FALSE, $deep - 1, $depth + 1);
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

/* EOF: ./core/utilities.php */
