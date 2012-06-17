<?php

/**
 * CSS math basic functions
 */

/**
 * Min value
 *
 * @param  mixed  Number|...
 * @return integer
 */
chess_helper::implement('min', function () {
  return min(func_get_args());
});


/**
 * Max value
 *
 * @param  mixed  Number|...
 * @return integer
 */
chess_helper::implement('max', function () {
  return max(func_get_args());
});


/**
 * Average value
 *
 * @param  mixed  Number|...
 * @return integer
 */
chess_helper::implement('avg', function () {
  $args  = func_get_args();
  $total = array_sum($args);

  return $total / sizeof($args);
});


/**
 * Next upper value
 *
 * @param  mixed  Number
 * @return integer
 */
chess_helper::implement('ceil', function ($num) {
  return ceil($num);
});


/**
 * Next lower value
 *
 * @param  mixed  Number
 * @return integer
 */
chess_helper::implement('floor', function ($num) {
  return floor($num);
});


/**
 * Rounds a float
 *
 * @param  mixed Number
 * @return float
 */
chess_helper::implement('round', function ($num) {
  $args = func_get_args();
  return call_user_func_array('round', $args);
});


/**
 * Absolute value
 *
 * @param  mixed   Number
 * @return integer
 */
chess_helper::implement('abs', function ($num) {
  return abs($num);
});


/**
 * Percentage to floatval
 *
 * @param  mixed   Number
 * @return integer
 */
chess_helper::implement('fval', function ($num) {
  return (float) (strpos($num, '%') ? (int) $num / 100 : $num);
});


/**
 * Floatval to percentage
 *
 * @param  mixed   Number
 * @return integer
 */
chess_helper::implement('perc', function ($num) {
  return sprintf('%d%%', (float) $num > 1 ? (int) $num : $num * 100);
});


/**
 * Fake calc() helper
 *
 * @param     mixed  Expression
 * @staticvar mixed  Function callback
 * @staticvar string Allowed css-math units
 * @return    mixed
 */
chess_helper::implement('calc', function ($expr) {
  static $solve = NULL,
         $regex = '/(-?(?:\d*\.)?\d+)(p[xtc]|e[xm]|[cm]m|g?rad|deg|in|s|%)/';;


  if ( ! $solve) {
    $solve = function ($input)
      use($regex) {
      if (strpos($input, '#') !== FALSE) {
        $out = preg_replace('/[^#\dxa-fA-F\s*\/%+-]/', '', $input);
        $out = str_replace('#', '0x', $out);
        $out = "sprintf('#%06x', $out)";

        @eval("\$out=$out;");
      } else {// TODO: when css3 calc() arrives?
        preg_match($regex, $input, $unit);

        $ext = ! empty($unit[2]) ? $unit[2] : 'px';
        $out = preg_replace($regex, '\\1', $input);

        @eval("\$out=($out).'$ext';");
      }
      return $out;
    };
  }


  while (preg_match_all('/\[([^[\]]+?)\]/', $expr, $matches)) {
    foreach ($matches[0] as $i => $val) {
      $expr = str_replace($val, $solve($matches[1][$i]), $expr);
    }
  }
  return $solve($expr);
});

/* EOF: ./library/chess/helpers/number.php */
