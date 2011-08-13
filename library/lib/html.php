<?php

/**
 * HTML tag library
 */

/**
 * CDATA tag
 *
 * @param  string  Inner text value
 * @param  boolean Treat as comment?
 * @return string
 */
function cdata($text, $comment = FALSE)
{
  if (is_true($comment))
  {
    return "/*<![CDATA[*/\n$text\n/*]]]>*/";
  }
  return "<![CDATA[$text]]]>";
}


/**
 * Data-URI
 *
 * @param  string  Value
 * @param  string  MIME type
 * @param  boolean Use chunk_split()?
 * @return string
 */
function data($text, $mime = 'text/plain', $chunk = FALSE)
{
  $text = base64_encode($text);
  
  if (is_true($chunk))
  {
    $text = chunk_split($text);
  }
  
  return "data:$type;base64,$text";
}


/**
 * Script tag
 *
 * @param  string  URL|Script
 * @param  string  Additional code
 * @param  boolean Force CDATA block
 * @return string
 */
function script($url, $text = '', $force = FALSE)
{
  $url  = ! is_url($url) ? '' : $url;
  $text = ! is_url($url) ? $url : $text;
  
  
  $attrs['type'] = 'text/javascript';
  
  if ( ! empty($url))
  {
    $attrs['src'] = $url;
  }

  return tag('script', $attrs, is_true($force) ? cdata($text, TRUE) : $text);
}


/**
 * Style tag
 *
 * @param  string  URL|Style
 * @param  boolean Force CDATA block
 * @return string
 */
function style($text, $force = FALSE)
{
  $attrs['type'] = 'text/css';
  
  if (is_url($text))
  {
    $attrs['src'] = $text;
    $text = '';
  }

  return tag('style', $attrs, is_true($force) ? cdata($text, TRUE) : $text);
}


/**
 * Meta tag
 *
 * @param  string  Key name
 * @param  string  Value|Description
 * @param  boolean Employ http-equiv?
 * @return string
 */
function meta($name, $content, $http = FALSE)
{
  $attrs = compact('content');
  
  $attrs[is_true($http) ? 'http-equiv' : 'name'] = $name;

  return tag('meta', $attrs);
}


/**
 * Non breaking space
 *
 * @param  integer Repetitions
 * @return string
 */
function nbsp($num = 1)
{
  return str_repeat('&nbsp;', $num);
}


/**
 * Threaded block tags
 *
 * @param  string Inner text value
 * @param  mixed  Attributes
 * @param  string Inner wrapper
 * @param  string Tag name
 * @return string
 */
function block($text, $args = array(), $wrap = '<p>%s</p>', $tag = 'blockquote')
{
  if (is_string($args))
  {
    $args = args(attrs($args));
  }
    
    if (is_scalar($text))
  {
    return tag($tag, $args, sprintf($wrap, $text));
  }
  elseif (is_array($text))
  {
    $test   = array_values($text);
    $length = sizeof($test);
    $out    = array();
    $cite   = FALSE;


    for ($i = 0; $i < $length; $i += 1)
    {
      $next = isset($test[$i + 1]) ? $test[$i + 1]: NULL;

      if (is_array($test[$i]))
      {
        $out []= block($test[$i], $args, $wrap, $tag);
      }
      elseif (is_array($next))
      {
        $inner = block($next, $args, $wrap, $tag);
        $out []= block(sprintf($wrap, $test[$i]) . $inner, $args, '%s', $tag);

        $cite = TRUE;
        $i   += 1;
      }
  
      if (is_string($test[$i]))
      {
        $out []= tag($tag, $args, sprintf($wrap, $test[$i]));
        
        if (is_true($cite))
        {
          $cite = FALSE;
        }
      }
    }
    
    return join("\n", $out);
  }
}


/**
 * Form fieldset
 *
 * @param  string Inner text value
 * @param  mixed  Fieldset legend|Attributes
 * @param  mixed  Attributes
 * @return string
 */
function fieldset($text, $title = '', $args = array())
{
  if (is_string($args))
  {
    $args = args(attrs($args));
  }
  
  if (is_assoc($title))
  {
    $args  += $title;
    $title  = '';
  }
  
  return tag('fieldset', $args, ($title ? tag('legend', '', $title) : '') . $text);
}


/**
 * Text headings
 *
 * @param  string  Inner text value
 * @param  integer Heading level
 * @param  array   Attributes
 * @return string
 */
function heading($text, $num = 1, $args = array())
{
  if (is_num($num, 1, 6))
  {
    return tag("h$num", $args, $text);
  }
}


/**
 * Data tables
 *
 * @param  mixed  Headers|Hash
 * @param  array  Vector data
 * @param  mixed  Footer|Hash
 * @param  mixed  Attributes
 * @param  mixed  Function callback
 * @return string
 *
 */
function table($head, array $body, $foot = array(), $args = array(), $filter = FALSE)
{
  $thead =
  $tbody =
  $tfoot = '';
  
  if ( ! empty($head))
  {
    $head = ! is_string($head) ? (array) $head : explode('|', $head);

    foreach ($head as $col)
    {
      $thead .= tag('th', '', $col);
    }
    $thead = tag('thead', '', tag('tr', '', $thead));
  }

  if ( ! empty($foot))
  {
    $foot  = ! is_string($foot) ? (array) $foot : explode('|', $foot);
    $attrs = array(
      'colspan' => sizeof($foot) > 1 ? 99 : FALSE,
    );

    foreach ($foot as $col)
    {
      $tfoot .= tag('th', $attrs, $col);
    }
    $tfoot = tag('tfoot', '', tag('tr', '', $tfoot));
  }

  
  foreach ((array) $body as $cols => $rows)
  {
    if ( ! is_array($rows))
    {
      $tbody .= tag('tr', '', tag('td', array('colspan' => 99), $rows));
      continue;
    }


    $row = '';

    foreach ($rows as $cell)
    {
      if (is_callable($filter))
      {
        $cell = call_user_func($filter, $cell);
      }
      
      if (is_array($cell))
      {
        $cell = table('', $cell);
      }
      $row .= tag('td', '', $cell);
    }
    $tbody .= tag('tr', '', $row);
  }
  
  return tag('table', $args, $thead . $tbody . $tfoot);
}


/**
 * Tag cloud based on word frequency
 *
 * @link   http://snipplr.com/view/2225/php-tag-cloud-based-on-word-frequency/
 * @param  array   Words set
 * @param  mixed   Attributes
 * @param  string  Default link
 * @param  integer Minimum font-size
 * @param  integer Maximum font-size
 * @param  string  Default size unit
 * @return string
 */
function cloud(array $from = array(), $args = array(), $href = '?q=%s', $min = 12, $max = 30, $unit = 'px')
{
  $min_count = min(array_values($set));
  $max_count = max(array_values($set));
  
    
  $set    = array();
  $spread = $max_count - $min_count;
  
  ! $spread && $spread = 1;
  
  foreach ($from as $tag => $count)
  {
    $size  = floor($min + ($count - $min_count) * ($max - $min) / $spread);
    $set []= a(sprintf($href, $tag), $tag, array(
      'style' => "font-size:$size$unit",
    ));
  }
  
  return ulist($set, $args);
}


/**
 * Navigation list
 *
 * @param  array  Links
 * @param  mixed  Attributes
 * @param  string Default value
 * @param  string CSS marker class
 * @return string
 */
function navlist($set, $args = array(), $default = URI, $class = 'here')
{
  if (is_string($args))
  {
    $args = args(attrs($args));
  }
  
  
  $out = array();

  foreach ($set as $key => $val)
  {
    $attrs = array();
    
    if ($default === $key)
    {
      $attrs['class'] = $class;
    }
    
    $out []= a($key, $val, $attrs);
  }
  
  return ulist($out, $args);
}


/**
 * Definition list
 *
 * @param  array  Values
 * @param  mixed  Attributes
 * @param  mixed  Function callback
 * @return string
 */
function dlist($set, $args = array(), $filter = FALSE)
{
  return ulist($set, $args, $filter, 0, 0);
}


/**
 * Ordered list
 *
 * @param  array  Values
 * @param  mixed  Attributes
 * @param  mixed  Function callback
 * @return string
 */
function olist($set, $args = array(), $filter = FALSE)
{
  return ulist($set, $args, $filter, 0);
}


/**
 * Unordered list
 *
 * @param  array  Values
 * @param  mixed  Attributes
 * @param  mixed  Function callback
 * @return string
 */
function ulist($set, $args = array(), $filter = FALSE)
{
  $ol = func_num_args() == 4;
  $dl = func_num_args() == 5;


  $tag   = 'ul';
  $el    = 'li';
  $out   = '';
  
  if (is_true($dl))
  {
    $tag = 'dl';
    $el  = 'dd';
  }
  elseif (is_true($ol))
  {
    $tag = 'ol';
  }
  
  

  foreach ((array) $set as $item => $value)
  {
    $test = is_callable($filter) ? call_user_func($filter, $item, $value) : array($item, $value);

    if ( ! isset($test[1]))
    {
      continue;
    }
    elseif (is_true($dl))
    {
      $out .= tag('dt', '', $test[0]);
    }

    if (is_array($test[1]))
    {
      $item = ! is_num($test[0]) ? $test[0] : '';
      $tmp  = array($test[1], $args, $filter);
      
      if (is_callable($filter))
      {
        $item = call_user_func($filter, -1, $item);
        $item = array_pop($item);
      }

      if (is_true($ol))
      {
        $tmp []= '';
      }
      
      $tmp[1] = NULL;

      $inner = call_user_func_array(__FUNCTION__, $tmp);
      $out  .= tag($el, '', $item . $inner);
      continue;
    }
    $out .= tag($el, '', $test[1]);
  }
  return tag($tag, $args, $out);
}


/**
 * Hyperlink tag
 *
 * @param  string URL string
 * @param  string Inner text value
 * @param  mixed  Attributes|Title value
 * @return string
 */
function a($href, $text = '', $title = array())
{
  if (is_array($href))
  {
    $href = http_build_query($href, NULL, '&amp;');
    $href = ! empty($href) ? "?$href" : '';
  }

  
  $attrs = array();
  
  if ( ! empty($href))
  {
    $attrs['href'] = $href;
  }

  if (is_assoc($title))
  {
    $attrs += $title;
  }
  elseif ( ! empty($title))
  {
    if (empty($text))
    {
      $text = $title;
    }
    $attrs['title'] = $title;
  }
  
  return tag('a', $attrs, $text ?: $href);
}


/**
 * Local anchor
 *
 * @param  string Identifier key name
 * @param  string Inner text value
 * @param  mixed  Attributes
 * @return string
 */
function anchor($name, $text = '', $args = array())
{
  $attrs = array();
  
  $attrs['id'] = preg_replace('/[^\w-]/', '', $name);
  
  return tag('a', $attrs += (array) $args, $text);
}


/**
 * Image tag
 *
 * @param  string URL string
 * @param  mixed  Image title|Attributes
 * @return string
 */
function img($url, $alt = '')
{
  $default = extn($url, TRUE);
  
  if (is_assoc($alt))
  {
    $attrs = $alt;
    $alt   = '';

    $default = isset($attrs['alt']) ? $attrs['alt'] : $default;
  }
  elseif ( ! empty($alt))
  {
    $default = $alt;
  }

  $attrs['alt']   =
  $attrs['title'] = $default;
  $attrs['src']   = $url;

  return tag('img', $attrs);
}


// dynamic tags
call_user_func(function()
{
  $code = <<<'PHP'
  
  function %s($text, $args = array())
  {
    if (is_string($args))
    {
      $args = args(attrs($args));
    }
    
    return tag(__FUNCTION__, $args, $text);
  }
  
PHP;

  foreach (array('p', 'div', 'span') as $tag)
  {// TODO: wich tags should be?
    eval(sprintf($code, $tag));
  }
});

/* EOF: ./lib/html.php */
