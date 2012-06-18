<?php

/**
 * Fancy tag helper
 */

class tag extends prototype
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


  // hidden constructor
  private function __construct($tag, array $args = array()) {
    static $fulltag = '([a-z][a-z0-9:-]*)([^>]*)';


    if (preg_match("/^.*<$fulltag>(.*?)<\/\\1>.*$/Uis", $tag, $match)) {
      $this->tag = strtolower($match[1]);
      $test = args(attrs($match[2]));
      $this->node []= new self($match[3]);
    } elseif (preg_match("/^\s*<$fulltag\/>\s*$/", $tag, $match)) {
      $this->tag = strtolower($match[1]);
      $test = args(attrs($match[2]));
    } else {
      $test = array();
      $this->tag = 'TEXT';
      $this->node []= $tag;
    }

    $this->fill_props(array_merge($test, $args));
  }

  /**#@-*/


  /**
   * Magic tags
   *
   * @param     string Method
   * @param     mixed  Arguments
   * @staticvar array  HTML tags
   * @return    string
   */
  final public static function missing($method, array $arguments) {
    static $test = NULL,
           $close = array(),
           $default = array(
             'link' => 'rel',
             'base' => 'href',
             'source' => 'src',
             'track' => 'default',
             'meta' => 'name',
             'img' => 'alt',
           );


    if (is_null($test)) {
      $close = $test['empty'];

      $test  = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
      $test  = array_merge($test['complete'], $test['empty']);
    }


    if ( ! in_array($method, $test)) {
      raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
    }


    $plain = '';
    $first = array_shift($arguments);

    if ( ! is_array($first)) {
      $plain = (string) $first;
    } else {
      array_unshift($arguments, $first);
    }

    $el = static::create("<$method/>", call_user_func_array('array_merge', $arguments));

    if ( ! empty($default[$method])) {
      $el->{$default[$method]}($plain);
    } else {
      $el->text($plain);
    }

    return $el;
  }


  /**
   * Elements
   *
   * @param  string Tag name
   * @param  mixed  Attributes
   * @return object
   */
  final public static function create($tag, array $args = array()) {
    return new static($tag, $args);
  }


  /**
   * Clone current object
   *
   * @return object
   */
  final public function clone_node() {
    return clone $this;
  }


  /**
   * Delete object sub nodes
   *
   * @return void
   */
  final public function empty_node() {
    $this->node = array();
  }


  /**
   * Prepare object to delete
   *
   * @return void
   */
  final public function remove_node() {
    $this->empty_node();

    $this->tag   = '';
    $this->attrs = array();
  }


  /**
   * Sub nodes count
   *
   * @return integer
   */
  final public function length() {
    return sizeof(array_filter($this->node, 'is_object')) - 1;
  }


  /**
   * Wraps the current elemento into new node
   *
   * @param  string Tag name
   * @param  mixed  Attributes
   * @return object
   */
  final public function wrap($tag, array $args = array()) {
    $old = new self($tag, $args);
    $new = $this->clone_node();

    $this->attrs  = array();

    $this->node   = $old->node;
    $this->node []= $new;

    $this->tag    = $old->tag;
    $this->fill_props($old->attrs);

    return $new;
  }


  /**
   * Unwraps current node
   *
   * @return Spandex
   */
  final public function unwrap() {
    $this->tag = 'TEXT';

    return $this;
  }


  /**
   * Appends current node to other element
   *
   * @param  object Spandex element
   * @return object
   */
  final public function append_to($node) {
    $node->append($this);

    return $this;
  }


  /**
   * Appends other element to current node
   *
   * @param  object Spandex element
   * @return object
   */
  final public function append($node) {
    $this->node []= $node;

    return $this;
  }


  /**
   * Prepends current node to other element
   *
   * @param  object Spandex element
   * @return object
   */
  final public function prepend_to($node) {
    $node->prepend($this);

    return $this;
  }


  /**
   * Prepends other element to current node
   *
   * @param  object Spandex element
   * @return object
   */
  final public function prepend($node) {
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
  final public function eq($num) {
    $inc = 0;

    foreach (array_keys($this->node) as $index) {
      if ( ! is_object($this->node[$index])) {
        continue;
      }

      if ($num === $inc) {
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
  final public function attr($key, $value = '') {
    if (is_string($key)) {
      if ( ! preg_match('/^[a-z][a-z0-9:-]+$/', $key)) {
        continue;
      }

      if (func_num_args() !== 1) {
        $this->attrs[$key] = $value;
      }
      return ! empty($this->attrs[$key]) ? $this->attrs[$key] : FALSE;
    } elseif (is_array($key)) {
      foreach ($key as $k => $v) {
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
  final public function data($key, $value = '') {
    if ( ! isset($this->attrs['data'])) {
      $this->attrs['data'] = array();
    }

    if (is_array($key)) {
      $this->attrs['data'] += $key;
    } else {
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
  final public function text($value = '') {
    if (func_num_args() === 0) {
      return strip_tags($this->build_text($this->node, FALSE));
    }

    $this->node   = array();
    $this->node []= htmlspecialchars((string) $value);

    return $this;
  }


  /**
   * Manipulate the current node hypertext
   *
   * @param  mixed Content
   * @return mixed
   */
  final public function html($value = '') {
    if (func_num_args() === 0) {
      return $this->build_text($this->node, TRUE);
    }

    $this->node   = array();
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
  final public function css($prop, $value = '') {
    if (is_string($prop)) {
      $test  = array();
      $style = explode(';', $this->attr('style'));

      foreach (array_map('trim', $style) as $rule) {
        $syntax = array_map('trim', explode(':', $rule));

        if ($prop === $syntax[0]) {
          return ! empty($syntax[1]) ? $syntax[1] : FALSE;
        }
        $test []= join(':', $syntax);
      }

      if (func_num_args() === 2) {
        $test []= "$prop:$value";
      }
      $this->attr('style', trim(join(';', $test), ';'));
    } elseif (is_array($prop)) {
      foreach($prop as $k => $v) {
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
  final public function add_class($name) {
    $args = func_get_args();

    $set  = $this->fetch_classes();
    $test = $this->fetch_classes($args);

    $set  = array_unique(array_merge($set, $test));
    $this->attr('class', join(' ', $set));

    return $this;
  }


  /**
   * Remove specified class from current node
   *
   * @param  string Class name
   * @return object
   */
  final public function remove_class($name) {
    $args = func_get_args();
    $set  = $this->fetch_classes();

    foreach ($this->fetch_classes($args) as $one) {
      $key = array_search($one, $set);

      if ($key !== FALSE) {
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
  final public function toggle_class($name) {
    if (in_array($name, $this->fetch_classes())) {
      $this->remove_class($name);
    } else {
      $this->add_class($name);
    }

    return $this;
  }



  /**#@+
   * @ignore
   */

  // dynamic attributes setter
  final public function __call($method, array $args = array()) {
    if ( ! $args) {
      return $this->attr($method);
    }


    if (sizeof($args) > 1) {
      $this->attr($method, $args);
    } else {
      $this->attr($method, array_shift($args));
    }

    return $this;
  }

  // build html output
  final public function __toString() {
    $num    = func_num_args();
    $single = ! ($num > 0 && func_get_arg(0));

    if ($num === 0) {
      $single = TRUE;
    }


    $str = $this->build_text($this->node, !! $single);

    if ($this->tag === 'TEXT') {
      return $str;
    } elseif (strlen($this->tag) === 0) {
      return FALSE;
    }


    $out = tag($this->tag, $this->attrs, $str);

    if ( ! $num) {
      $out = preg_replace('/<([\w:-]+)([^<>]*)>\s*([^<>]+?)\s*<\/\\1>/s', '<\\1\\2>\\3</\\1>', $out);
      $out = preg_replace('/<(a|pre)([^<>]*)>\s*(.+?)\s*<\/\\1>/s', '<\\1\\2>\\3</\\1>', $out);
      $out = preg_replace('/^\s*<!--#PRE#-->/m', '', $out);
      $out = str_replace('<!--#PRE#-->', '', $out);
    }

    $out = preg_replace('/[\r\n]+(?=<)/m', "\n", $out);

    return $out;
  }

  // retrieve node classes
  final protected function fetch_classes($test = '') {
    if ( ! empty($test)) {
      $test = preg_split('/[\s\.,]/', join(',', $test));
    } else {
      $test = explode(' ', $this->attr('class'));
    }

    $test = array_unique(array_map('trim', $test));
    $test = array_filter($test);

    return $test;
  }

  // retrieve the current node text
  final protected function build_text($set, $re) {
    $out = '';

    ksort($set);

    foreach ($set as $key => $val) {
      if (is_object($val)) {
        $out .= $val->__toString( ! $re);

        if ($re !== TRUE) {
          break;
        }
      } else {
        $out .= (string) $val;
      }
    }
    return $out;
  }

  // assign the node attributes
  final protected function fill_props($set) {
    foreach ($set as $key => $val) {
      if (preg_match('/^[a-z][a-z0-9:-]+$/', $key)) {
        $this->attrs[$key] = $val;
      }
    }
  }

  /**#@-*/

}

/* EOF: ./library/tag.php */
