<?php

/**
 * Token related functions
 */

/**
 * Tokens length
 *
 * @param  string Text
 * @param  mixed  Token
 * @return integer
 */
function numtok($text, $ord = 32)
{
  return substr_count($text, char($ord)) + 1;
}


/**
 * Retrieve single token or range
 *
 * @param  string  Text
 * @param  mixed   Index
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function gettok($text, $index = 0, $ord = 32, $join = TRUE)
{
  $test = explode(char($ord), $text);

  if ( ! $index)
  {
    return is_true($join) ? join(char($ord), $test) : $test;
  }
  elseif (preg_match('/(\d+)-(\d*)/', $index, $match))
  {
    if ( ! empty($match[2]))
    {
      $test = array_slice($test, $match[1] - 1, $match[2] - ($match[1] - 1));
    }
    else
    {
      $test = array_slice($test, $match[1] - 1);
    }
    return $join ? join(char($ord), $test) : $test;
  }

  $index = $index > 0 ? $index - 1:  $index;
  $index = $index < 0 ? sizeof($test) + $index : $index;
  
  return isset($test[$index]) ? $test[$index] : FALSE;
}


/**
 * Add single token
 *
 * @param  string  Text
 * @param  mixed   Value
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function addtok($text, $value = '', $ord = 32, $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  
  array_splice($out, sizeof($out), 0, $value);
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Search token
 *
 * @param  string  Text
 * @param  mixed   Value
 * @param  mixed   Token
 * @return integer
 */
function findtok($text, $value, $ord = 32)
{
  return array_search($value, gettok($text, 0, $ord, FALSE)) + 1;
}


/**
 * Token exists?
 *
 * @param  string  Text
 * @param  mixed   Value
 * @param  mixed   Token
 * @return boolean
 */
function istok($text, $value, $ord = 32)
{
  return in_array($value, gettok($text, 0, $ord, FALSE));
}


/**
 * Find and remove tokens
 *
 * @param  string  Text
 * @param  mixed   Value|Array
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function remtok($text, $find, $ord = 32, $join = TRUE)
{
  $out  = array();
  $find = (array) $find;
  
  foreach (gettok($text, 0, $ord, FALSE) as $one)
  {
    if ( ! in_array($one, $find))
    {
      $out []= $one;
    }
  }
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Delete single token or range
 *
 * @param  string  Text
 * @param  mixed   Index
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function deltok($text, $index = 0, $ord = 32, $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  
  if (preg_match('/(\d*)-(\d*)/', $index, $match))
  {
    if (empty($match[1]) && ! empty($match[2]))
    {
      $out = array_splice($out, - $match[2]);
    }
    elseif ( ! empty($match[2]))
    {
      $out = array_splice($out, $match[1] - 1, $match[2] - ($match[1] - 1));
    }
    
    $out = array_splice($out, $match[1] - 1);
  }
  elseif (($index > 0) && ($index <= sizeof($out)))
  {
    unset($out[$index - 1]);
  }
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Insert tokens
 *
 * @param  string  Text
 * @param  mixed   Array|Value
 * @param  mixed   Index
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function instok($text, $value = '', $index = 0, $ord = 32, $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  
  if ($index <> 0)
  {
    array_splice($out, $index, 0, $value);
  }
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Replace token
 *
 * @param  string  Text
 * @param  array   Replacements
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function repltok($text, $repl, $ord = 32, $join = TRUE)
{
  if ( ! is_array($repl))
  {
    return FALSE;
  }
  
  $out = gettok($text, 0, $ord, FALSE);
  
  foreach ($out as $key => $val)
  {
    if (isset($repl[$key]))
    {
      $out[$key] = $repl[$key];
    }
  }
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Set token value
 *
 * @param  string  Text
 * @param  mixed   Array|Value
 * @param  mixed   Range|Index
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function settok($text, $value, $index = 0, $ord = 32, $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  
  if (preg_match('/(\d*)-(\d*)/', $index, $match))
  {
    if (empty($match[1]) && ! empty($match[2]))
    {
      $out = array_splice($out, - $match[2], $match[2], $value);
    }
    elseif ( ! empty($match[2]))
    {
      $out = array_splice($out, $match[1] - 1, $match[2] - ($match[1] - 1), $value);
    }
    
    $out = array_splice($out, $match[1] - 1, sizeof($out), $value);
  }
  elseif ($index > 0)
  {
    $out = array_splice($out, $index - 1, 1, $value);
  }
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Retrieve token index by RegExp
 *
 * @param  string  Text
 * @param  string  Regex
 * @param  mixed   Token
 * @param  integer Index
 * @return integer
 */
function matchtok($text, $regex, $index = 0, $ord = 32)
{
  $regex = sprintf('/%s/', str_replace('/', '\\/', $regex));
  
  foreach (gettok($text, 0, $ord, FALSE) as $key => $val)
  {
    if (@preg_match($regex, $val))
    {
      return $key += 1;
    }
  }
}


/**
 * Retrieve token index by fnmatch
 *
 * @param  string Text
 * @param  mixed  Filter
 * @param  mixed  Index
 * @param  mixed  Token
 * @return mixed
 */
function wildtok($text, $filter, $index = 0, $ord = 32)
{
  foreach (gettok($text, 0, $ord, FALSE) as $key => $val)
  {
    if (match($filter, $val))
    {
      return $key += 1;
    }
  }
}


/**
 * Adjust token padding
 *
 * @param  string  Text
 * @param  mixed   Length
 * @param  mixed   Value
 * @param  mixed   Token
 * @param  boolean Join again?
 * @return mixed
 */
function padtok($text, $length, $value = '', $ord = 32, $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  $out = array_pad($out, $length, $value);
  
  return is_true($join) ? join(char($ord), $out) : $out;
}


/**
 * Sort tokens
 *
 * @param  string  Text
 * @param  mixed   Token
 * @param  string  Sort mode
 * @param  boolean Join again?
 * @return mixed
 */
function sorttok($text, $ord = 32, $mode = '', $join = TRUE)
{
  $out = gettok($text, 0, $ord, FALSE);
  $dec = strlen($mode);

  while ($dec > 0)
  {
    switch (substr($mode, $dec -= 1, 1))
    {
      case 'R';
        $out = array_reverse($out);
      break;
      case 'N';
        natsort($out);
      break;
      case 'n';
        natcasesort($out);
      break;
      case 's';
        shuffle($out);
      break;
      case 'r';
        rsort($out);
      break;
      default;
      break;
    }
  }

  ! $mode && sort($out);
  
  return is_true($join) ? join(char($ord), $out) : $out;
}

/* EOF: ./lib/tetl/token.php */
