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
cssp_helper::implement('min', function () {
  return min(func_get_args());
});


/**
 * Max value
 *
 * @param  mixed  Number|...
 * @return integer
 */
cssp_helper::implement('max', function () {
  return max(func_get_args());
});


/**
 * Average value
 *
 * @param  mixed  Number|...
 * @return integer
 */
cssp_helper::implement('avg', function () {
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
cssp_helper::implement('ceil', function ($num) {
  return ceil($num);
});


/**
 * Next lower value
 *
 * @param  mixed  Number
 * @return integer
 */
cssp_helper::implement('floor', function ($num) {
  return floor($num);
});


/**
 * Rounds a float
 *
 * @param  mixed Number
 * @return float
 */
cssp_helper::implement('round', function ($num) {
  $args = func_get_args();
  return call_user_func_array('round', $args);
});


/**
 * Absolute value
 *
 * @param  mixed   Number
 * @return integer
 */
cssp_helper::implement('abs', function ($num) {
  return abs($num);
});


/**
 * Percentage to floatval
 *
 * @param  mixed   Number
 * @return integer
 */
cssp_helper::implement('fval', function ($num) {
  return (float) (strpos($num, '%') ? (int) $num / 100 : $num);
});

/**
 * Floatval to percentage
 *
 * @param  mixed   Number
 * @return integer
 */
cssp_helper::implement('perc', function ($num) {
  return sprintf('%d%%', (float) $num > 1 ? (int) $num : $num * 100);
});

/* EOF: ./library/cssp/helpers/number.php */
