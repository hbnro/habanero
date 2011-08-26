<?php

/**
 * CSS basic color functions
 */

/**
 * Normalize HEX
 *
 * @param  string HEX color
 * @return string
 */
css::implement('hex', function($color)
{
  $color = preg_replace('/[^a-fA-F\d]/', '', $color);

  if (strlen($color) == 3)
  {
    $color = $color[0] . $color[0]
           . $color[1] . $color[1]
           . $color[2] . $color[2];
  }

  return $color;
});


/**
 * HSL value
 *
 * @param  mixed  HEX color|...
 * @return string
 */
css::implement('hsl', function()
{
  return css::rgb2hex(css::hsl2rgb(func_get_args()));
});


/**
 * HSL value + alpha
 *
 * @param  mixed  HEX color|...
 * @return string
 */
css::implement('hsla', function()
{
  return css::rgba(css::hsl2rgb(func_get_args()));
});


/**
 * RGB value
 *
 * @param  mixed  Red|Array|HEX color
 * @param  mixed  Green
 * @param  mixed  Blue
 * @return string
 */
css::implement('rgb', function($red, $green = 0, $blue = 0)
{
  return css::rgba($red, $green, $blue);
});


/**
 * RGB value + alpha
 *
 * @param  mixed  Red|Array|HEX color
 * @param  mixed  Green|Alpha value
 * @param  mixed  Blue
 * @param  mixed  Alpha value
 * @return string
 */
css::implement('rgba', function($red, $green = 0, $blue = 0, $alpha = 100)
{
  $args = func_get_args();

  if (is_array($red))
  {
    $alpha = $green;
    $args  = $red;
  }
  elseif (is_hex($red))
  {
    $args  = css::hex2rgb($red);
    $alpha = $green;
  }


  $alpha = isset($args[3]) ? $args[3] : $alpha;
  $alpha = $alpha > 1 ? ((int) $alpha / 100) : (float) $alpha;
  $args  = css::rgb_safe($args);

  if ((sizeof($args) < 3) OR ($alpha === 0))
  {
    return 'transparent';
  }
  elseif ($alpha < 1)
  {
    return "rgba!($args[0], $args[1], $args[2], $alpha)";
  }
  return "rgb!($args[0], $args[1], $args[2])";
});


/**
 * Darken value
 *
 * @param  string HEX color
 * @param  mixed  Max value
 * @return string
 */
css::implement('darken', function($test, $inc = 50)
{
  return css::mask($test, 100 - ((int) $inc), 0);
});


/**
 * Lighten value
 *
 * @param  string HEX color
 * @param  mixed  Max value
 * @return string
 */
css::implement('lighten', function($test, $inc = 50)
{
  return css::mask($test, 100 - ((int) $inc), 255);
});


/**
 * Saturation value
 *
 * @param  string HEX color
 * @param  mixed  Max value
 * @return string
 */
css::implement('saturate', function($test, $inc = 50)
{
  $hsl    = css::rgb2hsl($test);
  $hsl[1] = min(100, max(0, $hsl[1] + $inc));

  return css::rgb2hex(css::hsl2rgb($hsl));
});


/**
 * Desaturation value
 *
 * @param  string HEX color
 * @param  mixed  Max value
 * @return string
 */
css::implement('desaturate', function($test, $inc = 50)
{
  $hsl    = css::rgb2hsl($test);
  $hsl[1] = min(100, max(0, $hsl[1] - $inc));

  return css::rgb2hex(css::hsl2rgb($hsl));
});


/**
 * Apply light/dark mask
 *
 * @param  string HEX color
 * @param  string Mask value
 * @param  mixed  Change level
 * @return string
 */
css::implement('mask', function($color, $new = 0, $level = 128)
{
  $new /= 100;
  $new  = $new > 1 ? 1 : ($new < 0 ? 0 : $new);
  $rgb  = css::hex2rgb($color);


  for ($i = 0; $i < 3; $i += 1)
  {
    $old = round($rgb[$i] * $new) + round($level * (1 - $new));
    $rgb[$i] = $old > 255 ? 255 : ($old < 0 ? 0 : $old);
  }

  return css::rgb2hex($rgb);
});


/**
 * Color mixins
 *
 * @param  string HEX color
 * @param  string HEX color
 * @param  mixed  Average value
 * @return string
 */
css::implement('merge', function($color, $new = '#fff', $average = 50)
{
  while ((int) $average > 0)
  {
    $average /= 100;
  }

  $old = css::hex2rgb($color);
  $new = css::hex2rgb($new);

  return css::rgb2hex(array(
    (int) (($old[0] - $new[0]) * $average + $new[0]),
    (int) (($old[1] - $new[1]) * $average + $new[1]),
    (int) (($old[2] - $new[2]) * $average + $new[2]),
  ));
});


/**
 * Color combination
 *
 * @param  string HEX color
 * @param  string HEX color
 * @return string
 */
css::implement('combine', function($color, $new = '#fff')
{
  $old = css::hex2rgb($color);
  $new = css::hex2rgb($new);

  return css::rgb2hex(array($old[0] ^ $new[0], $old[1] ^ $new[1], $old[2] ^ $new[2]));
});


/**
 * Color invert
 *
 * @param  string HEX color
 * @return string
 */
css::implement('inverse', function($color)
{
  $old = css::hex2rgb($color);

  return css::rgb2hex(array($old[0] ^ 255, $old[1] ^ 255, $old[2] ^ 255));
});


/**
 * Red component
 *
 * @param  string HEX color
 * @return string
 */
css::implement('red', function($color)
{
  $old = css::hex2rgb($color);

  return css::rgb2hex(array($old[0], 255, 255));
});


/**
 * Green component
 *
 * @param  string HEX color
 * @return string
 */
css::implement('green', function($color)
{
  $old = css::hex2rgb($color);

  return css::rgb2hex(array(255, $old[1], 255));
});


/**
 * Blue component
 *
 * @param  string HEX color
 * @return string
 */
css::implement('blue', function($color)
{
  $old = css::hex2rgb($color);

  return css::rgb2hex(array(255, 255, $old[2]));
});


/**
 * Gray component
 *
 * @param  string HEX color
 * @return string
 */
css::implement('gray', function($color)
{
  $old = css::hex2rgb($color);
  $new = (int) ($old[0] * .3 + $old[1] * .59 + $old[2] * .11);

  return css::rgb2hex(array($new, $new, $new));
});


/**
 * Color gradients
 *
 * @param     string  From color
 * @param     string  To color
 * @param     mixed   Specific index
 * @param     integer Fragments length
 * @staticvar mixed   Function callback
 * @return    string
 */
css::implement('gradient', function($color, $to, $index = 0, $step = 10)
{
  static $deg = NULL;


  if (is_null($deg))
  {
    $deg = function($from, $to, $step, $step)
    {
      return ($val = $from + (($to - $from) / $step) * $index) < 0 ? 0 : ($val > 0xff ? 0xff : $val);
    };
  }


  if (strpos($index, '%'))
  {
    $index *= ($step / 100);
  }

  $old = css::hex2rgb($color);
  $new = css::hex2rgb($to);

  return css::rgb2hex(array(
    call_user_func($deg, $old[0], $new[0], $step, $index),
    call_user_func($deg, $old[1], $new[1], $step, $index),
    call_user_func($deg, $old[2], $new[2], $step, $index),
  ));
});


/**
 * Rotate hue value
 *
 * @param  string HEX color
 * @param  mixed  Max value
 * @return string
 */
css::implement('spin', function($test, $inc = 10)
{
  $hsl    = css::rgb2hsl(css::hex2rgb($color));
  $hsl[0] = min(360, max(0, $hsl[0] + $inc));

  return css::rgb2hex(css::hsl2rgb($hsl));
});


/**
 * Hue value
 *
 * @param  string  Hex color
 * @return integer
 */
css::implement('hue', function($color)
{
  $hsl = css::rgb2hsl(css::hex2rgb($color));

  return round($hsl[0]);
});


/**
 * Saturation value
 *
 * @param  string  Hex color
 * @return integer
 */
css::implement('saturation', function($color)
{
  $hsl = css::rgb2hsl(css::hex2rgb($color));

  return round($hsl[1]);
});


/**
 * Lightness value
 *
 * @param  string  Hex color
 * @return integer
 */
css::implement('lightness', function($color)
{
  $hsl = css::rgb2hsl(css::hex2rgb($color));

  return round($hsl[2]);
});


/**
 * Normalize HEX
 *
 * @param     string HEX color
 * @staticvar mixed  Function callback
 * @return    string
 */
css::implement('hex_safe', function($color)
{
  static $safe = NULL;


  if (is_null($safe))
  {
    $safe = function($key)
    {
      return ($key < 0x1a ? 0x00 : ($key < 0x4d ? 0x33 : ($key < 0x80 ? 0x66 :
             ($key < 0xb3 ? 0x99 : ($key < 0xe6 ? 0xcc : 0xff)))));
    };
  }


  $color = array_map($safe, css::hex2rgb($color));
  $color = css::rgb2hex($color);

  return $color;
});


/**
 * Normalize RGB
 *
 * @param  mixed   Red value|Array
 * @param  integer Green value
 * @param  integer Blue value
 * @return array
 */
css::implement('rgb_safe', function($red, $green = -1, $blue = -1)
{
  $color = func_get_args();

  if (is_array($red))
  {
    $color = $red;
  }


  foreach ($color as $key => $val)
  {
    if (strpos($val, '%'))
    {
      $color[$key] = ((int) $val) * 2.55;
    }
    else
    {
      $color[$key] = ($old = round(abs($val))) > 255 ? $old % 255 : $old;
    }
  }
  return $color;
});


/**
 * RGB to HEX conversion
 *
 * @param  mixed   Red value|Array
 * @param  integer Green value
 * @param  integer Blue value
 * @return string
 */
css::implement('rgb2hex', function($red, $green = -1, $blue = -1)
{
  $color = func_get_args();

  if (is_array($red))
  {
    $color = $red;
  }

  return sprintf('#%02x%02x%02x', $color[0], $color[1], $color[2]);
});


/**
 * RGB to HSL conversion
 *
 * @param mixed   Red value|Hex color|Array
 * @param integer Green value
 * @param integer Blue value
 * @return array
 */
css::implement('rgb2hsl', function($red, $green = -1, $blue = -1)
{
  $color = func_get_args();

  if (is_array($red))
  {
    $color = $red;
  }
  elseif (is_hex($red))
  {
    $color = css::hex2rgb($red);
  }


  $red   = $color[0] / 255;
  $green = $color[1] / 255;
  $blue  = $color[2] / 255;

  $min = min($red, $green, $blue);
  $max = max($red, $green, $blue);

  $lightness = ($min + $max) / 2;

  if ($min === $max)
  {
    $hue        =
    $saturation = 0;
  }
  else
  {
    $saturation = $lightness < 0.5 ? ($max - $min) / ($max + $min) : ($max - $min) / (2.0 - $max - $min);

    if ($red === $max)
    {
      $hue = ($green - $blue) / ($max - $min);
    }
    elseif ($green === $max)
    {
      $hue = 2.0 + ($blue - $red) / ($max - $min);
    }
    elseif ($blue === $max)
    {
      $hue = 4.0 + ($red - $green) / ($max - $min);
    }
  }

  return array(
    ($hue < 0 ? $hue + 6 : $hue) * 60,
    $saturation * 100,
    $lightness * 100,
  );
});


/**
 * HSL to RGB conversion
 *
 * @param     mixed Hue value|Array
 * @param     mixed Saturation
 * @param     mixed Lightness
 * @staticvar mixed Function callback
 * @return    array
 */
css::implement('hsl2rgb', function($hue, $saturation = -1, $lightness = -1)
{
  static $value = NULL;


  if (is_null($value))
  {
    $value = function($max, $val, $hue)
    {
      $hue = ($hue < 0 ? $hue + 1 : ($hue > 0 ? $hue - 1: $hue));

      if (($hue * 6) < 1)
      {
        return $max + ($val - $max) * $hue * 6;
      }
      elseif (($hue * 2) < 1)
      {
        return $val;
      }
      elseif (($hue * 3) < 2)
      {
        return $max + ($val - $max) * ((2 / 3) - $hue) * 6;
      }
      return $max;
    };
  }


  $color = func_get_args();

  if (is_array($hue))
  {
    $color = $hue;
  }

  $color[0] /= 360;
  $color[1] /= 100;
  $color[2] /= 100;

  $alpha = isset($color[3]) ? $color[3] : 0;

  if ($color[1] === 0)
  {
    $out = array($color[2] * 255, $color[2] * 255, $color[2] * 255);
  }
  else
  {
    $b = $color[2] <= 0.5 ? $color[2] * ($color[1] + 1) : ($color[2] + $color[1]) - ($color[2] * $color[1]);
    $a = $color[2] * 2 - $b;

    $out = array(
      round(call_user_func($value, $a, $b, - $color[0] + (1/3)) * 255),
      round(call_user_func($value, $a, $b, - $color[0]) * 255),
      round(call_user_func($value, $a, $b, - $color[0] - (1/3)) * 255),
    );
  }


  $alpha && $out []= $alpha;

  return $out;
});


/**
 * HEX to RGB conversion
 *
 * @param  string HEX color|...
 * @return array
 */
css::implement('hex2rgb', function($color)
{
  $color = css::hex($color);

  return array(
    hexdec(substr($color, 0, 2)),
    hexdec(substr($color, 2, 2)),
    hexdec(substr($color, 4, 2)),
  );
});

/* EOF: ./lib/tetl/css/colors.php */
