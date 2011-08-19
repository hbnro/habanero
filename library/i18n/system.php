<?php

/**
 * I18N translation library
 */

class i18n extends prototype
{

  /**
   * Plural expression translation
   *
   * @param  integer Numeric value
   * @param  string  Input string
   * @param  array   Options hash
   * @return string
   */
  final public static function pluralize($number, $string, array $params = array())
  {
    $decimal   = 0;// TODO: localize this?
    $separator = '.';
    $thousands = ' ';
    
    $string  = sprintf('%s.%s', $string, $number <> 1 ? 'other' : 'one');
    $string  = str_replace('%d', number_format($number, $decimal, $separator, $thousands), translate($string, $params));
    
    return $string;
  }
  
  
  /**
   * Retrieve the specified translation
   *
   * @param  mixed  Input string
   * @param  mixed  Default value
   * @param  array  Options hash
   * @return string
   */
  final public static function translate($string, $default = '', array $params = array())
  {
    static $defs = array(
              'scope'   => '',
              'string'  => '',
              'default' => '',
            );
    
    
    if (is_assoc($string))
    {
      $params += $string;
    }
    elseif ( ! isset($params['string']))
    {
      $params['string'] = $string;
    }
    
    if (is_assoc($default))
    {
      $params += $default;
    }
    elseif ( ! isset($params['default']))
    {
      $params['default'] = (string) $default;
    }
    
    
    
    $params += $defs;
    
    $params['default'] = (array) $params['default'];
    
    if (is_array($params['default']))
    {
      foreach ($params['default'] as $one)
      {
        if ( ! preg_match('/^[a-z][a-z0-9_]+$/', $one))
        {
          $params['default'] = $one;
          break;
        }
        else
        {
          $test = i18n::translate($one, array('scope' => $params['scope']));
        
          if ( ! empty($test))
          {
            $params['default'] = $test;
            break;
          }
        }
      }
    }
   
    $from    = i18n::load_locale();
    
    $prefix  = $params['scope'] ? "$params[scope]." : '';
    $default = $params['default'] ?: $params['string'];
    
    $string  = value($from, "$prefix$params[string]", $default);
  
    $string  = preg_replace_callback('/%\{(.+?)\}/', function($match)
      use($params)
    {
      return isset($params[$match[1]]) ? $params[$match[1]] : $match[1];
    }, $string);
    
    return $string;
  }
  
  
  /**
   * Import translations directory
   *
   * @param  mixed  Path|Array
   * @param  string Specific index
   * @return void
   */
  final public static function load_path($from, $scope = '')
  {
    if (is_array($from))
    {
      return array_map(__FUNCTION__, $from);
    }
    
    
    $dir = str_replace(LIB, '.', $from);
    $set = (array) option('locale_path', array());
    
    
    if ( ! is_dir($from) OR in_array($dir, $set))
    {
      return FALSE;
    }
    
    
    $set []= $dir;
    
    config('locale_path', $set);
    
    
    $path = realpath($from);
    $test = preg_split('/[^a-zA-Z]/', LANG);
    
    foreach (array(
      '.mo' => 'gettext',
      '.php' => 'array',
      '.csv' => 'csv',
      '.ini' => 'ini',
    ) as $ext => $type)
    {
      $callback = 'i18n::load_' . $type;
      
      foreach (array(
        $path.DS.join('_', $test).$ext,
        $path.DS.$test[0].$ext,
      ) as $one)
      {
        if (is_file($one))
        {
          $lang = call_user_func($callback, $one);
          i18n::load_locale($lang, $scope);
          break;
        }
      }
    }
  }
  
  
  /**
   * Import and retrieve translation values
   *
   * @param  array  Translation array
   * @param  string Specific index
   * @return array
   */
  final public static function load_locale(array $set = array(), $scope = '')
  {
    static $tree = array();
    
    
    if ( ! empty($set))
    {
      if ( ! empty($scope))
      {
        $set = array($scope => (array) $set);
      }
      $tree += (array) $set;
    }
    return $tree;
  }
  
  
  /**
   * Import MO translations file
   *
   * @param     string Path
   * @staticvar mixed  Bit callback
   * @return    mixed
   */
  final public static function load_gettext($from)
  {
    static $byte = NULL;
  
    
    if (is_null($byte))
    {
      $byte = function($length, $endian, &$resource)
      {
        return unpack(($endian ? 'N' : 'V') . $length, fread($resource, 4 * $length));
      };
    }
  
    
    if ( ! is_file($from))
    {
      return FALSE;
    }
    
    
    $out      = array();
    $resource = fopen($from, 'rb');
  
    $test   = $byte(1, $endian, $resource);
    $part   = strtolower(substr(dechex($test[1]), -8));
    $endian = '950412de' === $part ? FALSE : ('de120495' === $part ? TRUE : NULL);
  
    $test = $byte(1, $endian, $resource);// revision
    $test = $byte(1, $endian, $resource);// bytes
    $all  = $test[1];
  
    // offsets
    $test = $byte(1, $endian, $resource);
    $omax = $test[1];// original
  
    $test = $byte(1, $endian, $resource);
    $tmax = $test[1];// translate
  
    // tables
    fseek($resource, $omax);// original
    $otmp = $byte(2 *$all, $endian, $resource);
  
    fseek($resource, $tmax);// translate
    $ttmp = $byte(2 *$all, $endian, $resource);
  
    for ($i = 0; $i < $all; $i += 1)
    {
      $orig = -1;
      
      if ($otmp[$i * 2 + 1] <> 0)
      {
        fseek($resource, $otmp[$i * 2 + 2]);
        $orig = fread($resource, $otmp[$i * 2 + 1]);
      }
      
      if ($ttmp[$i * 2 + 1] <> 0)
      {
        fseek($resource, $ttmp[$i * 2 + 2]);
        $out[$orig] = fread($resource, $ttmp[$i * 2 + 1]);
      }
    }
    
    fclose($resource);
    unset($out[-1]);
    
    return $out;
  }
  
  
  /**
   * Import PHP translations array file
   *
   * @param  string Path
   * @return mixed
   */
  final public static function load_array($from)
  {
    if ( ! is_file($from))
    {
      return FALSE;
    }
    
    
    ob_start();
    $out = include $from;
    ob_end_clean();
    
    if ( ! empty($lang))
    {
      $out = $lang;
    }
    
    return (array) $out;
  }
  
  
  /**
   * Import CSV translations file
   *
   * @param  string Path
   * @param  string Character separator
   * @return mixed
   */
  final public static function load_csv($from, $split = ';')
  {
    if ( ! is_file($from))
    {
      return FALSE;
    }
    
    
    $out      = array();
    $resource = fopen($from, 'rb');
    
    fseek($resource, 0);
    
    while (FALSE !== ($old = fgetcsv($resource, 0, $split, '"')))
    {
      if ((substr($old[0], 0, 1) == '#') OR empty($old[1]))
      {
        continue;
      }
      
      $out[trim($old[0])] = $old[1];
    }
    
    fclose($resource);
    
    return $out;
  }
  
  
  /**
   * Import INI translations file
   *
   * @param  string path
   * @return mixed
   */
  final public static function load_ini($from)
  {
    if ( ! is_file($from))
    {
      return FALSE;
    }
    
    
    $out = parse_ini_file($from, FALSE);
    
    return $out;
  }
  
}

/* EOF: ./i18n/system.php */