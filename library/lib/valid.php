<?php

/**
 * Validation utilities library
 */
 
class valid extends prototype
{
  
  /**#@+
   * @ignore
   */
  
  // custom input data
  private static $data = array();
  
  // output errors
  private static $error = array();
  
  // validations
  private static $rules = array();
  
  /**#@-*/
  
  
  
  /**
   * Define rules
   *
   * @param  array Validation ruleset
   * @return void
   */
  final public static function setup(array $test = array())
  {
    valid::$error = array();
    valid::$rules = array_fill_keys(array_keys($test), array());
    
    foreach ($test as $field => $rules)
    {
      foreach ((array) $rules as $key => $one)
      {
        if (is_string($one))
        {
          foreach (array_filter(explode(' ', $one)) as $one)
          {
            $name = str_replace('|', '_or_', str_replace('!', 'not_', $one));
            $name = ! is_num($key) ? $key : slug($name, '_', SLUG_STRICT | SLUG_TRIM);
            
            valid::$rules[$field][$name] = $one;
          }
        }
        else
        {
          if (is_string($key) && ! is_num($key))
          {
            valid::$rules[$field][$key] = $one;
          }
          else
          {
            valid::$rules[$field] []= $one;
          }
        }
      }
    }
  }


  /**
   * Execute validation
   *
   * @param  array   Custom data
   * @return boolean
   */
  final public static function done(array $set = array())
  {
    valid::$data = $set ?: $_POST;
    
    $ok = 0;
    
    foreach (valid::$rules as $key => $set)
    {
      if ( ! valid::wrong($key, $set))
      {
        $ok += 1;
      }
    }
    
    return sizeof(valid::$rules) === $ok;
  }


  /**
  * Retrieve field error
  *
  * @param  string Name or key
  * @param  string Default value
  * @return string
  */
  final public static function error($name = '', $default = 'required')
  {
    if ( ! func_num_args())
    {
      return valid::$error;
    }
    
    return ! empty(valid::$error[$name]) ? valid::$error[$name] : $default;
  }
  
  
  /**
   * Retrieve field value
   *
   * @param  string Name or key
   * @param  mixed  Default value
   * @return mixed
   */
  final public static function data($name = '', $default = FALSE)
  {
    if ( ! func_num_args())
    {
      return valid::$data;
    }
    
    return value(valid::$data, $name, $default);
  }


  
  /**#@+
   * @ignore
   */
  
  // dynamic validation
  final private static function wrong($name, array $set = array())
  {
    $fail = FALSE;
    $test = value(valid::$data, $name);
    
    
    foreach ($set as $error => $rule)
    {
      if ($rule === 'required')
      {
        if ( ! trim($test))
        {
          $fail = TRUE;
          break;
        }
      }
      elseif (is_callable($rule))
      {
        if ( ! call_user_func($rule, $test))
        {
          $fail = TRUE;
          break;
        }
      }
      elseif ( ! is_false(strpos($rule, '|')))
      {
        $set     = array_filter(explode('|', $rule));
        $count   = 
        $default = sizeof($set);
        
        
        foreach ($set as $callback)
        {
          if (function_exists($callback) && call_user_func($callback, $test))
          {
            $count -= 1;
          }
        }
        
        
        if ($count === sizeof($set))
        {
          $fail = TRUE;
          break;
        }
      }
      elseif (preg_match('/(?:<>|(?:<|>|!=|==)=?)\s*(.+?)$/', $rule, $match))
      {
        $operator = substr($rule, 0, - strlen($match[1]));
        $value    = addslashes(array_shift(valid::vars($match[1])));
  
        $value    = ! is_num($value) ? "'$value'" : $value;
        $test = ! is_num($test) ? "'$test'" : $test;
  
  
        if ( ! @eval("return $value $operator $test ?: FALSE;"))
        {
          $fail = TRUE;
          break;
        }
      }
      elseif (preg_match('/^([^\[\]]+)\[([^\[\]]+)\]$/', $rule, $match))
      {
        $negate   = substr($match[1], 0, 1) === '!';
        $callback = $negate ? substr($match[1], 1) : $match[1];
        
        if (function_exists($callback))
        {
          if ( ! isset($match[2]))
          {
            $match[2] = NULL;
          }
          
          
          $args = valid::vars($match[2]);
  
          if (substr($callback, 0, 3) === 'is_')
          {
            array_unshift($args, $test);
          }
          
          
          $value = call_user_func_array($callback, $args);
          
          if (( ! $value && ! $negate) OR ($value && $negate))
          {
            $fail = TRUE;
            break;
          }
        }
      }
      elseif (($rule[0] === '%') && (substr($rule, -1) === '%'))
      {
        $expr = sprintf('/%s/us', str_replace('/', '\/', substr($rule, 1, -1)));
        
        if ( ! @preg_match($expr, $test))
        {
          $fail = TRUE;
          break;
        }
      }
      elseif ( ! in_array($test, valid::vars($rule)))
      {
        $fail = TRUE;
        break;
      }
    }
    
    
    if (is_true($fail))
    {
      valid::$error[$name] = is_string($error) ? $error : 'unknown';
      
      return FALSE;
    }
    
    return TRUE;
  }
  
  // dynamic values
  final private static function vars($test)
  {
    $test = array_filter(explode(',', $test));
    
    foreach ($test as $key => $val)
    {
      if (preg_match('/^([\'"]).*\\1$/', $val))
      {
        $test[$key] = substr(trim($val), 1, -1);
      }
      elseif (is_num($val))
      {
        $test[$key] = $val;
      }
      else
      {
        $test[$key] = value(valid::$data, $val);
      }
    }
    
    return $test;
  }
  
  /**#@-*/
}

/* EOF: ./lib/valid.php */
