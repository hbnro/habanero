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
css::implement('min', function()
{
  return min(func_get_args());
});


/**
 * Max value
 *
 * @param  mixed  Number|...
 * @return integer
 */
css::implement('max', function()
{
  return max(func_get_args());
});


/**
 * Average value
 *
 * @param  mixed  Number|...
 * @return integer
 */
css::implement('avg', function()
{
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
css::implement('ceil', function($num)
{
  return ceil($num);
});


/**
 * Next lower value
 *
 * @param  mixed  Number
 * @return integer
 */
css::implement('floor', function($num)
{
  return floor($num);
});


/**
 * Rounds a float
 *
 * @param  mixed Number
 * @return float
 */
css::implement('round', function($num)
{
  $args = func_get_args();

  return call_user_func_array('round', $args);
});


/**
 * Absolute value
 *
 * @param  mixed  Number
 * @return integer
 */
css::implement('abs', function($num)
{
  return abs($num);
});

/* EOF: ./library/tetl/css/numbers.php */
