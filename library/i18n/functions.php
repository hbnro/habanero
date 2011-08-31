<?php

/**
 * Translation core functions
 */

/**
 * Dynamic shortcode alias
 *
 * @param  mixed Input string|...
 * @return mixed
 */
function ln($input)
{
  $args  = func_get_args();

  if (is_array($input))
  {
    foreach ($input as $key => $value)
    {
      $args[0]     = $value;
      $input[$key] = apply(__FUNCTION__, $args);
    }
  }
  else
  {
    $callback = is_num($input) ? 'pluralize' : 'translate';
    $input    = apply("i18n::$callback", $args);
  }

  return $input;
}

/* EOF: ./lib/i18n/functions.php */
