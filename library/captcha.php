<?php

/**
 * Basic captcha library
 */

class captcha
{
  /**
   * Retrieve/set unique identifier
   *
   * @param     mixed  Code length|File
   * @param     string Unique key
   * @staticvar string Charset
   * @return    string
   */
  final public static function id($test = 6, $key = 0) {
    static $chars = 'jklmn-JKLMN#rstuvwx@RSTUVWX+56789=ABCDEFG';


    if ( ! is_numeric($test)) {
      if ( ! is_file($test)) {
        return FALSE;
      }

      $test = preg_split('/\b|[\s\t]+/', read($test));
      $test = array_filter(array_unique($test));
      $hash = $test[array_rand($test, 1)];
    } else {
      $hash = '';
      $max  = strlen($chars) - 1;

      if ($test > 10) {
        $test = 10;
      }

      while(strlen($hash) < $test) {
        $old = substr($chars, mt_rand(0, $max), 1);

        if (strpos($hash, $old) !== FALSE) {
          continue;
        }

        $hash .= $old;
      }
    }


    session("--captcha-id$$key", $hash);

    return $hash;
  }


  /**
   * Generate the captcha image
   *
   * @param     string  Hash
   * @param     integer Width
   * @param     integer Height
   * @param     string  Fontfile
   * @param     boolean Invert color?
   * @staticvar mixed   Function callback
   * @return    string
   */
  final public static function src($hash, $width = 120, $height = 24, $font = '', $invert = FALSE) {
    static $negative = NULL;


    if (is_null($negative)) {
      $negative = function ($num) {
        return $num * -1;
      };
    }


    $length   = strlen($hash);
    $resource = imagecreatetruecolor($width, $height);

    imagesavealpha($resource, TRUE);
    imagealphablending($resource, TRUE);


    $dark  = array(mt_rand(0, 96), mt_rand(0, 96), mt_rand(0, 96));
    $light = array(mt_rand(127, 240), mt_rand(127, 240), mt_rand(127, 240));

    if ($invert) {
      $bgcolor = imagecolorallocatealpha($resource, 0, 0, 0, 0);
      $light   = array_map($negative, $light);
      $dark    = array_map($negative, $dark);
    } else {
      $bgcolor = imagecolorallocatealpha($resource, 255, 255, 255, 127);
    }

    imagefill($resource, 0, 0, $bgcolor);


    $bg = imagecolorallocatealpha($resource, $light[0], $light[1], $light[2], mt_rand(45, 85));
    $fg = imagecolorallocatealpha($resource, $dark[0], $dark[1], $dark[2], mt_rand(33, 52));


    $left   = substr($hash, 0, ceil($length / 2));
    $right  = substr($hash, strlen($left));
    $factor = floor($width / $length);
    $font   = realpath($font);

    if ( ! is_file($font)) {
      $top = ($width * $height) / 3.3;

      for ($i = 0; $i < $top; $i += 1) {
        imagefilledellipse($resource, mt_rand(0, $width), mt_rand(0, $height), 1, mt_rand(1, 3), $bg);
      }

      for ($i = 0; $i < $length; $i += 1) {
        imagestring($resource, 5, ($factor / 3) + $i * $factor, mt_rand(0, $height - mt_rand(13, 20)), substr($hash, $i, 1), $fg);

        if (mt_rand(0, 75) % 9) {
          $img = imagerotate($resource, is_odd($i) ? - .8 : .7, $bgcolor);
        }
      }
    } else {
      $box = imageftbbox($factor, 0, $font, 'Q');

      $w   = max(abs($box[2]) - abs($box[0]), 1);
      $h   = max(abs($box[7]) - abs($box[6]), 1);

      imagettftext($resource, $factor, mt_rand(-4, 5), $x = mt_rand($w / strlen($left), $w * 1.3), mt_rand($h, $h * 1.33), $fg, $font, $left);

      for ($i = 2; $i < $factor; $i += 1) {
        imagettftext($resource, 16, mt_rand(20, 33), ($i - 1.5) * $factor, $i * 15, $bg, $font, str_shuffle($hash));
      }


      $a = $x + ($w * strlen($left));
      $b = $width - ($w * strlen($right)) - $w;

      imagettftext($resource, $factor, mt_rand(-3, 4), mt_rand(min($a, $b), max($a, $b)), $h * 1.20, ($fg * 1.3) / 96, $font, $right);
    }

    header('Content-Type: image/png');

    imagepng($resource);
    imagedestroy($resource);

    exit;
  }


  /**
   * Captcha code validation
   *
   * @param  string  Received code
   * @param  string  Unique key
   * @param  boolean Strict?
   * @return boolean
   */
  final public static function is_valid($test, $key = 0, $strict = FALSE) {
    if ( ! empty($test)) {
      $old = session("--captcha-id$$key");

      if ($strict) {
        return ! strcmp($old, $test);
      }
      return strtolower($old) === strtolower($test);
    }
    return FALSE;
  }
}

/* EOF: ./library/captcha.php */
