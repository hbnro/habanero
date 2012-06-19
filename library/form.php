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
  final public static function to($action, $content, array $params = array()) {
    if (is_assoc($action)) {
      $params = array_merge($action, $params);
    } elseif ( ! isset($params['action'])) {
      $params['action'] = $action;
    }

    if (is_assoc($content)) {
      $params = array_merge($content, $params);
    } elseif ( ! isset($params['content'])) {
      $params['content'] = $content;
    }


    if (empty($params['action'])) {
      raise(ln('function_param_missing', array('name' => 'form::to', 'input' => 'action')));
    }


    $params = array_merge(array(
      'action'    => '.',
      'method'    => 'GET',
      'content'   => 'raise',
      'multipart' => FALSE,
    ), $params);

    if ( ! is_closure($params['content'])) {
      raise(ln('failed_to_execute', array('callback' => $params['content'])));
    }


    if ( ! empty($params['method']) && ($params['method'] <> 'GET')) {
      if ($params['multipart']) {
        $params['enctype'] = 'multipart/form-data';
      }
    }


    $callback = $params['content'];
    $params   = static::ujs($params, TRUE);

    $params['type'] && $params['data']['type'] = $params['type'];

    unset($params['multipart'], $params['content'], $params['type']);

    $params['method'] = strtolower($params['method'] ?: 'GET');
    $params['action'] = $params['action'] === '.' ? '' : $params['action'];


    if (preg_match('/^(put|get|post|delete)\s+(.+?)$/i', $params['action'], $match)) {
      $params['method'] = strtolower($match[1]);
      $params['action'] = $match[2];
    }

    $input  = tag('input', array(
      'type' => 'hidden',
      'name' => '_token',
      'value' => option('csrf_token'),
    ));


    if (preg_match('/^(?:put|delete)$/', $params['method'])) {
      $input .= tag('input', array(
        'type' => 'hidden',
        'name' => '_method',
        'value' => $params['method'],
      ));

      $params['method'] = 'post';
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
  final public static function file($name, array $args = array()) {
    return static::input('file', $name, '', $args);
  }


  /**
  * Dynamic form fields
  *
  * @param     mixed  Options hash|...
  * @staticvar array  Defaults
  * @return    string
  */
  final public static function field($params) {
    $out  = array();
    $args = func_get_args();


    foreach ($args as $one) {
      if (is_assoc($one)) {
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

        switch ($one['type']) {
          case 'file';
            $input = static::file($one['name'], (array) $one['options']);
          break;
          case 'group';
          case 'select';
          case 'textarea';
            $input = static::$one['type']($one['name'], $one['value'], (array) $one['options']);
          break;
          default;
            $input = static::input($one['type'], $one['name'], $one['value'], (array) $one['options']);
          break;
        }

        $format = is_array($one['div']) ? sprintf('<div%s>%%s</div>', attrs($one['div'])) : '%s';
        $label  = ! empty($one['label']) ? static::label($one['name'], "<span>$one[label]</span>\n$input") : $input;

        $out  []= sprintf($format, "$one[before]\n$label\n$one[after]");
      } elseif (is_array($one)) {
        $out []= call_user_func_array('form::input', $one);
      } elseif (is_scalar($one)) {
        $out []= $one;
      }
    }

    return join('', $out);
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
  final public static function input($type, $name, $value = '', array $params = array()) {
    if (is_assoc($type)) {
      $params = array_merge($type, $params);
    } elseif ( ! isset($params['type'])) {
      $params['type'] = $type;
    }

    if (is_assoc($name)) {
      $params = array_merge($name, $params);
    } elseif ( ! isset($params['name'])) {
      $params['name'] = $name;
    }

    if (is_assoc($value)) {
      $params = array_merge($value, $params);
    } elseif ( ! isset($params['value'])) {
      $params['value'] = $value;
    }


    if (empty($params['type'])) {
      raise(ln('function_param_missing', array('name' => 'form::input', 'input' => 'type')));
    }


    $params = array_merge(array(
      'type'  => '',
      'name'  => '',
      'value' => '',
    ), $params);

    $params = static::ujs($params);
    $key    = static::index($params['name'], TRUE);


    if ( ! preg_match('/^(?:radio|checkbox)$/', $params['type'])) {
      $params['value'] = static::value($key, $params['value']);
    } else {
      $default = static::value($params['name'], static::value($key));

      $params['checked'] = is_array($default) ? in_array($params['value'], $default) : $default === $params['value'];
    }

    if (empty($params['id'])) {
      $params['id'] = strtr($key, '.', '_');
    }

    foreach (array_keys($params) as $key) {
      if (is_empty($params[$key])) {
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
  final public static function select($name, array $options, array $params = array()) {
    if (is_assoc($name)) {
      $params = array_merge($name, $params);
    } elseif ( ! isset($params['name'])) {
      $params['name'] = $name;
    }


    if (empty($params['name'])) {
      raise(ln('function_param_missing', array('name' => 'form::select', 'input' => 'name')));
    }


    if ( ! isset($params['default'])) {
      $params['default'] = key($options);
    }


    $out     = '';
    $args    = array();

    $params  = static::ujs($params);
    $key     = static::index($params['name'], TRUE);
    $default = static::value($key, $params['default']);

    $params['type'] && $params['data']['type'] = $params['type'];

    unset($params['type']);


    foreach ($options as $key => $value) {
      if (is_array($value)) {
        $sub = '';

        foreach ($value as $key => $val) {
          $sub .= tag('option', array(
            'value' => $key,
            'selected' => is_array($default) ? in_array($key, $default, TRUE) : ! strcmp($key, $default),
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


    if ( ! empty($params['multiple']) && (substr($params['name'], -2) <> '[]')) {
      $params['name'] .= $params['multiple'] ? '[]' : '';
    }

    if (empty($params['id'])) {
      $args['id'] = $params['name'];
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
  final public static function group($name, array $options, array $params = array()) {
    if (is_assoc($name)) {
      $params = array_merge($name, $params);
    } elseif ( ! isset($params['name'])) {
      $params['name'] = $name;
    }


    if (empty($params['name'])) {
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
    $key = static::index($params['name'], TRUE);

    $default = (array) static::value($params['name'], static::value($key, $params['default']));
    $index   = strtr($key, '.', '_');
    $name    = $params['name'];
    $old     = $params;

    unset($old['name']);

    if ($params['multiple'] && (substr($params['name'], -2) <> '[]')) {
      $params['name'] .= '[]';
    }

    foreach ($options as $key => $value) {
      if (is_array($value)) {
        $out .= sprintf($params['wrapper'], ents($key, TRUE), static::group($name, $value, $params));
        continue;
      }

      $input = tag('input', array(
        'type' => $params['multiple'] ? 'checkbox' : 'radio',
        'name' => $params['name'],
        'value' => $key,
        'checked' => in_array($key, $default, TRUE),
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
  * @param  array  Attributes
  * @return string
  */
  final public static function textarea($name, $value = '', array $args = array()) {
    if (is_assoc($name)) {
      $args = array_merge($name, $args);
    } elseif ( ! isset($args['name'])) {
      $args['name'] = $name;
    }

    if (is_assoc($value)) {
      $args = array_merge($value, $args);
    } elseif ( ! isset($params['text'])) {
      $args['text'] = $value;
    }


    if (empty($args['name'])) {
      raise(ln('function_param_missing', array('name' => 'form::textarea', 'input' => 'name')));
    }


    $args = static::ujs($args);

    $args['type'] && $args['data']['type'] = $args['type'];

    unset($args['type']);

    if ($id = static::index($args['name'], TRUE)) {
      $args['text'] = static::value($id, $value);
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
  * @param  array  Attributes
  * @return void
  */
  final public static function label($for, $text = '', $args = array()) {
    if (is_assoc($for)) {
      $args = array_merge($for, $args);
    } elseif ( ! isset($args['for'])) {
      $args['for'] = $for;
    }

    if (is_assoc($text)) {
      $args = array_merge($text, $args);
    } elseif ( ! isset($args['text'])) {
      $args['text'] = $text;
    }


    if (empty($args['text'])) {
      raise(ln('function_param_missing', array('name' => 'form::label', 'input' => 'text')));
    }

    $text = $args['text'];
    unset($args['text']);

    if ($id = static::index($for, TRUE)) {
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
  final public static function value($from, $or = FALSE) {
    $set   = value($_SERVER, 'REQUEST_METHOD') <> 'GET' ? $_POST : $_GET;
    $value = value($set, $from, $or);

    return $value;
  }


  /**
   * Dynamic inputs
   *
   * @param     string Method
   * @param     array  Arguments
   * @staticvar array  Input types
   * @staticvar array  Allowed methods
   * @return    string
   */
  final public static function missing($method, $arguments) {
    static $test = NULL,
           $allow = array('get', 'put', 'post', 'delete');


    if (is_null($test)) {
      $test = include LIB.DS.'assets'.DS.'scripts'.DS.'html_vars'.EXT;
      $test = $test['types'];
    }


    $type = strtr($method, '_', '-');

    if ( ! in_array($type, $test)) {
      $params = array();
      $lambda = array_pop($arguments);
      $action = array_shift($arguments);

      foreach (explode('_', $method) as $part) {
        if (in_array($part, $allow)) {
          $params['method'] = $part;
        } else {
          $params[$part] = array_shift($arguments) ?: TRUE;
        }
      }


      if ( ! empty($params['method'])) {
        return static::to($action, $lambda, $params);
      }
      raise(ln('method_missing', array('class' => get_called_class(), 'name' => $method)));
    }

    array_unshift($arguments, $type);

    return call_user_func_array('form::input', $arguments);
  }



  /**#@+
   * @ignore
   */

  // dynamic input identifier
  final private static function index($name = '', $inc = FALSE) {
    static $num = 0;


    if ( ! empty($name)) {
      $name = preg_replace('/\[([^\[\]]+)\]/', '.\\1', $name);
      $name = preg_replace_callback('/\[\]/', function ($match)
        use($inc, &$num) {
        return sprintf('.%d', $inc ? $num++ : $num);
      }, $name);
    }

    return $name;
  }

  // unobstrusive javascript
  final private static function ujs($params, $form = FALSE) {
    $params = array_merge(array(
      'url'          => FALSE,
      'type'         => FALSE,
      'method'       => FALSE,
      'remote'       => FALSE,
      'params'       => FALSE,
      'confirm'      => FALSE,
      'disable_with' => FALSE,
    ), $params);


    $params['url'] && $params['data']['url'] = $params['url'];
    $params['confirm'] && $params['data']['confirm'] = $params['confirm'];
    $params['params'] && $params['data']['params'] = http_build_query($params['params']);
    $params['disable_with'] && $params['data']['disable-with'] = $params['disable_with'];

    $params['remote'] && $params['data']['remote'] = 'true';

    unset($params['disable_with'], $params['confirm'], $params['remote'], $params['url']);

    if ( ! $form) {
      $params['method'] && $params['data']['method'] = $params['method'];
      unset($params['method']);
    }

    return $params;
  }

  /**#@-*/
}

/* EOF: ./library/form.php */
