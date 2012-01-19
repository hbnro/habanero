<?php

/**
 * GD2 library
 */

if ( ! function_exists('gd_info'))
{
  raise(ln('extension_missing', array('name' => 'GD2')));
}


/**#@+
 * @ignore
 */
! defined('JPEG') && define('JPEG', 'jpeg');
! defined('JPG') && define('JPG', 'jpeg');
! defined('PNG') && define('PNG', 'png');
! defined('GIF') && define('GIF', 'gif');
/**#@-*/

class gd
{
  /**#@+
   * @ignore
   */

  // allowed formats
  private $allow = array(JPEG, PNG, GIF);

  // transparent color
  private $transparency = '#ff00ff';

  // default value
  private $alpha = 127;

  // filepath
  private $file = '';

  // size in bytes
  private $size = 0;

  // image type
  private $type = JPEG;

  // image MIME
  private $mime = 'image/jpeg';

  // the image
  private $resource = NULL;


  // hidden constructor
  private function __construct($path) {
    $test = getimagesize($path);
    $tmp  = imagecreatefromstring(read($path));

    $this->type = end(explode('/', $test['mime']));
    $this->mime = $test['mime'];
    $this->file = realpath($path);
    $this->size = filesize($path);

    $this->resource = $this->fix_alpha($tmp);
  }
  /**#@-*/


  /**
   * Image load
   *
   * @param  string File path
   * @return image
   */
  final public function import($path) {
    if ( ! is_file($path)) {
      raise(ln('file_not_exists', array('name' => $path)));
    }
    return new static($path);
  }


  /**
   * Image export
   *
   * @param  string Name|Path
   * @param  string $type Tipo de imagen
   * @return mixed
   */
  final public function export($test = '', $type = '') {
    $ext = str_replace(JPEG, 'jpg', $this->type);

    if ( ! empty($test) && is_dir($test)) {
      $output  = rtrim($test, '\\/').DS;
      $output .= extn($this->file, TRUE);
      $output .= '.' . $ext;
    } elseif (func_num_args() == 0) {
      $output = $this->file;
    } elseif ( ! empty($test)) {
      $output = dirname($this->file).DS.extn($test, TRUE).".$ext";
    }

    $callback = 'image' . (in_array($type, $this->allow) ? $type : $this->type);

    if ( ! is_callable($callback)) {
      raise(ln('not_implemented', array('name' => $callback)));
    } elseif ( ! empty($output)) {
      $callback($this->resource, $output);
      imagedestroy($this->resource);
      return $output;
    }
    $callback($this->resource);
    imagedestroy($this->resource);
  }


  /**
   * Image conversion
   *
   * @param  string  Type
   * @param  boolean Force?
   * @return void
   */
  final public function convert($type, $force = FALSE) {
    $type = in_array($type, $this->allow) ? $type : JPEG;

    if (is_true($force) OR ($this->type <> $type)) {
      ob_start();

      $this->type = $type;
      $this->file = preg_replace('/\.\w+$/', ".$type", $this->file);
      $this->mime = "image/$type";
      $this->export(NULL, $type);

      $out = ob_get_contents();
      ob_end_clean();

      $tmp = imagecreatefromstring($out);
      $tmp = $this->fix_alpha($tmp);

      imagedestroy($this->resource);

      $this->size = strlen($out);
      $this->resource = $tmp;
    }
    return $this;
  }


  /**
   * Image output
   *
   * @return void
   */
  final public function output() {
    header("Content-Length: $this->size");
    header("Content-Type: $this->mime");
    $this->export(NULL, $this->type);
    exit;
  }


  /**
   * Image info
   *
   * @return array
   */
  final public function info() {
    return array(
      'width' => $this->width(),
      'height' => $this->height(),
      'mime' => $this->mime,
      'type' => $this->type,
      'path' => dirname($this->file),
      'name' => extn($this->file, TRUE),

      'ctime' => filectime($this->file),
      'mtime' => filemtime($this->file),
      'atime' => fileatime($this->file),

      'ext' => ext($this->file, TRUE),
      'file' => $this->file,
      'size' => $this->size,
    );
  }


  /**
   * Color palette
   *
   * @link   http://www.phpbuilder.com/board/showpost.php?p=10868783&postcount=2
   * @param  integer Limit
   * @param  integer Step
   * @return array
   */
  final public function palette($limit = 10, $step = 5) {
    $out = array();

    $w = $this->width();
    $h = $this->height();

    if ($step < 1) {
      $step = 1;
    }

    for ($x = 0; $x < $w; $x += $step) {
      for ($y = 0; $y < $h; $y += $step) {
        $color = imagecolorat($this->resource, $x, $y);
        $rgb   = imagecolorsforindex($this->resource, $color);

        $R   = round(round(($rgb['red'] / 0x33)) * 0x33);
        $G   = round(round(($rgb['green'] / 0x33)) * 0x33);
        $B   = round(round(($rgb['blue'] / 0x33)) * 0x33);
        $hex = sprintf('%02x%02x%02x', $R, $G, $B);

        if (array_key_exists($hex, $out)) {
          $out[$hex] += 1;
        } else {
          $out[$hex] = 1;
        }
      }
    }

    arsort($out);

    $out = array_keys($out);
    $out = array_slice($out, 0, $limit);

    return $out;
  }


  /**
   * Image type
   *
   * @param  boolean MIME?
   * @return string
   */
  final public function type($mime = FALSE) {
    return is_true($mime) ? $this->mime : $this->type;
  }


  /**
   * Image width
   *
   * @return integer
   */
  final public function width() {
    return imagesx($this->resource);
  }


  /**
   * Image height
   *
   * @return integer
   */
  final public function height() {
    return imagesy($this->resource);
  }


  /**
   * Image filepath
   *
   * @return string
   */
  final public function file() {
    return $this->file;
  }


  /**
   * Thumbnail
   *
   * @param  integer Width
   * @param  integer Height
   * @return image
   */
  final public function thumb($width = 120, $height = 0) {
    $this->fix_dimset($width, $w = $this->width());
    $this->fix_dimset($height, $h = $this->height());

    if ($height <= 0) {
      $height = $width;
    }

    $ratio = $w / $h;

    if (($width / $height) > $ratio) {
      $h = $width / $ratio;
      $w = $width;
    } else {
      $w = $height * $ratio;
      $h = $height;
    }

    $left = ($w / 2) -($width / 2);
    $top  = ($h / 2) -($height / 2);

    return $this->resize($w, $h)->crop($width, $height, $left, $top);
  }


  /**
   * Scaling
   *
   * @param  integer Width
   * @param  integer Height
   * @return image
   */
  final public function scale($width, $height = 0) {
    $this->fix_dimset($width, $w = $this->width());
    $this->fix_dimset($height, $h = $this->height());

    if ($width && ! $height) {
      $height = ($width * $h) / $w;
    } elseif ( ! $width && $height) {
      $width = ($w / $h) * $height;
    } else {
      if($w > $h) {
        $width = ($w / $h) * $height;
      } else {
        $height = ($width * $h) / $w;
      }
    }

    return $this->resize($width, $height);
  }


  /**
   * Resizing
   *
   * @param  integer Width
   * @param  integer Height
   * @return image
   */
  final public function resize($width, $height = 0) {
    $this->fix_dimset($width, $w = $this->width());
    $this->fix_dimset($height, $h = $this->height());

    if ($height <= 0) {
      $height = $width;
    }

    $old = $this->fix_alpha(imagecreatetruecolor($width, $height));

    $this->resample($old, 0, 0, $width, $height, 0, 0, $w, $h);
    $this->resource = $old;

    return $this;
  }


  /**
   * Cropping
   *
   * @param  integer Width
   * @param  integer Height
   * @param  integer Offset X
   * @param  integer Offset Y
   * @return image
   */
  final public function crop($width, $height, $left = 0, $top = 0) {
    $this->fix_dimset($width, $w = $this->width());
    $this->fix_dimset($height, $h = $this->height());
    $this->fix_dimset($left, $w);
    $this->fix_dimset($top, $h);

    $old = $this->fix_alpha(imagecreatetruecolor($width, $height));

    imagecopyresampled($old, $this->resource, 0, 0, $left, $top, $width, $height, $width, $height);
    imagedestroy($this->resource);

    $this->resource = $old;
    return $this;
  }


  /**
   * Rotating
   *
   * @param  integer Angle
   * @param  mixed   HEX|RGB
   * @return image
   */
  final public function rotate($angle = 45, $bgcolor = '#fff') {
    if (($angle % 180) <> 0) {
      $bg  = $this->allocate($this->resource, $bgcolor);
      $tmp = imagerotate($this->resource, $angle, $bg, $this->type === PNG);

      if (is_resource($tmp)) {
        imagedestroy($this->resource);
        $this->resource = $tmp;
      }
    }
    return $this;
  }


  /**
   * Adjust brightness
   *
   * @param  integer Amount
   * @return image
   */
  final public function brightness($mnt = 13) {
    $mnt && $this->filter(__FUNCTION__, $mnt);
    return $this;
  }


  /**
   * Adjust contrast
   *
   * @param  integer Amount
   * @return image
   */
  final public function contrast($mnt = 20) {
    $mnt && $this->filter(__FUNCTION__, $mnt);
    return $this;
  }


  /**
   * Colorized image
   *
   * @param  integer Amount
   * @param  mixed   HEX|RGB
   * @return image
   */
  final public function colorize($mnt = 33, $mask = '#aa0') {
    $mnt = is_num($mnt) ? $mnt : 25;
    $per = $mnt / 100;

    if ($mask <> 'gray') {
      if ( ! is_array($mask)) {
        $px = $this->fix_rgbhex($mask);
      } else {
        $px = array_values($mask);
      }
    }

    $mnt && $this->filter(__FUNCTION__, $px[0], $px[1], $px[2]);
    return $this;
  }


  /**
   * Gaussian blur
   *
   * @return image
   */
  final public function blur() {
    return $this->filter('gaussian_blur');
  }


  /**
   * Image negative
   *
   * @return image
   */
  final public function negative() {
    return $this->filter('negate');
  }


  /**
   * Image flip
   *
   * @param  mixed Vertical?
   * @return image
   */
  final public function mirror($vertical = FALSE) {
    $width  = $this->width();
    $height = $this->height();

    // (TRUE) vertical,v,ver,vert
    $vertical = ! is_string($vertical) ? (boolean) $vertical : (strtolower(substr($vertical, 0, 1)) != 'v' ? FALSE : TRUE);
    $old      = $this->fix_alpha(imagecreatetruecolor($width, $height));

    if ( ! is_true($vertical)) {
      for ($x = 0, $w = $width; $x < $width; $x += 1) {
        imagecopy($old, $this->resource, $w -= 1, 0, $x, 0, 1, $height);
      }
    } else {
      for ($y = 0, $h = $height; $y < $height; $y += 1) {
        imagecopy($old, $this->resource, 0, $h -= 1, 0, $y, $width, 1);
      }
    }

    $this->resource = $old;
    return $this;
  }


  /**
   * Aplicar mascara
   *
   * @param  mixed   Image
   * @param  integer Offset X
   * @param  integer Offset Y
   * @param  integer Width
   * @param  integer Height
   * @param  integer Opacity
   * @return image
   */
  final public function mask($test, $left = 0, $top = 0, $width = '100%', $height = '100%', $opacity = 100) {
    if (is_num($opacity, 0, 1)) {
      $opacity *= 100;
    } // TODO: fixit

    $this->fix_dimset($x, $left, $cw = $this->width(), $w = $test->width());
    $this->fix_dimset($y, $top, $ch = $this->height(), $h = $test->height());
    $this->fix_dimset($width, $cw);
    $this->fix_dimset($height, $ch);

    $tmp = $this->fix_alpha(imagecreatetruecolor($width, $height));

    for ($wmax = ceil($width / $w), $r = 0, $m = 0; $m < $wmax; $m += 1, $r += $w) {
      for ($hmax = ceil($height / $h), $s = 0, $n = 0; $n < $hmax; $n += 1, $s += $h) {
        imagecopymerge($tmp, $test->resource, $r, $s, 0, 0, $w, $h, 100);
      }
    }

    imagecopymerge($this->resource, $tmp, $x, $y, 0, 0, $width, $height, $opacity);
    imagedestroy($tmp);

    return $this;
  }


  /**
   * Draw text
   *
   * @param  string  Text
   * @param  integer Offset X
   * @param  integer Offset Y
   * @param  integer Font size
   * @param  mixed   Font color
   * @param  mixed   Opacity
   * @param  string  Font
   * @param  mixed   Angle
   * @return image
   */
  final public function draw($text, $left = 0, $top = 0, $size = 5, $color = '#000', $opacity = 100, $font = '', $angle = 0) {
    $font = realpath($font);
    $this->fix_dimset($angle, 360);
    $pos = $this->outerbox($text, $size, $angle, $font);

    $this->fix_dimset($x, $left, $this->width(), $pos['width']);
    $this->fix_dimset($y, $top, $this->height(), $pos['height']);

    $pencil = $this->allocate($this->resource, $color, $opacity);

    if ( ! is_file($font)) {
      imagestring($this->resource, min($size, 5), $x, $y, $text, $pencil);
    } else {
      imagettftext($this->resource, $size, $angle, $x + $pos['left'], $y + $pos['top'], $pencil, $font, $text);
    }
    return $this;
  }


  /**
   * Fill rectangle
   *
   * @param  mixed Color
   * @param  mixed Offset X
   * @param  mixed Offset Y
   * @param  mixed Width
   * @param  mixed Height
   * @param  mixed Opacity
   * @return image
   */
  final public function fill($color, $left = 0, $top = 0, $width = '100%', $height = '100%', $opacity = 100) {
    $this->fix_dimset($width, $cw = $this->width());
    $this->fix_dimset($height, $ch = $this->height());
    $this->fix_dimset($x, $left, $cw, $width);
    $this->fix_dimset($y, $top, $ch, $height);

    $stroke = $this->allocate($this->resource, $color, $opacity);
    imagefilledrectangle($this->resource, $x, $y, $x + $width, $y + $height, $stroke);

    return $this;
  }


  /**
   * Gradients
   *
   * @param  mixed From
   * @param  mixed To
   * @param  mixed Offset X
   * @param  mixed Offset Y
   * @param  mixed Width
   * @param  mixed Height
   * @param  mixed Step
   * @param  mixed Opacity
   * @return void
   */ // TODO: add angle?
  final public function gradient($from, $to, $left, $top, $width = '100%', $height = '100%', $step = 1, $opacity = 100, $vertical = FALSE) {
    //http://blog.themeforest.net/tutorials/fun-with-the-php-gd-library-part-2/
    $base = $this->fix_rgbhex($from);
    $end  = $this->fix_rgbhex($to);

    $this->fix_dimset($width, $cw = $this->width());
    $this->fix_dimset($height, $ch = $this->height());
    $this->fix_dimset($x, $left, $cw, $width);
    $this->fix_dimset($y, $top, $ch, $height);

    $step = max(1, $step);
    $vertical = (boolean) $vertical;

    $max = is_true($vertical) ? $height : $width;
    $w   = is_true($vertical) ? $width : $step;
    $h   = is_true($vertical) ? $step : $height;

    foreach (array('r', 'g', 'b') as $m => $n) {
      ${$n . 'mod'} = ($end[$m] - $base[$m]) / ($max + 2);
    }

    for ($i = 0; $i < $max; $i += $step, is_true($vertical) ? $y += $step : $x += $step) {
      if (is_true($vertical) && ($diff = (($y + $h) - ($top + $height))) > 0) {
        $h -= $diff;
      } elseif (($diff = (($x + $w) -($left + $width))) > 0) {
        $w -= $diff;
      }

      $old[0] = ($rmod * $i) + $base[0];
      $old[1] = ($gmod * $i) + $base[1];
      $old[2] = ($bmod * $i) + $base[2];

      $color = $this->allocate($this->resource, $old, $opacity);
      imagefilledrectangle($this->resource, $x, $y, $x + $w, $y + $h, $color);
    }
    return $this;
  }



  /**#@+
   * @ignore
   */

  // color allocating
  final private function allocate( &$test, $color, $opacity = 100) {
    $old = $this->fix_rgbhex($color);
    if ($opacity < 100) {
      $alpha = (is_num($opacity, 0, 1) ? $opacity * 100 : min((int) $opacity, 100)) * 1.27;
      return imagecolorallocatealpha($test, $old[0], $old[1], $old[2], abs($alpha - 127));
    }
    return imagecolorallocate($test, $old[0], $old[1], $old[2]);
  }

  // filter callback
  final private function filter() {
    $args = func_get_args();
    $test = array_shift($args);
    $type = is_string($test) ? constant('IMG_FILTER_' . strtoupper($test)) : $test;

    array_unshift($args, $type);
    array_unshift($args, $this->resource);

    call_user_func_array('imagefilter', $args);

    return $this;
  }

  // better resampling?
  final private function resample( &$tmp, $tx, $ty, $tw, $th, $sx, $sy, $sw, $sh) {
    if (($tw > $sw) OR ($th > $sh)) {
      return imagecopyresampled($tmp, $this->resource, $tx, $ty, $sx, $sy, $tw, $th, $sw, $sh);
    } elseif ( ! ($tw == $sw && $th == $sh)) {
      $rX = $sw / $tw;
      $rY = $sh / $th;
      $w  = 0;

      for ($y = 0; $y < $th; $y += 1) {
        $t  = 0;
        $ow = $w;
        $w  = round(($y +1) * $rY);

        for ($x = 0; $x < $tw; $x += 1) {
          $a  = 0;
          $ot = $t;
          $r  = $g = $b = 0;
          $t  = round(($x +1) *$rX);

          for ($u = 0; $u < ($w - $ow); $u += 1) {
            for ($p = 0; $p < ($t - $ot); $p += 1) {
              $c  = $this->getdot($ot + $p + $sx, $ow + $u + $sy);
              $r += array_shift($c);
              $g += array_shift($c);
              $b += array_shift($c);
              $a += 1;
            }
          }

          imagesetpixel($tmp, $x, $y, imagecolorclosest($tmp, $r / $a, $g / $a, $b / $a));
        }
      }
    }
  }

  // compute for gray colors
  final private function gray_value($r, $g, $b) {
    return round(($r * 0.3) + ($g * 0.59) + ($b * 0.11));
  }

  // make a single pixel gray
  final private function gray_pixel($orig) {
    $gray = $this->gray_value($orig[0], $orig[1], $orig[2]);
    return array(0 => $gray, 1 => $gray, 2 => $gray);
  }

  // retrieve the pixel at position
  final private function getdot($x = 0, $y = 0) {
    $test = imagecolorsforindex($this->resource, @imagecolorat($this->resource, $x, $y));
    return array_values($test);
  }

  // compute TTF box
  final private function outerbox($test, $size = 5, $angle = 0, $file = NULL) {
    $file = realpath($file);

    if ( ! is_file($file)) {
      return array(
        'left' => 0,
        'top' => 0,
        'width' => imagefontwidth($size) * strlen($test),
        'height' => imagefontheight($size),
      );
    }

    $box = imagettfbbox($size, $angle, $file, $test);

    $xx = min(array($box[0], $box[2], $box[4], $box[6]));
    $yx = max(array($box[0], $box[2], $box[4], $box[6]));
    $xy = min(array($box[1], $box[3], $box[5], $box[7]));
    $yy = max(array($box[1], $box[3], $box[5], $box[7]));

    return array(
      'left' => $xx >= -1 ? - abs($xx + 1) : abs($xx + 2),
      'top' => abs($xy),
      'width' => $yx - $xx,
      'height' => $yy - $xy,
    );
  }

  // fixate alpha channel
  final private function fix_alpha($tmp) {// TODO: PNG/GIF bad handling...
    if (in_array($this->type, array(PNG, GIF))) {
      $index = imagecolortransparent($tmp);

      if ($index >= 0) {
        $old = imagecolorsforindex($tmp, $index);

        if (is_array($old)) {
          $index = $this->allocate($tmp, $old, $old['alpha']);
        }

        imagefill($tmp, 0, 0, $index);
        imagecolortransparent($tmp, $index);
      } else {
        $old = $this->fix_rgbhex($this->transparency);

        imagealphablending($tmp, FALSE);
        $bgcolor = imagecolorallocatealpha($tmp, $old[0], $old[1], $old[2], $this->alpha);
        imagefill($tmp, 0, 0, $bgcolor);
        imagesavealpha($tmp, TRUE);
      }
    }
    return $tmp;
  }

  // normalize RGB values
  final private function fix_rgbhex($test) {
    if ($test === 'transparent') {
      $index = imagecolortransparent($this->resource);

      if ($index >= 0) {
        return array_values(imagecolorsforindex($this->resource, $index));
      }
      return $this->fix_rgbhex($this->transparency);
    } elseif (is_array($test)) {
      return $test;
    }

    $test = preg_replace('/[^a-fA-F0-9]/', '', $test);

    if (strlen($test) === 3) {
      $test = str_repeat(substr($test, 0, 1), 2)
            . str_repeat(substr($test, 1, 1), 2)
            . str_repeat(substr($test, 2, 1), 2);
    }

    $out[0] = hexdec(substr($test, 0, 2));
    $out[1] = hexdec(substr($test, 2, 2));
    $out[2] = hexdec(substr($test, 4, 2));

    return $out;
  }

  // compute offset/dimension values
  final private function fix_dimset( &$test, $offset, $max = NULL, $min = NULL) {
    if (strrpos($test, '%')) {
      $test = floor(((func_num_args() == 2 ? $offset : $max) / 100) * ((int) $test));
    }

    if (func_num_args() === 2) {
      $test = $test < 0 ? ($offset += 1) + $test : $test;
    } elseif (func_num_args() === 4) {
      if ($offset === 0) {
        $test = $min < $max ? floor(($max - $min) / 2) : 0;
      } else {
        $test = $offset < 0 ? ($offset += 1) +($max - $min) : $offset -= 1;
      }
    }
  }

  /**#@-*/
}

/* EOF: ./library/gd.php */
