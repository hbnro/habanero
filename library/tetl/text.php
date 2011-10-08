<?php

/**
 * Text related functions
 */

/**#@+
 * URL rewrite rules
 */
define('URL_NO_FOLLOW', 1);
define('URL_EXTERNAL', 2);
/**#@-*/


/**
 * Automatic links
 *
 * @param     string String
 * @param     mixed  URL_NO_FOLLOW|URL_EXTERNAL
 * @staticvar string Match url
 * @return    string
 */
function urlify($text, $options = FALSE)
{
  static $expr = NULL;


  if (is_null($expr))
  {
    $expr = '/(?:^|\b)(?:[a-z]{3,7}:\/\/|\w+@|www\.)'
          . '[-\.\w]+(?::\d{1,5})?[\/\w?:;+=#!%.-]+(?:\b|$)/i';
  }

  $hash = uniqid('--amp-entity');
  $text = str_replace('&amp;', $hash, $text);
  $text = preg_replace_callback($expr, function($matches)
    use($options)
  {
    $args['href'] = $matches[0];

    $nofollow = ((int) $options & URL_NO_FOLLOW) == 0 ? FALSE : TRUE;
    $external = ((int) $options & URL_EXTERNAL) == 0 ? FALSE : TRUE;

    $args['href'] = pre_url($args['href']);

    if ($external && ! is_local($args['href']))
    {
      $args['rel']  = $nofollow ? 'nofollow ' : '';
      $args['rel'] .= 'external';
    }

    $output = sprintf('<a%s>%s</a>', attrs($args), $matches[0]);

    return $output;
  }, $text);

  $text = str_replace($hash, '&amp;', $text);

  return $text;
}


/**
 * Randomly encode a string to entities
 *
 * @param  string Input string
 * @return string
 */
function encode($text)
{
  $out    = '';
  $length = strlen($text);


  for ($i = 0; $i < $length; $i += 1)
  {
    $rand = mt_rand(0, 100);
    $char = substr($text, $i, 1);


    if ($ran < 45)
    {
      $out .= '&#x' . dechex(ord($char)) . ';';
    }
    elseif ($ran > 90 && ! preg_match('/[@:.]/', $char))
    {
      $out .= $char;
    }
    else
    {
      $out .= '&#' . ord($char) . ';';
    }
  }

  return $out;
}


/**
 * Intercalate given words
 *
 * @param  string  Input string
 * @param  string  Replace string
 * @param  mixed   Character separator
 * @param  boolean It is odd?
 * @return string
 */
function even($text, $repl = '%s', $ord = 124, $odd = FALSE)
{
  $chr = char($ord);
  $str = explode($chr, $text);

  foreach ($str as $key => $val)
  {
    if (($key % 2) <> $odd)
    {
      $str[$key] =  sprintf($repl, $val);
    }
    else
    {
      $str[$key] = $val;
    }
  }

  return join($chr, $str);
}


/**
 * Alternation of given words
 *
 * @param  array  Array|...
 * @return string
 */
function alt(array $set = array())
{
  static $index = 0;


  if (func_get_args() == 0)
  {
    $index = 0;
    return FALSE;
  }


  $num  = func_num_args();
  $args = is_array($set) ? $set : func_get_args();

  $num  = $index % sizeof($args);
  $out  = isset($args[$num]) ? $args[$num] : '';

  $index += 1;

  return $out;
}


/**
 * Delimitate words
 *
 * @param  integer Input string
 * @param  integer Characters length
 * @param  mixed   Character separator
 * @return string
 */
function delim($text, $max = 3, $ord = 160)
{
  $meta = str_repeat('.', $max);
  $expr = "/(.)(?=($meta)+(?!.))/";

  return preg_replace($expr, '\\1' . char($ord), $text);
}


/**
 * Chunks from the left
 *
 * @param  string  Input string
 * @param  integer Characters length
 * @return string
 */
function left($text, $max = 0)
{
  return $max > 0 ? substr($text, 0, $max) : substr($text, $max * -1);
}


/**
 * Chunks from the right
 *
 * @param  string  Input string
 * @param  integer Characters length
 * @return string
 */
function right($text, $max = 0)
{
  return $max > 0 ? substr($text, -$max) : substr($text, 0, $max);
}


/**
 * Count words
 *
 * @param  string  Input string
 * @param  integer Words from left
 * @param  integer Words from right
 * @param  string  Character separator
 * @return string
 */
function words($text, $left = 100, $right = 0, $char = '&hellip;')
{
  if ( ! empty($text))
  {
    preg_match_all('/\s*(\S+\s+)\s*/', $text, $match);

    if (($left + $right) >= str_word_count($text))
    {
      return $text;
    }

    $length = sizeof($match[1]);

    $left   = trim(join(' ', array_slice($match[1], 0, $left)));
    $right  = trim(join(' ', array_slice($match[1], $length - $right)));

    return $left . $char . $right;
  }
}


/**
 * Retrieve the frequency of given words
 *
 * @param     string Input string|Array
 * @staticvar string Words match
 * @return    array
 */
function freq($text)
{
  static $expr = NULL;


  if (is_null($expr))
  {
    $expr = match('%G|\s+');
  }

  $set  = array();

  $text = is_utf8($text) ? utf8_decode($text) : $text;
  $text = preg_replace($expr, ' ', $text);

  foreach (array_filter(explode(' ', $text)) as $word)
  {
    if (array_key_exists($word, $set))
    {
      $set[$word] += 1;
    }
    else
    {
      $set[$word] = 0;
    }
  }

  return $set;
}


/**
 * Short given string
 *
 * @param  string Input string
 * @param  string Chunk from left
 * @param  string Chunk from right
 * @param  string Character separator
 * @return string
 */
function short($text, $left = 33, $right = 0, $glue = '&hellip;')
{
  $prefix =
  $suffix = '';

  $hash   = uniqid('--short-separator');
  $max    = char($glue) === '&' ? 1 : strlen($glue);


  if (preg_match('/&#?[a-zA-Z0-9];/', $text))
  {
    $text = unents($text);
  }

  if ((strlen($text) + $max) > ($left + $right))
  {
    $prefix = trim(substr($text, 0, $left - $max));
    $prefix = preg_replace('/^#?\w*;/', '', $prefix);

    if ($right > 0)
    {
      $suffix = trim(substr($text, - $right));
    }

    $suffix = preg_replace('/(&|&amp;)#?\w*$/u', '', $suffix);
    $text   = $prefix . $hash . $suffix;
  }


  $text = str_replace($hash, $glue, $text);

  return $text;
}


/**
 * Find and highlight without break html
 *
 * @param     string Input string
 * @param     mixed  Array|Words set
 * @param     string Replacement string
 * @param     mixed  Character separator
 * @staticvar string Latin charset match
 * @return    string
 */
function search($text, $find, $repl = '<strong>%s</strong>', $ord = 32)
{
  static $latin = NULL;


  if (is_null($latin))
  {
    $latin = substr(match('%L'), 1, -1);
  }


  $found = array();
  $word  = ! is_array($find) ? explode(char($ord), $find) : $find;

  foreach (array_unique(array_filter($word)) as $test)
  {
    $test    = preg_quote(strips($test, TRUE), '/');
    $found []= plain($test, TRUE);
  }

  if ( ! empty($found))
  {
    $expr  = join('|', $found);
    $regex = "/(([<][^>]*)|(?<!&|#|\w)[$latin]*{$expr}(?:$latin*|[^\W<>]*)?(?=\s|\b))/i";

    $text  = preg_replace_callback($regex, function($match)
      use($repl)
    {
      return ! isset($match[2]) ? sprintf($repl, $match[0]) : $match[0];
    }, $text);
  }

  return $text;
}


/**
 * Search and select text chunks
 *
 * @param  string  Input string
 * @param  string  Keywords
 * @param  string  Delimiters
 * @param  integer Character bounds
 * @return array
 */
function find($text, $query = '', $chunk = '..', $length = 30)
{
  $bad   =
  $good  = array();
  $query = strtolower(plain($query));


  $query = preg_replace_callback('/"([^"]+?)"/', function($match)
    use(&$good)
  {
    $good []= preg_quote($match[1]);
  }, $query);


  foreach (preg_split('/\s+/', $query) as $one)
  {
    switch(substr($one, 0, 1))
    {
      case '-':
        if(strlen($one) > 1)
        {
          $bad []= preg_quote(substr($one, 1), '/');
        }
      break;
      case '+':
        if(strlen($one) > 1)
        {
          $good []= preg_quote(substr($one, 1), '/');
        }
      break;
      default:
        $good []= preg_quote($one, '/');
      break;
    }
  }


  $good = array_filter($good);

  if (sizeof($good) > 0)
  {
    $regex  = sprintf('(?<!&|#|\w)\w*(?:%s)(?=', plain(join('|', $good), TRUE));
    $regex .= $bad ? '(?!' . plain(join('|', $bad), TRUE) . ')' : '';
    $regex .= '.*?(?=\b))';

    if (preg_match_all("/$regex/uis", $text, $match, PREG_OFFSET_CAPTURE))
    {
      $out = array();

      foreach ($match[0] as $key => $val)
      {
        $tmp = substr($text, $val[1] - ($length / 2), $length);
        $tmp = preg_replace('/^#?\w*;|(&|&amp;)#?\w*$/', '', $chunk . trim($tmp) . $chunk);

        $out []= array(
          'excerpt' => $tmp,
          'offset' => $val[1],
          'word' => $val[0],
        );
      }

      return $out;
    }
  }
  return FALSE;
}

/* EOF: ./lib/tetl/text.php */
