<?php

/**
 * Vagely inspirated on jQuery/XHP syntax
 */

class dom extends prototype {
  static function missing($method, $arguments) {
    return new spandex("<$method/>", $arguments);
  }
}

class spandex
{

  /**#@+
   * @ignore
   */

  // tag name
  protected $tag = 'TEXT';

  // nodes data
  protected $node = array();

  // node attributes
  protected $attrs = array();

  /**#@-*/



  /**
   * Class constructor
   *
   * @param  string Tag name
   * @param  mixed  Attributes
   * @return object
   */
  final public function __construct($tag, $args = array())
  {
    static $fulltag = '([a-z][a-z0-9:-]*)([^>]*)';


    if (preg_match("/^.*<$fulltag>(.*?)<\/\\1>.*$/Uis", $tag, $match))
    {
      $this->tag = strtolower($match[1]);
      $test = $this->_fetchArgs($match[2]);
      $this->node []= new self($match[3]);
    }
    elseif (preg_match("/^\s*<$fulltag\/>\s*$/", $tag, $match))
    {
      $this->tag = strtolower($match[1]);
      $test = $this->_fetchArgs($match[2]);
    }
    else
    {
      $test = array();
      $this->tag = 'TEXT';
      $this->node []= $tag;
    }

    $args = ! is_array($args) ? $this->_fetchArgs($args) : $args;

    $this->_fillProps(array_merge($test, $args));
  }


  /**
   * Clone current object
   *
   * @return object
   */
  final public function cloneNode()
  {
    return clone $this;
  }


  /**
   * Delete object sub nodes
   *
   * @return void
   */
  final public function emptyNode()
  {
    $this->node = array();
  }


  /**
   * Prepare object to delete
   *
   * @return void
   */
  final public function removeNode()
  {
    $this->emptyNode();

    $this->tag = '';
    $this->attrs = array();
  }


  /**
   * Sub nodes count
   *
   * @return integer
   */
  final public function length()
  {
    return sizeof(array_filter($this->node, 'is_object')) - 1;
  }


  /**
   * Wraps the current elemento into new node
   *
   * @param  string Tag name
   * @param  mixed  Attributes
   * @return object
   */
  final public function wrap($tag, $args = array())
  {
    $old = new self($tag, $args);
    $new = $this->cloneNode();

    $this->attrs = array();

    $this->node = $old->node;
    $this->node []= $new;

    $this->tag = $old->tag;
    $this->_fillProps($old->attrs);

    return $new;
  }


  /**
   * Unwraps current node
   *
   * @return Spandex
   */
  final public function unWrap()
  {
    $this->tag = 'TEXT';

    return $this;
  }


  /**
   * Appends current node to other element
   *
   * @param  object Spandex element
   * @return object
   */
  final public function appendTo($node)
  {
    $node->append($this);

    return $this;
  }


  /**
   * Appends other element to current node
   *
   * @param  object Spandex element
   * @return object
   */
  final public function append($node)
  {
    $this->node []= $node;

    return $this;
  }


  /**
   * Prepends current node to other element
   *
   * @param  object Spandex element
   * @return object
   */
  final public function prependTo($node)
  {
    $node->prepend($this);

    return $this;
  }


  /**
   * Prepends other element to current node
   *
   * @param  object Spandex element
   * @return object
   */
  final public function prepend($node)
  {
    $key = (sizeof($this->node) + 1) * -1;
    $this->node[$key] = $node;

    return $this;
  }


  /**
   * Retrieve specific node by index
   *
   * @param  integer Index
   * @return mixed
   */
  final public function eq($num)
  {
    $inc = 0;

    foreach (array_keys($this->node) as $index)
    {
      if ( ! is_object($this->node[$index]))
      {
        continue;
      }

      if ($num === $inc)
      {
        return $this->node[$index];
      }
      $inc += 1;
    }
  }


  /**
   * Manipulate attributes
   *
   * @param  mixed Key|Attributes
   * @param  mixed Value
   * @return mixed
   */
  final public function attr($key, $value = '')
  {
    if (is_string($key))
    {
      if ( ! preg_match('/^[a-z][a-z0-9:-]+$/', $key))
      {
        continue;
      }

      if (func_num_args() !== 1)
      {
        $this->attrs[$key] = $value;
      }
      elseif (preg_match('/^[@#.]/', $key))
      {
        return $this->attr($this->_fetchArgs($key));
      }
      return ! empty($this->attrs[$key]) ? $this->attrs[$key] : FALSE;
    }
    elseif (is_array($key))
    {
      foreach ($key as $k => $v)
      {
        $this->attr($k, $v);
      }
    }

    return $this;
  }


  /**
   * Data attributes
   *
   * @param  mixed  Key|Data
   * @param  mixed  Value
   * @return object
   */
  final public function data($key, $value = '')
  {
    if ( ! isset($this->attrs['data']))
    {
      $this->attrs['data'] = array();
    }

    if (is_array($key))
    {
      $this->attrs['data'] += $key;
    }
    else
    {
      $this->attrs['data'][$key] = $value;
    }

    return $this;
  }


  /**
   * Manipulate the current node inner text
   *
   * @param  string Value
   * @return mixed
   */
  final public function text($value = '')
  {
    if (func_num_args() === 0)
    {
      return strip_tags($this->_buildText($this->node, FALSE));
    }

    $this->node = array();
    $this->node []= htmlspecialchars((string) $value);

    return $this;
  }


  /**
   * Manipulate the current node hypertext
   *
   * @param  mixed Content
   * @return mixed
   */
  final public function html($value = '')
  {
    if (func_num_args() === 0)
    {
      return $this->_buildText($this->node, TRUE);
    }

    $this->node = array();
    $this->node []= $value;

    return $this;
  }


  /**
   * Manipulate current node styles
   *
   * @param  mixed Name|Properties
   * @param  mixed Expression
   * @return mixed
   */
  final public function css($prop, $value = '')
  {
    if (is_string($prop))
    {
      $test  = array();
      $style = explode(';', $this->attr('style'));

      foreach (array_map('trim', $style) as $rule)
      {
        $syntax = array_map('trim', explode(':', $rule));

        if ($prop === $syntax[0])
        {
          return ! empty($syntax[1]) ? $syntax[1] : FALSE;
        }
        $test []= join(':', $syntax);
      }

      if (func_num_args() === 2)
      {
        $test []= "$prop:$value";
      }
      $this->attr('style', trim(join(';', $test), ';'));
    }
    elseif (is_array($prop))
    {
      foreach($prop as $k => $v)
      {
        $this->css($k, $v);
      }
    }

    return $this;
  }


  /**
   * Add a class name to current node
   *
   * @param  string Class name
   * @return object
   */
  final public function addClass($name)
  {
    $args = func_get_args();

    $set = $this->_fetchClasses();
    $test = $this->_fetchClasses($args);

    $set = array_unique(array_merge($set, $test));
    $this->attr('class', join(' ', $set));

    return $this;
  }


  /**
   * Remove specified class from current node
   *
   * @param  string Class name
   * @return object
   */
  final public function removeClass($name)
  {
    $args = func_get_args();
    $set = $this->_fetchClasses();

    foreach ($this->_fetchClasses($args) as $one)
    {
      $key = array_search($one, $set);

      if ($key !== FALSE)
      {
        unset($set[$key]);
      }
    }

    $set = array_unique(array_filter($set));
    $this->attr('class', join(' ', $set));

    return $this;
  }


  /**
   * Remove or add specified class if present, or not
   *
   * @param  string Class name
   * @return object
   */
  final public function toggleClass($name)
  {
    if (in_array($name, $this->_fetchClasses()))
    {
      $this->removeClass($name);
    }
    else
    {
      $this->addClass($name);
    }

    return $this;
  }



  /**#@+
   * @ignore
   */

  // dynamic attributes setter
  final public function __call($method, array $args = array())
  {
    if ( ! $args)
    {
      return $this->attr($method);
    }


    if (sizeof($args) > 1)
    {
      $this->attr($method, $args);
    }
    else
    {
      $this->attr($method, array_shift($args));
    }

    return $this;
  }

  // build html output
  final public function __toString()
  {
    static $close = array(
              'img',
              'base',
              'link',
              'meta',
              'embed',
              'param',
              'source',
              'track',
              'area',
            );


    $num = func_num_args();
    $single = ! ($num > 0 && func_get_arg(0));

    if ($num === 0)
    {
      $single = TRUE;
    }


    $attrs = $this->_buildAtts($this->attrs);
    $str = $this->_buildText($this->node, !! $single);

    if ($this->tag === 'TEXT')
    {
      return $str;
    }
    elseif (strlen($this->tag) === 0)
    {
      return FALSE;
    }


    $out = '';

    if (in_array($this->tag, $close))
    {
      $out .= "<{$this->tag}$attrs/>\n";
    }
    else
    {
      $str = preg_replace('/^/m', $this->tag === 'pre' ? '<!--#PRE#-->' : ' ', $str);
      $out .= "<{$this->tag}$attrs>\n$str\n</{$this->tag}>\n";
    }


    if ( ! $num)
    {
      $out = preg_replace('/<([\w:-]+)([^<>]*)>\s*([^<>]+?)\s*<\/\\1>/s', '<\\1\\2>\\3</\\1>', $out);
      $out = preg_replace('/<(a|pre)([^<>]*)>\s*(.+?)\s*<\/\\1>/s', '<\\1\\2>\\3</\\1>', $out);
      $out = preg_replace('/^\s*<!--#PRE#-->/m', '', $out);
      $out = str_replace('<!--#PRE#-->', '', $out);
    }

    $out = preg_replace('/[\r\n]+(?=<)/m', "\n", $out);

    return $out;
  }

  // arguments from attributes string
  final protected function _fetchArgs($text)
  {
    return args($text);
  }

  // retrieve node classes
  final protected function _fetchClasses($test = '')
  {
    if ( ! empty($test))
    {
      $test = preg_split('/[\s\.,]/', join(',', $test));
    }
    else
    {
      $test = explode(' ', $this->attr('class'));
    }

    $test = array_unique(array_map('trim', $test));
    $test = array_filter($test);

    return $test;
  }

  // assemble dynamic attributes
  final protected function _buildAtts($args)
  {
    return attrs($args);
  }

  // retrieve the current node text
  final protected function _buildText($set, $re)
  {
    $out = '';

    ksort($set);

    foreach ($set as $key => $val)
    {
      if (is_object($val))
      {
        $out .= $val->__toString( ! $re);

        if ($re !== TRUE)
        {
          break;
        }
      }
      else
      {
        $out .= (string) $val;
      }
    }
    return $out;
  }

  // assign the node attributes
  final protected function _fillProps($set)
  {
    foreach ($set as $key => $val)
    {
      if (preg_match('/^[a-z][a-z0-9:-]+$/', $key))
      {
        $this->attrs[$key] = $val;
      }
    }
  }

  /**#@-*/

}

/* EOF: ./spandex.php */
