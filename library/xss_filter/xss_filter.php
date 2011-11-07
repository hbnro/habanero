<?php

/**
 * Basic XSS security filter
 */

class xss_filter extends prototype
{

  /**#@+
   * @ignore
   */

  // empty values
  private static $null = array(
                    '/%0[0-8bcef]/',
                    '/%(?:(?:11|12)|1[0-9a-f])/',
                    '/%(?:1[4-9]|%2[0-9]|%3[0-1])/',
                    '/[\x0e-\x19]|[\x0e-\x1f]/',
                    '/[\x00-\x08]|\x0b|\x0c/',
                  );

  // fullspec tag match
  private static $tags = '/<([a-zA-Z0-9:-]+)(\s*[^>]*)\/?>/s';

  // computed expressions
  private static $regex = array();

  // defaults
  protected static $defs = array();

  /**#@-*/



  /**
   * Apply filters
   *
   * @param  string  Hypertext
   * @param  boolean Strip tags?
   * @return string
   */
  final public static function clean($text, $strip = FALSE) {
    static::init();


    $text = self::fix_white(urldecode($text));

    if (is_true($strip)) {
      $text = strip_tags($text, sprintf('<%s>', join('><', static::$defs['allow']['tags'])));
      $text = preg_replace(static::$regex['clear_tags'], '', $text);
      $text = preg_replace(static::$regex['clean_tags'], '', $text);
    }


    $text = preg_replace_callback(static::$tags, array('xss', 'fix_attributes'), $text);

    if (preg_match_all('/<[^>]+>/', $text, $matches)) {
      $hash = uniqid('--place-holder');
      $text = htmlspecialchars(str_replace($matches[0], $hash, $text));

      foreach ($matches[0] as $val) {
        $offset = strpos($text, $hash);
        $length = $offset + strlen($hash);
        $text   = substr($text, 0, $offset) . $val . substr($text, $length);
      }
    }

    return $text;
  }



  /**#@+
   * @ignore
   */

  // startup
  final private static function init() {
    if (is_empty(static::$defs)) {
      static::$defs = include __DIR__.DS.'assets'.DS.'scripts'.DS.'clean_vars'.EXT;


      if ( ! empty(static::$defs['remove']['content'])) {
        foreach (static::$defs['remove']['content'] as $key) {
          static::$regex['clear_tags'] []= "/<[\s\n]*$key.*<[\s\n]*\/{$key}[\s\n]*>/is";
        }
      }

      if  ( !empty(static::$defs['remove']['tags'])) {
        foreach (static::$defs['remove']['tags'] as $key) {
          static::$regex['clean_tags'] []= "/<\/?[\s\n]*{$key}[^>]*>/i";
        }
      }

      if ( ! empty(static::$defs['remove']['css'])) {
        foreach (static::$defs['remove']['css'] as $key) {
          $key = static::fix_space($key);

          static::$regex['clean_css'] []= "/;?$key:[^;]*;?/i";
        }
      }
    }
  }

  // fixate white space
  final private static function fix_white($text) {
    return preg_replace(static::$null, '', $text);
  }

  // fixate spaced text
  final private static function fix_space($text) {
    $out = '[\s\x01-\x1F]*';
    $len = strlen($text);

    for ($i = 0; $i < $len; $i += 1) {
      $out .= substr($text, $i, 1);
      $out .= '[\s\x01-\x1F]*';
    }

    return str_replace('/', '\/', $out);
  }

  // fixate mixed entities
  final private static function fix_entities($text) {
    $hash = uniqid('--entity-fix');

    $text = preg_replace('/&([a-z_0-9;]+)=([a-z_0-9]+)/i', "$hash\\1=\\2", $text);
    $text = rawurldecode(str_replace($hash, '&', ents($text, FALSE)));

    return $text;
  }

  // attributes cleanup callback
  final private static function fix_attributes($match) {
    $tag  = strtolower($match[1]);
    $text = $match[2];

    if ( ! in_array($tag, static::$defs['allow']['tags'])) {
      return "[$tag]";
    }


    $out  = array();
    $test = args($text);

    foreach ($test as $key => $val) {
      if (in_array($key, static::$defs['allow']['attributes'])) {
        $val = static::fix_white(stripslashes($val));
        $val = static::fix_entities($val);

        if ($key == 'style') {
          do
          {
            $old = $val;
            $val = preg_replace('/\/\*.*?\*\//s', '', $val);
          } while ($old <> $val);

          $val = preg_replace(static::$regex['clean_css'], '', $val);
          $val = preg_replace(sprintf('/;?(?:[a-z]*:?)?%s(?::?\(?[^;]*\)?)?;?/i', static::fix_space('expression')), '', $val);
        } elseif (($key == 'href' OR $key == 'src') && preg_match('/^([^:]*):/', $val, $test)) {
          if ( ! in_array($test[1], static::$defs['allow']['protocols'])) {
            continue;
          }
        }

        $out[$key] = ents($val, TRUE);
      }
    }

    return preg_replace('/<\/\w+>/', '', tag($tag, $out));
  }

  /**#@-*/
}

/* EOF: ./library/xss_filter/xss_filter.php */
