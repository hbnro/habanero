<?php

/**
 * CSS manipulation library
 */

class cssp extends prototype
{

  /**#@+
   * @ignore
   */

  // output
  private static $css = array();

  // attributes
  private static $sets = array();
  private static $props = array();
  private static $mixins = array();
  private static $imports = array();

  // hackish conversion
  private static $fixate_array_expr = array(
    '/\s*(@[\w\-]+)\s+([^\{\};]+);|\s*([^:\r\n]+)\s*:\s*([^\r\n;]+);/m' => "\ntrim('\\1\\3!').mt_rand()=>'\\2\\4',",
    '/\s*([^\r\n;]+)\s*(:?)\s*\{/m' => "\ntrim('\\1\\2!').mt_rand()=>array(",
    '/([^;]+);/m' => '\\1',
    '/(?<!\w)\}/' => '),',
    '/[\r\n]+/' => "\n",
  );

  // formatting
  private static $fixate_css_expr = array(
    '/[\s\r\n\t]*,+[\s\r\n\t]*/s' => ',',
    '/;\s*(?=[#@\w\-]+\s*:?)/' => ";\n",
    '/;?[\s\r\n\t]*\}/s' => ";\n}",
    '/[\s\r\n\t]+\{/s' => ' {',
    '/:\s+\{/' => ':{',
    '/\{/' => "{\n",
  );

  // defaults
  protected static $defs = array(
    'path' => APP_PATH,
    'extension' => '.css',
  );

  /**#@-*/



  /**
   * Assign properties
   *
   * @param  array Hash
   * @return void
   */
  final public static function assign(array $vars) {
    foreach ($vars as $key => $val) {
      static::$props[$key] = $val;
    }
  }


  /**
   * Render file
   *
   * @param  string  Path
   * @return void
   */
  final public static function render($path) {
    return static::parse(static::load_file($path));
  }


  /**
   * Parse expression
   *
   * @param  string  CSS rules
   * @return void
   */
  final public static function parse($rules) {
    static::$css     =
    static::$sets    =
    static::$props   =
    static::$mixins  =
    static::$imports = array();

    static::parse_buffer($rules);
    static::build_properties(static::$sets);

    foreach (static::$sets as $key => $set) {
      static::$css []= static::build_rules($set, $key);
    }

    $text = join("\n", static::$css);

    $text = preg_replace('/\b(\w+)\!\(([^\(\)]+)\)/is', '\\1(\\2)', $text);
    $text = preg_replace('/\b0(?:p[xtc]|e[xm]|[cm]m|in|%)/', 0, $text);
    $text = preg_replace('/__ENTITY(\w+)__/', '&\\1;', $text);
    $text = preg_replace('/\b0+(?=\.)/', '', $text);
    $text = preg_replace('/ +/', ' ', $text);

    return $text;
  }


  /**
   * Solve URLs
   *
   * @param  string  Path
   * @return string
   */
  final public static function path($path) {
    if ( ! is_url($path)) {
      $root = static::$defs['path'];

      $path = str_replace(array('\\', '/'), DS, $path);
      $path = preg_replace(sprintf('/^\.%s/', preg_quote(DS, '/')), $root, $path);

      while (substr($path, 0, 3) === '..'.DS) {
        $path = substr($path, 2);
        $root = dirname($root);
      }
      return $root.DS.ltrim($path, DS);
    }
    return $path;
  }



  /**#@+
   * @ignore
   */

  // load file
  final private static function load_file($path, $parse = FALSE) {
    if ( ! is_file($path)) {
      $path = static::path($path);

      if (is_false(strrpos(basename($path), '.'))) {
        $path .= static::$defs['extension'];
      }
    }


    if ( ! is_file($path)) {
      raise(ln('file_not_exists', array('name' => $path)));
    }

    $text = read($path);

    if (is_true($parse)) {
      static::parse_buffer($text);
    }
    return $text;
  }

  // internal file append
  final private static function add_file($path, $parse = FALSE) {
    $text = static::load_file($path, $parse);

    if (is_false($parse)) {
      static::$css []= $text;
    }
  }

  // parse external rules
  final private static function fetch_externals($match) {
    switch ($match[1]) {
      case 'require';
        $inc_file  = static::$defs['path'].DS.$match[3];
        $inc_file .= static::$defs['extension'];

        if ( ! is_file($inc_file)) {
          raise(ln('file_not_exists', array('name' => $inc_file)));
        }

        static::add_file($inc_file, TRUE);
      break;
      case 'use';
        if (in_array($match[3], static::$imports)) {
          break;
        }

        static::$imports []= $match[3];

        $css_file = __DIR__.DS.'assets'.DS.'styles'.DS."$match[3].css";

        if ( ! is_file($css_file)) {
          raise(ln('file_not_exists', array('name' => $css_file)));
        }

        static::add_file($css_file, TRUE);
      break;
      default;
        return static::load_file($match[3], FALSE);
      break;
    }
  }

  // set properties callback
  final private static function fetch_properties($match) {
    static::$props[$match[1]] = $match[2];
  }

  // fetch blocks callback
  final private static function fetch_blocks($match) {
    $test = explode('<', $match[1]);
    $part = trim(array_shift($test));

    if (substr($part, 0, 6) === '@mixin') {
      $args   = array();
      $params = trim(substr($part, 7));

      if ($offset = strpos($params, '(')) {
        $parts = substr($params, $offset);
        $name  = substr($params, 0, $offset);

        foreach (explode(',', substr($parts, 1, -1)) as $val) {
          if ( ! empty($val)) {
            @list($key, $val) = explode(':', $val);
            $args[substr($key, 1)] = trim($val);
          }
        }
      } else {
        $name = end(explode(' ', trim($match[1])));
      }

      static::$mixins[$name]['props'] = static::parse_properties($match[2]);
      static::$mixins[$name]['args']  = $args;
    } else {
      $props  = static::parse_properties($match[2]);
      $parent = array_map('trim', array_filter($test));

      static::$sets[$part] = array();

      if ( ! empty($parent)) {
        foreach (array_keys(static::$sets) as $key) {
          if (in_array($key, $parent)) {
            static::$sets[$part] += static::$sets[$key];
          }
        }
      }

      static::$sets[$part] += static::parse_properties($match[2]);
    }
  }

  // parse entire buffer
  final private static function parse_buffer($text) {
    $text = preg_replace('/\/\*(.+?)\*\//s', '', $text);
    $text = preg_replace('/^(?:\/\/|;).+?$/m', '', $text);
    $text = preg_replace('/&(#?\w+);?/', '__ENTITY\\1__', $text);
    $text = preg_replace(array_keys(static::$fixate_css_expr), static::$fixate_css_expr, $text);
    $text = preg_replace_callback('/@(import|require|use)\s+([\'"]?)([^;\s]+)\\2;?/s', 'static::fetch_externals', $text);
    $text = preg_replace_callback('/^\s*\$([a-z][$\w\d-]*)\s*=\s*(.+?)\s*;?\s*$/mi', 'static::fetch_properties', $text);

    $depth  = 0;
    $buffer = '';
    $length = strlen($text);
    $hash   = uniqid('--block-mark');
    $regex  = "/([^\r\n;\{\}]+)\{\[{$hash}#(.*?)#{$hash}\]\}/is";

    for ($i = 0; $i < $length; $i += 1) {
      switch ($char = substr($text, $i, 1)) {
        case '{';
          $buffer .= ++$depth === 1 ? "{[{$hash}#" : $char;
        break;
        case '}';
          $buffer .= --$depth <= 0 ? "#{$hash}]}" : $char;
        break;
        default;
          $buffer .= $char;
        break;
      }
    }

    preg_replace_callback($regex, 'static::fetch_blocks', $buffer);
  }

  // hackish properties parsing
  final private static function parse_properties($text) {
    $out  = array();

    $text = str_replace("'", "\'", $text);
    $text = preg_replace(array_keys(static::$fixate_array_expr), static::$fixate_array_expr, $text);

    @eval("\$out = array($text);");

    return $out;
  }

  // build css properties
  final private static function build_properties($set, $parent = '') {
    foreach ($set as $key => $val) {
      $key = preg_replace('/!\d*$/', '', $key);

      if (is_array($val)) {//FIX
        static::build_properties($val, trim("$parent $key"));
      } else {
        switch($key) {
          case '@extend';
            foreach (array_filter(explode(',', $val)) as $part) {
              if ( ! empty(static::$sets[$part])) {
                static::$sets[$part]['@children'] = $parent;
              }
            }
          break;
          case '@include';
            $mix = static::do_mixin($val);

            static::build_properties($mix, $parent);

            $old = isset(static::$sets[$parent]) ? static::$sets[$parent] : array();

            static::$sets[$parent] = array_merge($old, $mix);
          break;
          default;
          break;
        }
      }
    }
  }

  // build css rules
  final private static function build_rules($set, $parent = '') {
    $out = array();

    foreach ($set as $key => $val) {
      $key = preg_replace('/!\d*(:|)$/', '\\1', $key);

      if (substr($key, -1) === ':') {
        $out []= static::make_properties($val, $key);
      } elseif (is_array($val)) {
        if (substr($parent, 0, 1) === '@') {
          $out []= static::build_rules($val, $key);
        } elseif ($tmp = static::build_rules($val, "$parent $key")) {
          static::$css []= $tmp;
        }
      } elseif (substr($key, 0, 1) <> '@') {
        $out []= "  $key: $val;";
      }
    }


    if ( ! empty($out)) {

      if ( ! empty($set['@children'])) {
        $parent .= ',' . join(',', $set['@children']);
      }

      $rules = static::do_solve(join("\n", $out));
      $parts = preg_split('/\s*,+\s*/', $parent);

      $top  = '';
      $rule = array();


      foreach ($parts as $one) {
        $pos = strpos($one, '&');

        if ( ! is_false($pos)) {
          if ($pos > 0) {
            $top = trim(substr($one, 0, $pos));
          }
          $rule []= $top . substr($one, $pos + 1);
        } else {
          $rule []= trim($one);
        }
      }

      $top = join(', ', $rule);
      $out  = "$top {\n$rules\n}";

      return $out;
    }
  }

  // build raw-deep properties
  final private static function make_properties($test, $old = '') {
    if ( ! is_array($test)) {
      return "  $old $test;";
    }


    $out = array();

    foreach ($test as $key => $val) {
      $val = trim($val);
      $key = preg_replace('/!\d*(:|)$/', '\\1', $key);

      if ( ! is_array($val)) {
        $out []= str_replace(':', '-', "  $old$key: $val;");
      } else {
        $out []= static::make_properties($val, $old . $key);
      }
    }

    $out = join("\n", $out);
    $out = str_replace('- ', ': ', $out);

    return $out;
  }

  // replace variables
  final private static function do_vars($test, $set) {
    static $repl = 'isset($set["\\1"])?$set["\\1"]:NULL;';


    if (is_array($test)) {
      foreach ($test as $key => $val) {
        $test[$key] = static::do_vars($val, $set);
      }
      return $test;
    }

    $test = preg_replace('/%\{(\$[a-z_]\w*)\}/ei', $repl, $test);
    $test = preg_replace('/\$([a-z_]\w*)!?/ei', $repl, $test);

    return $test;
  }

  // compile mixin properties
  final private static function do_mixin($text) {
    $out  = array();
    $text = static::do_solve($text);

    if (preg_match_all('/\s*([\w\-]+)!?(?:\((.+?)\))?\s*/', $text, $matches)) {
      foreach ($matches[1] as $i => $part) {
        if (array_key_exists($part, static::$mixins)) {
          $old = static::$mixins[$part]['args'];

          if ( ! empty($matches[2][$i])) {
            $new = array_filter(explode(',', $matches[2][$i]), 'strlen');
            $new = array_values($new) + array_values($old);

            if (sizeof($old) === sizeof($new)) {//FIX
              $old = array_combine(array_keys($old), $new);
            }
          }

          foreach ($old as $key => $val) {
            $old[substr($key, 1)] = trim(preg_match('/^\s*([\'"])(.+?)\\1\s*$/', $val, $match) ? $match[2] : $val);
          }


          $old = array_merge(static::$props, $old);
          $out = static::do_vars(static::$mixins[$part]['props'], $old);
        }
      }
    }
    return $out;
  }

  // solve css expressions
  final private static function do_solve($text) {
    static $set = NULL;


    if (is_null($set)) {
      $test = include __DIR__.DS.'assets'.DS.'scripts'.DS.'named_colors.php';

      foreach ($test as $key => $val) {
        $key = sprintf('/#%s\b/', preg_quote($key, '/'));
        $set[$key] = $val;
      }
    }


    if (is_array($text)) {
      foreach ($text as $key => $val) {
        $text[$key] = static::do_solve($val);
      }
    } else {//FIX
      do
      {
        $old  = strlen($text);

        $text = preg_replace_callback('/(?<![\-._])(\w[\w-]+?|[%#]\w*?)\(([^\(\)]+)\)/', 'static::do_helper', $text);
        $text = static::do_math(static::do_vars($text, static::$props));
        $text = preg_replace(array_keys($set), $set, $text);

      } while($old != strlen($text));
    }

    return $text;
  }

  // css helper callback
  final private static function do_helper($match) {
    $args = static::do_solve($match[2]);
    $args = array_map('trim', explode(',', $args));

    if ( ! cssp_helper::defined($match[1])) {
      return "$match[1]!($match[2])" . ( ! empty($match[3]) ? $match[3] : '');
    }

    $out = cssp_helper::apply($match[1], $args);
    $out = ! empty($match[3]) ? value($out, substr($match[3], 1)) : $out;

    return $out;
  }

  // solve math operations
  final private static function do_math($text) {
    static $regex = '/(-?(?:\d*\.)?\d+)(p[xtc]|e[xm]|[cm]m|g?rad|deg|in|s|%)/';


    if (is_false(strpos($text, '['))) {
      return $text;
    }

    while (preg_match_all('/\[([^\[\]]+?)\]/', $text, $matches)) {
      foreach ($matches[0] as $i => $val) {
        preg_match($regex, $matches[1][$i], $unit);

        $ext  = ! empty($unit[2]) ? $unit[2] : 'px';
        $expr = preg_replace($regex, '\\1', $matches[1][$i]);


        if (strpos($val, '#') !== FALSE) {
          $out  = preg_replace('/#(\w+)(?=\b|$)/e', '"0x".static::hex("\\1");', $expr);
          $out  = preg_replace('/[^\dxa-fA-F\s*\/.+-]/', '', $expr);
          $expr = "sprintf('#%06x', $out)";
          $ext  = '';
        }

        @eval("\$out = $expr;");

        $out   = isset($out) ? $out : '';
        $out  .= is_numeric($out) ? $ext : '';
        $text  = str_replace($val, $out, $text);
      }
    }

    return $text;
  }

  /**#@-*/
}

/* EOF: ./library/cssp/cssp.php */
