<?php

/**
 * Date functions
 */

class date extends prototype
{

  /**
   * Translations
   *
   * @param  string ...
   * @return string
   */
  final public static function ln() {
    $args = func_get_args();
    return call_user_func_array('ln', $args);
  }


  /**
   * GMT datetime format
   *
   * @param  integer Timestamp
   * @return string
   */
  final public static function gmt($of) {
    return date('D, d M Y H:i:s \G\M\T', $of);
  }


  /**
   * MySQL inspired datetime format
   *
   * @param     string  Simple format
   * @param     integer Timestamp
   * @staticvar string  Allowed formats regex
   * @return    string
   */
  final public static function format($with, $of = 0) {
    static $expr = '/(?<!%)%([dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU])/';


    $with = preg_replace_callback($expr, function ($match)
      use($of) {
      $test = date($match[1], $of ? (is_timestamp($of) ? strtotime($of) : (int) $of) : time::now());
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
  final public static function simple($to, $from = 0) {
    static $set = array(
              '/\bDDDD\b/' => '%l',
              '/\bDDD\b/' => '%D',
              '/\bDD\b/' => '%d',
              '/\bMMMM\b/' => '%F',
              '/\bMMM\b/' => '%M',
              '/\bMM\b/' => '%m',
              '/\bYYYY\b/' => '%Y',
              '/\bYY\b/' => '%y',
              '/\bHH\b/' => '%H',
              '/\bhh\b/' => '%g',
              '/\bii\b/' => '%i',
              '/\bss\b/' => '%s',
            );

    $to = preg_replace(array_keys($set), $set, $to);
    $to = static::format($to, $from);

    return $to;
  }


  /**
   * Distance between two dates
   *
   * @param  integer Timestamp
   * @param  integer Timestamp
   * @param  string  Default format
   * @return string
   */
  final public static function distance($since, $to = 0, $or = '%F %Y') {
    if (is_timestamp($since)) {
      $since = strtotime($since);
    }

    if ($to <= 0) {
      $to = time();
    }


    $diff = $to - $since;

    if (($diff >= 0) && ($diff <= 4)) {
      return ln('date.now');
    } elseif ($diff > 0) {
      $day_diff = floor($diff / 86400);

      if ($day_diff == 0) {
        if ($diff < 120) {
          return ln('date.less_than_minute');
        }

        if ($diff < 3600) {
          return sprintf(ln('date.minutes_ago'), floor($diff / 60));
        }

        if ($diff < 7200) {
          return ln('date.hour_ago');
        }

        if ($diff < 86400) {
          return sprintf(ln('date.hours_ago'), floor($diff / 3600));
        }
      }

      if ($day_diff == 1) {
        return ln('date.yesterday');
      }

      if ($day_diff < 7) {
        return sprintf(ln('date.days_ago'), $day_diff);
      }

      if ($day_diff < 31) {
        return sprintf(ln('date.weeks_ago'), ceil($day_diff / 7));
      }

      if ($day_diff < 60) {
        return ln('date.last_month');
      }

      return static::format($or, $since);
    } else {
      $diff     = abs($diff);
      $day_diff = floor($diff / 86400);

      if ($day_diff == 0) {
        if ($diff < 120) {
          return ln('date.in_a_minute');
        }

        if ($diff < 3600) {
          return sprintf(ln('date.in_minutes'), floor($diff / 60));
        }

        if ($diff < 7200) {
          return ln('date.in_a_hour');
        }

        if ($diff < 86400) {
          return sprintf(ln('date.in_hours'), floor($diff / 3600));
        }
      }

      if ($day_diff == 1) {
        return ln('date.tomorrow');
      }

      if ($day_diff < 4) {
        return static::format('%l', $since);
      }

      if ($day_diff < (7 + (7 - date('w')))) {
        return ln('date.next_week');
      }

      if (ceil($day_diff / 7) < 4) {
        return sprintf(ln('date.in_weeks'), ceil($day_diff / 7));
      }

      if ((date('n', $since) == (date('n') + 1)) && (date('y', $since) == date('Y'))) {
        return ln('date.next_month');
      }

      return static::format($or, $since);
    }
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
  final public static function duration($secs, $used = 'hms', $zero = FALSE) {
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

    foreach ($period as $key => $value) {
      if ( ! empty($used) && is_false(strpos($used, substr($key, 0, 1)))) {
        continue;
      }

      $count = $secs / $value;

      if (floor($count) == 0 && is_false($zero)) {
        continue;
      }

      $secs       %= $value;
      $parts[$key] = abs($count);
    }

    foreach ($parts as $key => $value) {
      $out []= ln((int) $value, 'date.' . strtolower($key));
    }

    return $out;
  }


  /**
   * UTC hours set list
   *
   * @return array
   */
  final public static function utc_list() {
    static $set = NULL;


    if (is_null($set)) {
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
  final public static function timezone_list($key = NULL) {
    static $set = NULL;


    if (is_null($set)) {
      /**
       * @ignore
       */
      $test = include __DIR__.DS.'assets'.DS.'scripts'.DS.'datetime_vars'.EXT;
      $set  = $test['tz'];
    }

    return isset($set[$key]) ? $set[$key] : $set;
  }

}

/* EOF: ./library/date.php */
