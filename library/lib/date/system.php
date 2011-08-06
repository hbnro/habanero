<?php

/**
 * Datetime functions script
 */

/**
 * Seconds to human readable
 *
 * @param  integer Time
 * @param  string  Alternative format
 * @param  string  Character separator
 * @return string
 */
function secs($from, $or = 'YMWD', $glue = ' ')
{
  if ($from >= 86400)
  {
    return join($glue, duration($from, $or));
  }
  
  $hours = floor($from / 3600);
  $mins  = floor($from % 3600 /60);
  $out   = sprintf('%d:%02d:%02d', $hours, $mins, $from % 60);
  $out   = preg_replace('/^0+:/', '', $out);
  
  return $out;
}


/**
 * GMT datetime format
 *
 * @param  integer Timestamp
 * @return string
 */
function gmt($of)
{
  return date('D, d M Y H:i:s \G\M\T', $of);
}


/**
 * GMT timestamp
 *
 * @param  integer Timestamp
 * @return integer
 */
function gmtime($from = 0)
{
  $from = $from > 0 ? $from : time();

  $out  = gmdate('D M ', $from);
  $out .= sprintf('%2d ', (int) gmdate('d', $from));
  $out .= gmdate('H:i:s Y', $from);

  return strtotime($out);
}


/**
 * GMT alias for timestamp
 *
 * @return integer
 */
function now()
{
  return strtoupper(option('timezone')) == 'GMT' ? gmtime() : time();
}


/**
 * MySQL inspired datetime format
 *
 * @param     string  Simple format
 * @param     integer Timestamp
 * @staticvar string  Allowed formats regex
 * @return    string
 */
function mdate($with, $of = 0)
{
  static $expr = '/(?<!%)%([dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU])/';
  
  
  $with = preg_replace_callback($expr, function($match)
    use($of)
  {
    $test = date($match[1], $of > 0 ? $of : now());
    $test = is_num($test) ? $test : ln('date.' . strtolower($test));
    
    return $test;
  }, $with);
  
  return $with;
}


/**
 * Simple timestamp format
 *
 * @param     string  Date format
 * @param     integer Timestamp
 * @staticvar array   Formats
 * @return    string
 */
function strdate($to, $from = 0)
{
  static $set = array(
            '/\bDDDD\b/' => '%l',
            '/\bDDD\b/' => '%D',
            '/\bDD\b/' => '%d',
            '/\bD\b/' => '%j',
            '/\bMMMM\b/' => '%F',
            '/\bMMM\b/' => '%M',
            '/\bMM\b/' => '%m',
            '/\bM\b/' => '%n',
            '/\bYYYY\b/' => '%Y',
            '/\bYY\b/' => '%y',
            '/\bHH\b/' => '%H',
            '/\bhh\b/' => '%g',
            '/\bii\b/' => '%i',
            '/\bss\b/' => '%s',
          );
  
  $to = preg_replace(array_keys($set), $set, $to);
  $to = mdate($to, $from);
  
  return $to;
}


/**
 * Change timestamp/datetime format
 *
 * @param  mixed  Datetime|Timestamp
 * @param  string DATE_ATOM|DATE_COOKIE|DATE_ISO8601|DATE_RFC822
 *                        DATE_RFC850|DATE_RFC1036|DATE_RFC1123
 *                        DATE_RFC2822|DATE_RSS|DATE_W3C
 * @return string
 */
function fmtdate($time, $format = DATE_RFC822)
{
  return date($format, is_timestamp($time) ? strtotime($time) : $time);
}


/**
 * Duration seconds to human readable format
 *
 * @link      http://aidanlister.com/2004/04/making-time-periods-readable/
 * @param     integer Seconds
 * @param     string  Formats to use
 * @param     boolean Include empty values?
 * @staticvar array   Conversion set
 * @return    string
 */
function duration($secs, $used = 'hms', $zero = FALSE)
{
  static $period = array(
            'Years' => 31556926,
            'Months' => 2629743,
            'Weeks' => 604800,
            'Days' => 86400,
            'hours' => 3600,
            'minutes' => 60,
            'seconds' => 1,
          );

  
  $out   =
  $parts = array();
  $secs  = (float) $secs;
  
  foreach ($period as $key => $value)
  {
    if ( ! empty($used) && is_false(strpos($used, substr($key, 0, 1))))
    {
      continue;
    }

    $count = floor($secs / $value);
    
    if ($count == 0 && is_false($zero))
    {
      continue;
    }

    $secs       %= $value;
    $parts[$key] = abs($count);
  }

  foreach ($parts as $key => $value)
  {
    $out []= ln($value, 'date.' . strtolower($key));
  }
  
  return $out;
}


/**
 * Distance between two dates
 *
 * @param  integer Timestamp
 * @param  integer Timestamp
 * @param  string  Default format
 * @return string
 */
function distance($since, $to = 0, $or = '%F %Y')
{
  if (is_timestamp($since))
  {
    $since = strtotime($since);
  }
  
  if ($to <= 0)
  {
    $to = time();
  }

  
  $diff = $to - $since;
  
  if (($diff >= 0) && ($diff <= 2))
  {
    return ln('date.now');
  }
  elseif ($diff > 0)
  {
    $day_diff = floor($diff / 86400);
    
    if ($day_diff == 0)
    {
      if ($diff < 120)
      {
        return ln('date.less_than_minute');
      }
      
      if ($diff < 3600)
      {
        return sprintf(ln('date.minutes_ago'), floor($diff / 60));
      }
      
      if ($diff < 7200)
      {
        return ln('date.hour_ago');
      }
      
      if ($diff < 86400)
      {
        return sprintf(ln('date.hours_ago'), floor($diff / 3600));
      }
    }
    
    if ($day_diff == 1)
    {
      return ln('date.yesterday');
    }
    
    if ($day_diff < 7)
    {
      return sprintf(ln('date.days_ago'), $day_diff);
    }
    
    if ($day_diff < 31)
    {
      return sprintf(ln('date.weeks_ago'), ceil($day_diff / 7));
    }
    
    if ($day_diff < 60)
    {
      return ln('date.last_month');
    }
    
    return mdate($or, $to);
  }
  else
  {
    $diff     = abs($diff);
    $day_diff = floor($diff / 86400);
    
    if ($day_diff == 0)
    {
      if ($diff < 120)
      {
        return ln('date.in_a_minute');
      }
      
      if ($diff < 3600)
      {
        return sprintf(ln('date.in_minutes'), floor($diff / 60));
      }
      
      if ($diff < 7200)
      {
        return ln('date.in_a_hour');
      }
      
      if ($diff < 86400)
      {
        return sprintf(ln('date.in_hours'), floor($diff / 3600));
      }
    }
    
    if ($day_diff == 1)
    {
      return ln('date.tomorrow');
    }
    
    if ($day_diff < 4)
    {
      return mdate('%l', $since);
    }
    
    if ($day_diff < (7 + (7 - date('w'))))
    {
      return ln('date.next_week');
    }
    
    if (ceil($day_diff / 7) < 4)
    {
      return sprintf(ln('date.in_weeks'), ceil($day_diff / 7));
    }
    
    if (date('n', $since) == (date('n') + 1))
    {
      return ln('date.next_month');
    }
    
    return mdate($or, $since);
  }
}


/**
 * Days in a month
 *
 * @param     integer Month
 * @param     integer Year
 * @staticvar array   Days
 * @return    integer
 */
function days($month, $from = 1970)
{
  static $num = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

  
  if (($month < 1) OR ($month > 12))
  {
    return FALSE;
  }
  elseif ( ! is_num($year) OR (strlen($year) <> 4))
  {
    $year = date('Y');
  }

  if ($month == 2)
  {
    if ((($year % 400) == 0) OR ((($year % 4) == 0) AND (($year % 100) <> 0)))
    {
      return 29;
    }
  }
  
  return $num[$month - 1];
}


/**
 * UTC hours set list
 *
 * @return array
 */
function utc_list()
{
  static $set = NULL;
  
  
  if (is_null($set))
  {
    /**
     * @ignore
     */
    $test = include __DIR__.DS.'assets'.DS.'scripts'.DS.'datetime_vars'.EXT;
    $set  = $test['utc'];
  }
  return $set;
}


/**
 * Timezone set list
 *
 * @return array
 */
function timezone_list($key = NULL)
{
  static $set = NULL;
  
  
  if (is_null($set))
  {
    /**
     * @ignore
     */
    $test = include __DIR__.DS.'assets'.DS.'scripts'.DS.'datetime_vars'.EXT;
    $set  = $test['tz'];
  }
  
  return isset($set[$key]) ? $set[$key] : $set;
}

/* EOF: ./lib/date/system.php */