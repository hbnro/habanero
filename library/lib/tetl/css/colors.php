<?php

/**
 * CSS basic color functions
 */

/**
 * Normalize HEX
 *
 * @param  string Hex color
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
 * @param  string Hex color|...
 * @return array
 */
css::implement('hsl', function()
{
   return css::rgb2hex(css::hsl2rgb(func_get_args()));
});


/**
 * HSV value
 *
 * @param  string Hex color|...
 * @return array
 */
css::implement('hsv', function()
{
   return css::rgb2hex(css::hsv2rgb(func_get_args()));
});


/**
 * RGBA value
 *
 * @param  string Red|Array|Hex color|...
 * @param  mixed  Green|Alpha value
 * @param  mixed  Blue
 * @param  mixed  Alpha value
 * @return array
 */
css::implement('rgba', function($red, $green = 0, $blue = 0, $alpha = 100)
{
  $args = func_get_args();

  if (is_hex($red))
  {
    $args  = css::hex2rgb($red);
    $alpha = $green;
  }
  elseif (is_array($red))
  {
    $alpha = $green;
    $args  = $red;
  }
  else
  {
    $alpha = array_pop($args);
  }

  $args  = css::rgb_safe($args);
  $alpha = $alpha > 1 ? ((int) $alpha / 100) : (float) $alpha;

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
 * RGB value
 *
 * @param  string Red|Array|Hex color|...
 * @param  mixed  Green
 * @param  mixed  Blue
 * @return array
 */
css::implement('rgb', function($red, $green = 0, $blue = 0)
{
  return css::rgba($red, $green, $blue);
});


/**
 * Darken value
 *
 * @param  string Hex color
 * @param  mixed  Max value
 * @return array
 */
css::implement('darken', function($test, $inc = 50)
{
  return css::mask($test, 100 - ((int) $inc), 0);
});


/**
 * Lighten value
 *
 * @param  string Hex color
 * @param  mixed  Max value
 * @return array
 */
css::implement('lighten', function($test, $inc = 50)
{
  return css::mask($test, 100 - ((int) $inc), 255);
});


/**
 *
 */
css::implement('saturate', function()
{
  function lib_saturate($args) {
list($color, $delta) = $this->colorArgs($args);

$hsl = $this->toHSL($color);
$hsl[2] = $this->clamp($hsl[2] + $delta, 100);
return $this->toRGB($hsl);
}

});

css::implement('desaturate', function()
{

function lib_desaturate($args) {
list($color, $delta) = $this->colorArgs($args);

$hsl = $this->toHSL($color);
$hsl[2] = $this->clamp($hsl[2] - $delta, 100);
return $this->toRGB($hsl);
}
});


/**
 * Apply light/dark mask
 *
 * @param  string Hex color
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
 * @param  string Hex color
 * @param  string Hex color
 * @param  mixed  Average value
 * @return string
 */
css::implement('merge', function($color, $new, $average = 50)
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
 * @param  string Hex color
 * @param  string Hex color
 * @return string
 */
css::implement('combine', function($color, $new)
{
  $old = css::hex2rgb($color);
  $new = css::hex2rgb($new);

  return css::rgb2hex(array($old[0] ^ $new[0], $old[1] ^ $new[1], $old[2] ^ $new[2]));
});


/**
 * Color invert
 *
 * @param  string Hex color
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
 * @param  string Hex color
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
 * @param  string Hex color
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
 * @param  string Hex color
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
 * @param  string Hex color
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
    $deg($old[0], $new[0], $step, $index),
    $deg($old[1], $new[1], $step, $index),
    $deg($old[2], $new[2], $step, $index),
  ));
});


/**
 * Web safe color
 *
 * @param     string Hex color
 * @staticvar mixed  Function callback
 * @return    string
 */
css::implement('web_safe', function($color)
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
 * @param  mixed $red   Nivel de Rojo|Arreglo
 * @param  mixed $green Nivel de Verde
 * @param  mixed $blue  Nivel de Azul
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
 * @param  mixed  Red value|Array
 * @param  mixed  Green value
 * @param  mixed  Blue value
 * @return string
 */
css::implement('rgb2hex', function($red, $green = -1, $blue = -1)
{
  $color = func_get_args();

  if (is_array($red))
  {
    $color = $red;
  }

  if (is_string($color))
  {
    $color = preg_split('/\D+/', $color);
    $color = css::rgb_safe($color);
  }

  return sprintf('#%02x%02x%02x', $color[0], $color[1], $color[2]);
});


/**
 *
 */
css::implement('rgb2hsl', function($red, $green = -1, $blue = -1)
{
  $color = func_get_args();

  if (is_array($red))
  {
    $color = $red;
  }

$r = $color[1] / 255;
$g = $color[2] / 255;
$b = $color[3] / 255;

$min = min($r, $g, $b);
$max = max($r, $g, $b);

$L = ($min + $max) / 2;
if ($min == $max) {
$S = $H = 0;
} else {
if ($L < 0.5)
$S = ($max - $min)/($max + $min);
else
$S = ($max - $min)/(2.0 - $max - $min);

if ($r == $max) $H = ($g - $b)/($max - $min);
elseif ($g == $max) $H = 2.0 + ($b - $r)/($max - $min);
elseif ($b == $max) $H = 4.0 + ($r - $g)/($max - $min);

}

$out = array('hsl',
($H < 0 ? $H + 6 : $H)*60,
$S*100,
$L*100,
);

if (count($color) > 4) $out[] = $color[4]; // copy alpha
return $out;

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

  if ($color[1] == 0.0)
  {
    return array($color[2] * 255, $color[2] * 255, $color[2] * 255);
  }

  $b = $color[2] <= 0.5 ? $color[2] * ($color[1] + 1) : ($color[2] + $color[1]) - ($color[2] * $color[1]);
  $a = $color[2] * 2 - $b;

  return array(
    round($value($a, $b, - $color[0] + (1/3)) * 255),
    round($value($a, $b, - $color[0]) * 255),
    round($value($a, $b, - $color[0] - (1/3)) * 255),
  );
});


/**
 * HSV a RGB
 *
 * @param  mixed Hue value|Array
 * @param  mixed Saturation
 * @param  mixed Light value
 * @return array
 */
css::implement('hsv2rgb', function($hue, $saturation = -1, $value = -1)
{
  $color = func_get_args();

  if (is_array($hue))
  {
    $color = $hue;
  }

  $color[1] /= 255;
  $color[2] /= 255;

  if ($color[1] == 0.0)
  {
    return array($color[2], $color[2], $color[2]);
  }
  else
  {
    $color[0] = $color[0] / 255 * 6.0;
    $i = floor($color[0]);
    $f = $color[0] - $i;

    $color[2] *= 255;

    $p = (int) ($color[2] * (1.0 - $color[1]));
    $q = (int) ($color[2] * (1.0 - $color[1] * $f));
    $t = (int) ($color[2] * (1.0 - $color[1] * (1.0 - $f)));

    if ($i === 0)
    {
      return array($color[2], $t, $p);
    }
    elseif ($i === 1)
    {
      return array($q, $color[2], $p);
    }
    elseif ($i === 2)
    {
      return array($p, $color[2], $t);
    }
    elseif ($i === 3)
    {
      return array($p, $q, $color[2]);
    }
    elseif ($i === 4)
    {
      return array($t, $p, $color[2]);
    }
    return array($color[2], $p, $q);
  }
});


/**
 * HEX to RGB conversion
 *
 * @param  string Hex color|...
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
