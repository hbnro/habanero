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
    if (is_assoc($action))
    {
      $params = array_merge($action, $params);
    }
    elseif ( ! isset($params['action']))
    {
      $params['action'] = $action;
    }

    if (is_assoc($content))
    {
      $params = array_merge($content, $params);
    }
    elseif ( ! isset($params['content']))
    {
      $params['content'] = $content;
    }


    if (empty($params['action']))
    {
      raise(ln('function_param_missing', array('name' => 'form::to', 'input' => 'action')));
    }


    $params = array_merge(array(
      'action'    => '.',
      'method'    => GET,
      'content'   => 'raise',
      'multipart' => FALSE,
    ), $defs);

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
    }


    $callback = $params['content'];

    unset($params['multipart'], $params['content']);

    $params['method'] = strtolower($params['method'] ?: GET);
    $params['action'] = $params['action'] === '.' ? '' : $params['action'];


    if (preg_match('/^(put|get|post|delete)\s+(.+?)$/i', $params['action'], $match))
    {
      $params['method'] = strtolower($match[1]);
      $params['action'] = $match[2];
    }

    $params['action'] && $params['action'] = pre_url($params['action']);


    $input = tag('input', array(
      'type' => 'hidden',
      'name' => '_token',
      'value' => TOKEN,
    ));


    if (preg_match('/^(?:put|delete)$/', $params['method']))
    {
      $input .= tag('input', array(
        'type' => 'hidden',
        'name' => '_method',
        'value' => $params['method'],
      ));

      $params['method']  = 'post';//FIX
    }

    $div = tag('div', array(
      'style' => 'display:none',
    ), $input);


    ob_start() && $callback($params);

    $div .= ob_get_clean();

    return tag('form', $params, $div);
  }


  /**
  * Input type file
  *
  * @param  mixed  Input name
  * @param  mixed  Attributes
  * @return string
  */
  final public static function file($name, $args = array())
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
    $out  = array();
    $args = func_get_args();


    foreach ($args as $one)
    {
       if (is_assoc($one))
       {
         $one = array_merge(array(
            'type'    => '',
            'name'    => '',
            'value'   => '',
            'label'   => '',
            'options' => array(),
            'before'  => '',
            'after'   => '',
            'div'     => '',
          ), $one);

         switch ($one['type'])
         {
           case 'file';
             $input = form::file($one['name'], (array) $one['options']);
           break;
           case 'group';
           case 'select';
           case 'textarea';
             $input = form::$one['type']($one['name'], $one['value'], (array) $one['options']);
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
         $out []= apply('form::input', $one);
       }
       elseif (is_scalar($one))
       {
         $out []= $one;
       }
     }

     return tag('div', '', join('', $out));
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
    if (is_assoc($type))
    {
      $params = array_merge($type, $params);
    }
    elseif ( ! isset($params['type']))
    {
      $params['type'] = $type;
    }

    if (is_assoc($name))
    {
      $params = array_merge($name, $params);
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }

    if (is_assoc($value))
    {
      $params = array_merge($value, $params);
    }
    elseif ( ! isset($params['value']))
    {
      $params['value'] = $value;
    }


    if (empty($params['type']))
    {
      raise(ln('function_param_missing', array('name' => 'form::input', 'input' => 'type')));
    }


    $params = array_merge(array(
      'type'  => '',
      'name'  => '',
      'value' => '',
    ), $params);

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

    return tag('input', $params);
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
      $params = array_merge($name, $params);
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }


    if (empty($params['name']))
    {
      raise(ln('function_param_missing', array('name' => 'form::select', 'input' => 'name')));
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
        $sub = '';

        foreach ($value as $key => $val)
        {
          $sub .= tag('option', array(
            'value' => $key,
            'selected' => is_array($default) ? in_array($key, $default) : ! strcmp($key, $default),
          ), ents($val, TRUE));
        }

        $out .= tag('optgroup', array(
          'label' => ents($key, TRUE),
        ), $sub);

        continue;
      }

      $out  .= tag('option', array(
        'value' => $key,
        'selected' => is_array($default) ? in_array($key, $default) : ! strcmp($key, $default),
      ), ents($value, TRUE));
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

    return tag('select', array_merge($params, $args), $out);
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
    if (is_assoc($name))
    {
      $params = array_merge($name, $params);
    }
    elseif ( ! isset($params['name']))
    {
      $params['name'] = $name;
    }


    if (empty($params['name']))
    {
      raise(ln('function_param_missing', array('name' => 'form::group', 'input' => 'name')));
    }


    $params = array_merge(array(
      'name'      => '',
      'default'   => '',
      'multiple'  => FALSE,
      'placement' => 'before',
      'wrapper'   => '<div><h3>%s</h3>%s</div>',
      'break'     => '<br/>',
    ), $params);

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

      $input = tag('input', array(
        'type' => $params['multiple'] ? 'checkbox' : 'radio',
        'name' => $params['name'],
        'value' => $key,
        'checked' => in_array($key, $default),
        'title' => $value,
        'id' => $index . '_' . $key,
      ));


      $text = ($params['placement'] === 'before' ? $input : '')
            . ents($value, TRUE)
            . ($params['placement'] === 'after' ? $input : '');

      $label = tag('label', array(
        'for' => $index . '_' . $key,
      ), $text);

      $out .= $label . $params['break'];
    }

    return $out;
  }


  /**
  * Form textarea
  *
  * @param  string Textfield name
  * @param  string Inner text
  * @param  mixed  Attributes
  * @return string
  */
  final public static function textarea($name, $value = '', $args = array())
  {
    if (is_string($args))
    {
      $args = args(attrs($args));
    }

    if (is_assoc($name))
    {
      $args = array_merge($name, $args);
    }
    elseif ( ! isset($args['name']))
    {
      $args['name'] = $name;
    }

    if (is_assoc($value))
    {
      $args = array_merge($value, $args);
    }
    elseif ( ! isset($params['text']))
    {
      $args['text'] = $value;
    }


    if (empty($args['name']))
    {
      raise(ln('function_param_missing', array('name' => 'form::group', 'input' => 'name')));
    }


    if ($id = form::index($args['name'], TRUE))
    {
      $args['text'] = form::value($id, $value);
      $args['id']   = strtr($id, '.', '_');
      $args['name'] = $args['name'];
    }

    $args  = array_merge(array(
      'cols' => 40,
      'rows' => 6,
    ), $args);

    $value = ents($args['text'], TRUE);

    unset($args['text']);

    return tag('textarea', $args, $value);
  }


  /**
  * Form labels
  *
  * @param  string Input name|Function callback|Attributes
  * @param  mixed  Label text|Attributes
  * @param  mixed  Attributes
  * @return void
  */
  final public static function label($for, $text = '', $args = array())
  {
    if (is_string($args))
    {
      $args = args(attrs($args));
    }

    if (is_assoc($for))
    {
      $args = array_merge($for, $args);
    }
    elseif ( ! isset($args['for']))
    {
      $args['for'] = $for;
    }

    if (is_assoc($text))
    {
      $args = array_merge($text, $args);
    }
    elseif ( ! isset($args['text']))
    {
      $args['text'] = $text;
    }


    if (empty($args['text']))
    {
      raise(ln('function_param_missing', array('name' => 'form::label', 'input' => 'text')));
    }

    $text = $args['text'];
    unset($args['text']);

    if ($id = form::index($for, TRUE))
    {
      $args['for'] = strtr($id, '.', '_');
    }

    return tag('label', $args, $text);
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
    $set   = $_SERVER['REQUEST_METHOD'] <> 'GET' ? $_POST : $_GET;
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
call_user_func(function()
{
  $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;

  foreach ($test['types'] as $type)
  {
    if ( ! form::defined(strtr($type, '-', '_')))
    {
      form::implement(strtr($type, '-', '_'), function($name, $value = '', $args = array())
        use($type)
      {
        return form::input($type, $name, $value, $args);
      });
    }
  }
});

/* EOF: ./lib/tetl/form.php */
