<?php

/**
 * Time functions
 */

class time extends prototype
{

  /**
   * Seconds to human readable
   *
   * @param  integer Time
   * @param  string  Alternative format
   * @param  string  Character separator
   * @return string
   */
  final public static function secs($from, $or = 'YMWD', $glue = ' ') {
    if ($from >= 86400) {
      return join($glue, date::duration($from, $or));
    }

    $hours = floor($from / 3600);
    $mins  = floor($from % 3600 /60);
    $out   = sprintf('%d:%02d:%02d', $hours, $mins, $from % 60);
    $out   = preg_replace('/^0+:/', '', $out);

    return $out;
  }


  /**
   * GMT timestamp
   *
   * @param  integer Timestamp
   * @return integer
   */
  final public static function gmt($from = 0) {
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
  final public static function now() {
    return strtoupper(option('timezone')) == 'GMT' ? gmtime() : time();
  }


  /**
   * Days in a month
   *
   * @param     integer Month
   * @param     integer Year
   * @staticvar array   Days
   * @return    integer
   */
  final public static function days($month, $from = 1970) {
    static $num = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);


    if (($month < 1) OR ($month > 12)) {
      return FALSE;
    } elseif ( ! is_numeric($year) OR (strlen($year) <> 4)) {
      $year = date('Y');
    }

    if ($month == 2) {
      if ((($year % 400) == 0) OR ((($year % 4) == 0) AND (($year % 100) <> 0))) {
        return 29;
      }
    }

    return $num[$month - 1];
  }

}

/* EOF: ./library/time.php */
