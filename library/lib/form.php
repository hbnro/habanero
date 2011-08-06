<?php

/**
 * Form related functions library
 */

class form extends prototype
{

  /**
   * Build a basic form
   *
   * @param     mixed  URL string|Function callback
   * @param     mixed  Function callback
   * @param     array  Options hash
   * @staticvar array  Defaults
   * @return    string
   */
  final public static function to($action, $content, array $params = array())
  {
    static $defs = array(
              'action'    => '.',
              'method'    => GET,
              'content'   => 'raise',
              'multipart' => FALSE,
            );
    
    
    if (is_assoc($action))
    {
      $params += $action;
    }
    elseif ( ! isset($params['action']))
    {
      $params['action'] = $action;
    }
    
    if (is_assoc($content))
    {
      $params += $content;
    }
    elseif ( ! isset($params['content']))
    {
      $params['content'] = $content;
    }
    
    
    if (empty($params['action']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::to', 'input' => 'action')));
    }
    elseif (is_closure($params['action']))
    {
      return filter('form::to', $params['action']);
    }
    
    
    $params = filter('form::to', extend($defs, $params), TRUE);
    
    if ( ! is_closure($params['content']))
    {
      raise(ln('failed_to_execute', array('callback' => $params['content'])));
    }
    
    
    if ( ! empty($params['method']) && ($params['method'] <> GET))
    {
      if (is_true($params['multipart']))
      {
        $params['enctype'] = 'multipart/form-data';
      }
      $params['method']  = 'post';
    }
    
    
    $callback = $params['content'];
    
    unset($params['multipart'], $params['content']);
    
    $params['method'] = strtolower($params['method'] ?: GET);
    $params['action'] = $params['action'] === '.' ? '' : $params['action'];
    
    ob_start();
    lambda($callback, $params);
    
    echo '<div style="display:none">';
    echo '<input type="hidden" name="_token" value="', TOKEN, '">';
    
    if (preg_match('/^(?:put|delete)$/', $params['method']))
    {
      echo '<input type="hidden" name="_method" value="', $params['method'], '"/>';
    }
    
    echo '</div>';
    
    $out   = ob_get_clean();
    $attrs = attrs($params);
    
    return "<form$attrs>\n$out\n</form>";
  }
  
  
  /**
  * Input type file
  *
  * @param  mixed  Input name
  * @param  mixed  Attributes
  * @return string
  */
  final public static function file($name, array $args = array())
  {
   return form::input('file', $name, '', $args);
  }
  
  
  /**
  * Dynamic form fields
  *
  * @param     mixed  Options hash|...
  * @staticvar array  Defaults
  * @return    string
  */
  final public static function field($params)
  {
    static $defs = array(
              'type'    => '',
              'name'    => '',
              'value'   => '',
              'label'   => '',
              'options' => array(), 
              'before'  => '',
              'after'   => '',
              'div'     => '',
            );
    
    
   $out  = array();
   $args = func_get_args();
   
   
   foreach ($args as $one)
   {
      if (is_assoc($one))
      {
        $one = filter('form::field', extend($defs, $one), TRUE);
        
        switch ($one['type'])
        {
          case 'file';
            $input = form::file($one['name'], (array) $one['options']);
          break;
          case 'group';
          case 'select';
          case 'textarea';
            $input = lambda(array('form', $one['type']), $one['name'], $one['value'], (array) $one['options']);
          break;
          default;
            $input = form::input($one['type'], $one['name'], $one['value'], (array) $one['options']);
          break;
        }
        
        $format = ! empty($one['div']) ? sprintf('<div%s>%%s</div>', attrs($one['div'])) : '%s';
        $label  = ! empty($one['label']) ? form::label($one['name'], $one['label']) : '';
        
        $out  []= sprintf($format, $one['before'] . $label . $input . $one['after']);
      }
      elseif (is_array($one))
      {
        $out []= call_user_func_array('form::input', $one);
      }
      elseif (is_scalar($one))
      {
        $out []= $one;
      }
    }
    
    return sprintf("<div>%s</div>\n", join('', $out));
  }
  
  
  /**
  * Generic input tag
  *
  * @param     mixed  Input type|Function callback
  * @param     mixed  Input name
  * @param     mixed  Input value
  * @param     array  Options hash
  * @staticvar array  Defaults
  * @return    string
  */
  final public static function input($type, $name, $value = '', array $params = array())
  {
    static $defs = array(
              'type'  => '',
              'name'  => '',
              'value' => '',
            );
    
    
    if (is_assoc($type))
    {
      $params += $type;
    }
    elseif ( ! isset($params['type']))
    {
      $params['type'] = $type;
    }
    
    if (is_assoc($name))
    {
      $params += $name;
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }
    
    if (is_assoc($value))
    {
      $params += $value;
    }
    elseif ( ! isset($params['value']))
    {
      $params['value'] = $value;
    }
    
    
    if (empty($params['type']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::input', 'input' => 'type')));
    }
    elseif (is_closure($params['type']))
    {
      return filter('form::input', $params['type']);
    }
    
    
    $params = filter('form::input', extend($defs, $params), TRUE);
    
    $key = form::index($params['name'], TRUE);
    
    
    if ( ! preg_match('/^(?:radio|checkbox)$/', $params['type']))
    {
      $params['value'] = form::value($key, $params['value']);
    }
    else
    {
      $default = form::value($params['name'], form::value($key));
      
      $params['checked'] = is_array($default) ? in_array($params['value'], $default) : $default === $params['value'];
    }
    
    if (empty($params['id']))
    {
      $params['id'] = strtr($key, '.', '_');
    }
    
    foreach (array_keys($params) as $key)
    {
      if (is_empty($params[$key]))
      {
        unset($params[$key]);
      }
    }
    
    $attrs = attrs($params);
    
    return "<input$attrs/>";
  }
  
  
  /**
  * Form select dropdown
  *
  * @param  mixed  Select name|Function callback
  * @param  array  Option values
  * @param  array  Options hash
  * @return string
  */
  final public static function select($name, array $options, array $params = array())
  {
    if (is_assoc($name))
    {
      $params += $name;
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }
    
    
    if (empty($params['name']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::select', 'input' => 'name')));
    }
    elseif (is_closure($params['name']))
    {
      return filter('form::select', $params['name']);
    }
    
    
    if ( ! isset($params['default']))
    {
      $params['default'] = key($options);
    }
    
    
    $out     = '';
    $args    = array();
    
    $key     = form::index($params['name'], TRUE);
    $default = form::value($key, $params['default']);
    
    foreach ($options as $key => $value)
    {
      if (is_array($value))
      {
        $out .= '<optgroup label="' . ents($key, TRUE) . '">';
        
        foreach ($value as $key => $val)
        {
          $val  = ents($val, TRUE);
          $sel  = is_array($default) ? (in_array($key, $default) ? ' selected' : '') : ( ! strcmp($key, $default) ? ' selected' : '');
          $out .= "<option value=\"$key\"$sel>$val</option>\n";
        }
        
        $out .= '</optgroup>';
        continue;
      }
      
      $value = ents($value, TRUE);
      $sel   = is_array($default) ? (in_array($key, $default) ? ' selected' : '') : ( ! strcmp($key, $default) ? ' selected' : '');
      $out  .= "<option value=\"$key\"$sel>$value</option>\n";
    }
    
    
    if ( ! empty($params['multiple']) && (substr($params['name'], -2) <> '[]'))
    {
      $params['name'] .= is_true($params['multiple']) ? '[]' : '';
    }
    
    if (empty($params['id']))
    {
      $args['id']   = strtr($key, '.', '_');
    }
    $args['name'] = $params['name'];
    
    unset($params['default']);
    
    
    $args  = extend($params, $args);
    $args  = filter('form::select', $args, TRUE);
    
    $attrs = attrs($args);
    
    return "<select$attrs>\n$out</select>";
  }
  
  
  /**
  * Form checkbox/radio group
  *
  * @param     mixed  Group name|Function callback
  * @param     mixed  Group values
  * @param     array  Options hash
  * @staticvar array  Defaults
  * @return    string
  */
  final public static function group($name, array $options, array $params = array())
  {
    static $defs = array(
              'name'      => '',
              'default'   => '',
              'multiple'  => FALSE,
              'placement' => 'before',
              'wrapper'   => '<div><h3>%s</h3>%s</div>',
              'break'     => '<br/>',
            );
    
    
    if (is_assoc($name))
    {
      $params += $name;
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }
    
    
    if (empty($params['name']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::group', 'input' => 'name')));
    }
    elseif (is_closure($params['name']))
    {
      return filter('form::group', $params['name']);
    }
    
    
    $params = filter('form::group', extend($defs, $params), TRUE);
    
    $out = '';
    $key = form::index($params['name'], TRUE);
    
    $default = (array) form::value($params['name'], form::value($key));
    $index   = strtr($key, '.', '_');
    $name    = $params['name'];
    $old     = $params;
    
    unset($old['name']);
    
    if (is_true($params['multiple']) && (substr($params['name'], -2) <> '[]'))
    {
      $params['name'] .= '[]';
    }
    
    
    foreach ($options as $key => $value)
    {
      if (is_array($value))
      {
        $out .= sprintf($params['wrapper'], ents($key, TRUE), form::group($name, $value, $params));
        continue;
      }
      
      $value = ents($value, TRUE);
      
      $input = '<input type="' . ($params['multiple'] ? 'checkbox' : 'radio')
             . '" value="' . $key . '" title="' . $value . '" name="'
             . $params['name'] . '" id="' . $index . '_' . $key . '"'
             . (in_array($key, $default) ? ' checked' : '')
             . '/>';
      
      $label = '<label for="' . $index . '_' . $key . '">'
             . ($params['placement'] === 'before' ? $input : '') . $value
             . ($params['placement'] === 'after' ? $input : '')
             . '</label>';
      
      $out .= $label . $params['break'];
    }
    
    return $out;
  }
  
  
  /**
  * Form textarea
  *
  * @param  string Textfield name
  * @param  string Inner text
  * @param  array  Attributes hash
  * @return string
  */
  final public static function textarea($name, $value = '', array $args = array())
  {
    static $defs = array(
             'cols' => 40,
             'rows' => 6,
           );
    
    
    if (is_assoc($name))
    {
      $args += $name;
    }
    elseif ( ! isset($args['name']))
    {
      $args['name'] = $name;
    }
    
    if (is_assoc($value))
    {
      $args += $value;
    }
    elseif ( ! isset($params['text']))
    {
      $args['text'] = $value;
    }
    
    
    if (empty($args['name']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::group', 'input' => 'name')));
    }
    elseif (is_callable($args['name']))
    {
      return filter('form::group', $args['name']);
    }
    
    
    if ($id = form::index($args['name'], TRUE))
    {
      $args['text'] = form::value($id, $value);
      $args['id']   = strtr($id, '.', '_');
      $args['name'] = $args['name'];
    }
   
    $args  = filter('form::textarea', extend($defs, $args), TRUE);
    $value = ents($args['text'], TRUE);
    
    unset($args['text']);
    
        
    return sprintf('<textarea%s>%s</textarea>', attrs($args), $value);
  }
  
  
  /**
  * Form labels
  *
  * @param  string Input name|Function callback|Attributes
  * @param  mixed  Label text|Attributes
  * @param  array  Attributes
  * @return void
  */
  final public static function label($for, $text = '', array $args = array())
  {
    if (is_assoc($for))
    {
      $args += $for;
    }
    elseif (is_callable($for))
    {
      return filter('form::label', $for);
    }
    elseif ( ! isset($args['for']))
    {
      $args['for'] = $for;
    }
    
    if (is_assoc($text))
    {
      $args += $text;
    }
    elseif ( ! isset($args['text']))
    {
      $args['text'] = $text;
    }
    
    
    if (empty($args['text']))
    {
      raise(ln('function_or_param_missing', array('name' => 'form::label', 'input' => 'text')));
    }
    
    $text = $args['text'];
    unset($args['text']);
    
    if ($id = form::index($for, TRUE))
    {
      $args['for'] = strtr($id, '.', '_');
    }
    
    return sprintf('<label%s>%s</label>', attrs($args), $text);
  }
  
  
  /**
  * Default field value
  *
  * @param  mixed  Input name
  * @param  mixed  Default value
  * @return string
  */
  final public static function value($from, $or = FALSE)
  {
    $set   = method() <> 'GET' ? $_POST : $_GET;
    $value = value($set, $from, $or);
    
    return $value;
  }
  
  
  
  /**#@+
   * @ignore
   */
   
  // dynamic input identifier
  final private static function index($name = '', $inc = FALSE)
  {
    static $num = 0;
  
    
    if ( ! empty($name))
    {
      $name = preg_replace('/\[([^\[\]]+)\]/', '.\\1', $name);
      $name = preg_replace_callback('/\[\]/', function($match)
        use($inc, &$num)
      {
        return sprintf('.%d', is_true($inc) ? $num++ : $num);
      }, $name);
    }
    
    return $name;
  }

  /**#@-*/
}


// dynamic inputs
lambda(function()
{
  $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
  
  foreach ($test['types'] as $type)
  {
    if ( ! form::defined(strtr($type, '-', '_')))
    {
      form::implement(strtr($type, '-', '_'), function($name, $value = '', array $args = array())
        use($type)
      {
        return form::input($type, $name, $value, $args);
      });
    }
  }
});

/* EOF: ./lib/form.php */