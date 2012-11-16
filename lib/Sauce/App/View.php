<?php

namespace Sauce\App;

class View implements \ArrayAccess
{

  private $sections = array();



  public function __get($key)
  {
    return $this->yield($key);
  }

  public function __set($key, $value)
  {
    $this->section($key, $value);
  }

  public function __invoke($section, array $params = array())
  {
    return $this->yield($section, $params);
  }

  public function __toString()
  {
    return join('', $this->all());
  }

  public function offsetSet($offset, $value)
  {
    $this->$offset = $value;
  }

  public function offsetExists($offset)
  {
    return isset($this->$offset);
  }

  public function offsetUnset($offset)
  {
    unset($this->$offset);
  }

  public function offsetGet($offset)
  {
    return $this->$offset;
  }


  public function clear($name)
  {
    if (isset($this->sections[$name])) {
      unset($this->sections[$name]);
    }
  }

  public function assign($name, $content)
  {
    return $this->section($name, $content);
  }

  public function section($name, $content)
  {
    $this->sections[$name] = array($content);
  }

  public function prepend($section, $content)
  {
    isset($this->sections[$section]) && array_unshift($this->sections[$section], $content);
  }

  public function append($section, $content)
  {
    isset($this->sections[$section]) && $this->sections[$section] []= $content;
  }

  public function yield($section, array $params = array())
  {
    if ( ! isset($this->sections[$section])) {
      return; // TODO: raise exception
    }

    $out = '';

    foreach ($this->sections[$section] as $one) {
      if (is_callable($one)) {
        ob_start() && call_user_func($one, $params);
        $one = ob_get_clean();
      }
      $out .= $one;
    }

    return $out;
  }

  public function all()
  {
    $out = array();

    foreach ($this->sections as $key => $val) {
      if (sizeof($val) === 1) {
        $out[$key] = array_pop($val);
      } else {
        $out[$key] = $val;
      }
    }

    return $out;
  }

}
