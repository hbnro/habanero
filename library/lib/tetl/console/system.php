<?php

/**
 * Command line functions library
 */

class cli extends prototype
{

  /**#@+
   * @ignore
   */

  //
  private static $loop = FALSE;

  //
  private static $width = 80;

  //
  private static $height = 20;

  //
  private static $flags = NULL;

  /**#@-*/



  /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error
   * @param  boolean $plain Formato plano
   * @return void
   */
  final public static function args()
  {
    if (is_null(cli::$flags))
    {
      cli::$flags = array();

      $test   = $_SERVER['argv'];
      $length = sizeof($test);


      for ($i = 0; $i < $length; $i += 1)
      {
        $str = $test[$i];

        if ((strlen($str) > 2) && (substr($str, 0, 2) === '--'))
        {// --does-nothing
          $str   = substr($str, 2);
          $parts = explode('=', $str);

          cli::$flags[$parts[0]] = TRUE;

          if ((sizeof($parts) === 1) && isset($test[$i + 1]))
          {// --foo bar
            if ( ! preg_match('/^--?.+/', $test[$i + 1]))
            {
              cli::$flags[$parts[0]] = $test[$i + 1];
            }
          }
          else
          {
            cli::$flags[$parts[0]] = isset($parts[1]) ? $parts[1] : TRUE;
          }
        }
        elseif ((strlen($str) === 2) && ($str[0] === '-'))
        {// -a
          cli::$flags[$str[1]] = TRUE;

          if (isset($test[$i + 1]))
          {
            if ( ! preg_match('/^--?.+/', $test[$i + 1]))
            {
              cli::$flags[$str[1]] = $test[$i + 1];
            }
          }
        }
        elseif ((strlen($str) > 1) && ($str[0] === '-'))
        {// -xyz
          $k = strlen($str);

          for ($j = 1; $j < $k; $j += 1)
          {
            cli::$flags[$str[$j]] = TRUE;
          }
        }
      }

      foreach (array_reverse(array_slice($test, 1)) as $i => $val)
      {
        if ( ! is_num($i) OR (substr($val, 0, 1) === '-'))
        {
          continue;
        }

        array_unshift(cli::$flags, $val);
      }
    }

    return cli::$flags;
  }


  /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error
   * @param  boolean $plain Formato plano
   * @return void
   */
  final public static function main($callback)
  {
    // TODO: echo start


    cli::$loop = TRUE;

    while (cli::$loop)
    {
      $callback();
    }
  }


  /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error
   * @param  boolean $plain Formato plano
   * @return void
   */
  final public static function quit()
  {
    cli::$loop = FALSE;
  }



  /**
   * Mantener ejecucion en espera
   *
   * @param  string $text Mensaje de paso
   * @param  integer
   * @return void
   */
  final public static function wait($text = 'press_any_key')
  {
    if (is_num($text))
    {
      while (1)
      {
        if (($text -= 1) < 0)
        {
          break;
        }

        cli::write($len = strlen("$text..."));
        cli::back($len);

        pause(1);
      }
    }
    else
    {
      cli::writeln(cli::ln($text));
      cli::readln();
    }
  }


  /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error|...
   * @return void
   */
  final public static function printf($text)
  {
    fwrite(STDOUT, vsprintf(cli::format($text), array_slice(func_get_args(), 1)));
  }


  /**
   * Leer la entrada del usuario
   *
   * @param  string $text Cadena de consulta
   * @return mixed
   */
  final public static function readln($text = "\n")
  {
    $args = func_get_args();

    cli::printf(join('', $args));

    return trim(fgets(STDIN, 128));
  }


  /**
   * Escribir la salida en la consola
   *
   * @param  string $text Cadena de texto
   * @return void
   */
  final public static function writeln($text = "\n")
  {
    $args = func_get_args();

    cli::printf(trim(join('', $args)) . "\n");
    cli::flush();
  }


  /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error
   * @param  boolean $plain Formato plano
   * @return void
   */
  final public static function write($text = '')
  {
    fwrite(STDOUT, $text);
    cli::flush();
  }


   /**
   * Mostrar un error
   *
   * @param  string  $text  Mensaje de error
   * @param  boolean $plain Formato plano
   * @return void
   */
  final public static function error($text, $plain = FALSE)
  {
    $text = is_true($plain) ? $text : "\n\bred(Error)\b $text\n";

    fwrite(STDERR, cli::format($text));

    cli::flush();
  }


  /**
   * Limpiar buffer
   *
   * @return void
   */
  final public static function clear($num = 0)
  {
    if ($num)
    {
      return cli::write(str_repeat("\x08", $num));
    }
    elseif ( ! IS_WIN)
    {
      cli::write("\033[H\033[2J");
    }
    else
    {
      $c = 20;
      while($c -= 1)
      {//FIX
        cli::writeln();
      }
    }

    cli::flush();
  }




  /**
   * Formatear texto sencillo
   *
   * @param   string $text
   * @staticvar Expresion regular
   * @return  string
   */
  final public static function format($text)
  {
    static $regex = NULL,
           $bgcolors = array(
              'black' => 40,
              'red' => 41,
              'green' => 42,
              'yellow' => 43,
              'blue' => 44,
              'magenta' => 45,
              'cyan' => 46,
              'light_gray' => 47,
            ),
           $fgcolors = array(
              'black' => 30,
              'red' => 31,
              'green' => 32,
              'brown' => 33,
              'blue' => 34,
              'purple' => 35,
              'cyan' => 36,
              'light_gray' => 37,
              'dark_gray' => '1;30',
              'light_red' => '1;31',
              'light_green' => '1;32',
              'yellow' => '1;33',
              'light_blue' => '1;34',
              'light_purple' => '1;35',
              'light_cyan' => '1;36',
              'white' => '1;37',
            );


    if (is_null($regex))
    {
      $expr  = '/(\\\[cbuh]{1,3})((?:%s|)(?:,(?:%s))?)\(\s*(.*?)\s*\)\\1/s';
      $regex = sprintf($expr, join('|', array_keys($fgcolors)), join('|', array_keys($bgcolors)));
    }

    while (preg_match_all($regex, $text, $match))
    {
      foreach ($match[0] as $i => $val)
      {
        $out  = array();
        $test = explode(',', $match[2][$i]); // fg,bg

        if ($key = array_shift($test))
        {
          $out []= $fgcolors[$key];
        }


        if (strstr($match[1][$i], 'b'))
        {
          $out []= 1;
        }

        if (strstr($match[1][$i], 'u'))
        {
          $out []= 4;
        }

        if (strstr($match[1][$i], 'h'))
        {
          $out []= 7;
        }


        if ($key = array_shift($test))
        {
          $out []= $bgcolors[$key];
        }

        $color = "\033[" . ( $out ? join(';', $out) : 0) . 'm';
        $color = is_false(IS_WIN) ? "{$color}{$match[3][$i]}\033[0m" : $match[3][$i];
        $text  = str_replace($val, $color, $text);
      }
    }
    return $text;
  }


  /**
   * Comprobar argumento especifico
   *
   * @param  string  $name Bandera
   * @return boolean
   */
  function flag($name)
  {
    $set  = cli::args();

    $args = func_get_args();
    $test = is_array($name) ? $name : $args;


    foreach ($test as $one)
    {
      if ($one && array_key_exists($one, $set))
      {//FIX
        return $set[$one];
      }
    }
    return FALSE;
  }


  /**
   * Pregunta generica
   *
   * @param  string $text  Cuestionamiento de entrada
   * @param  string $default Valor por defecto
   * @return mixed
   */
  function prompt($text, $default = '')
  {
    $default && $text .= " [$default]";

    return ($out = cli::readln($text, ': ')) ? $out : $default;
  }


  /**
   * Elegir un opcion
   *
   * @param  string $text  Cuestionamiento de entrada
   * @param  string $value   Opciones disponibles
   * @param  string $default Valor por defecto
   * @return mixed
   */
  function option($text, $value = 'yn', $default = 'n')
  {
    $value = strtolower(str_replace($default, '', $value)) . strtoupper($default);
    $value = str_replace('\\', '/', trim(addcslashes($value, $value), '\\'));

    $out   = strtolower(cli::readln(sprintf('%s [%s]: ', $text, $value)));

    return ($out && strstr($value, $out)) ? $out : $default;
  }


  /**
   * Menu de seleccion
   *
   * @param  array  $set   Arreglo de opciones
   * @param  mixed  $default Valor por defecto
   * @param  string $title   Titulo del menu
   * @param  string $warn Opcion errada
   * @return mixed
   */
  function menu($set, $default = '', $title = 'choose_one_option', $warn = 'unknown_option')
  {
    $old = array_values($set);
    $pad = strlen(sizeof($set)) + 2;

    foreach ($old as $i => $val)
    {
      $test = array_search($val, $set) == $default ? ' [*]' : '';

      cli::writeln("\n", str_pad($i + 1, $pad, ' ', STR_PAD_LEFT), '. ', $val, $test);
    }


    while (1)
    {
      $val = cli::readln("\n", cli::ln($title), ': ');

      if ( ! is_numeric($val)) return $default;
      else
      {
      if (isset($old[$val -= 1])) return array_search($old[$val], $set);
      elseif ($val < 0 OR $val >= sizeof($old)) cli::error(cli::ln($warn));
      }
    }
  }




  /**
   * Ajustar texto
   *
   * @param  mixed   $text    Cadena de entrada|Arreglo
   * @param  integer $width   Ancho final del texto
   * @param  integer $align   Orientacion del texto
   * @param  integer $margin  Margen horizontal
   * @param  string  $separator Union de frases
   * @return void
   */
  function wrap($text, $width = -1, $align = 1, $margin = 2, $separator = ' ')
  {//--
  if (is_array($text)) $text = join("\n", $text);
  $max = $width > 0? $width: cli::$width +$width;
  $max -= $margin *2;
  $out = array();
  $cur = '';

  $sep = strlen($separator);
  $left = str_repeat(' ', $margin);
  $pad = $align < 0? 0: ($align == 0? 2: 1);
  $test = explode("\n", str_replace(' ', "\n", $text));

  foreach ($test as $i => $str)
  {
    if (strlen($str) > $max)
    {
    if ( ! empty($cur)) $out []= $cur;
    $out []= wordwrap($str, $max +2, "\n$left", TRUE);
    $cur = '';
    }
    else
    {
    if ((strlen($cur) +strlen($str) +$sep) >= $max)
    {//--
      $cur = trim($cur, $separator);
      $out []= str_pad($cur, $max, ' ', $pad);
      $cur = '';
    }
    $cur .= "$str$separator";
    }
  }

  if ( ! empty($cur)) $out []= $cur;

  $test = join("\n$left", $out);
  cli::writeln("\n", "$left$test");
  }


  /**
   * Mostrar cuadro de informacion
   *
   * @param  string $title Titulo del cuadro
   * @param  array  $set   Opciones detalladas
   * @return void
   */
  function help($title, $set = array())
  {// --
  cli::_print("\n$title");

  $max = 0;
  foreach (array_keys($set) as $one)
  {
    $cur = ! empty($val['args'])? strlen(join('< >', $val['args'])): 0;
    if (($cur += strlen($one)) > $max) $max = $cur;
  }
  $max += 4;

  foreach ($set as $key => $val)
  {
    cli::_print(sprintf("\n  %-{$max}s %s%s",
        $key . ( ! empty($val['args'])? ' <' . join('> <', $val['args']) . '>': ''),
        ! empty($val['flag'])? "-$val[flag]  ": '',
        $val['title']));
  }
  cli::_flush(1);
  }





  /**
   * Barra de progreso
   *
   * @param   integer $current Valor actual
   * @param   integer $total   Valor total
   * @param   string  $title   Titulo
   * @staticvar Marca de tiempo
   * @return  void
   */
  function progress($current, $total = 100, $title = '')
  {// --
  static $start = 0;

  $now = array_sum(explode(' ', microtime()));
  if ($current == 0) $start = $now;

  $diff = $current > 0? round((($now -$start) /$current) *($total -$current)): 0;
  $perc = min(100, str_pad(round(($current /$total) *100), 4, ' ', STR_PAD_LEFT) +  1);

  $title = str_replace('{elapsed}', cli::_time(round($now -$start)), $title);
  $title = str_replace('{remaining}', cli::_time($diff), $title); //--
  $dummy = cli::_strip($title = cli::format($title));
  $length = cli::$width -(strlen($dummy) +7);

  if ($current > 0) cli::back(cli::$width);
  if ( ! empty($title)) cli::_print("$title ");
  cli::_print($current == $total? "\n": '');

  $inc = 0;
  for ($i = 0; $i <= $length; $i += 1)
  {
    if ($i <= ($current /$total *$length))
    {
      $char = $i === 0 ? '[' : ($i == $length ? ']' : cli::__progress_char);
      cli::_print(cli::format("\c{cli::__progress}{$char}\c"));
    }
    else
    {//--
      $background = $perc > 99 ? cli::__progress : cli::__background;
    $char = ($inc += 1) == 0? cli::__progress_mark: ' ';
    cli::_print(cli::format("\c{$background}" . ($i == $length? ']': $char) . '\c'));
    }
  }

  cli::_print(' %3d%%', $perc);
  cli::_flush($perc > 99);
  }


  /**
   * Tabulacion de resultados
   *
   * @param  array Matriz de datos
   * @param  array Cabeceras
   * @return void
   */
  function table(array $set, array $heads = array())
  {
    $set  = array_values($set);
    $max  = cli::$width / sizeof($set[0]);
    $max -= sizeof($set[0]);

    $head =
    $sep  = array('');
    $col  = array();

    foreach ($set as $test)
    {// columns
      $key = 0;

      foreach (array_values($test) as $one)
      {
        $old = isset($col[$key]) ? $col[$key] : strlen($key);

        if ( ! isset($col[$key]))
        {
          $col[$key] = strlen($key);
        }

        if (strlen($one) > $old)
        {
          $num = strlen($one);
          $col[$key] = $num < $max ? $num : $max;
        }
        $key += 1;
      }
    }


    reset($set);

    $out = array();

    foreach (array_values($heads) as $key => $one)
    {
      $head []= str_pad($one, $col[$key], ' ', STR_PAD_BOTH);
      if (strlen($one) > $col[$key])
      {
        $col[$key] = strlen($one);
      }
    }


    $head []= '';
    $glue   = '+';
    $sep    = trim(join(' | ', $head));
    $sep    = preg_replace('/[^|\s]/', ' ', $sep);
    $sep    = strtr($sep, '| ', '+-');

    cli::write("$sep\n");

    if ( ! empty($heads))
    {
      cli::write(trim(join(' | ', $head)));
      cli::write("\n$sep");
    }


    foreach ($set as $test)
    { // data
      $key = 0;
      $row = array('');

      foreach ($test as $one)
      {
        $one   = substr($one, 0, strlen($one) > $max ? $max - 3 : $max) . (strlen($one) > $max ? '...' : '');
        $one   = str_pad($one, $col[$key], ' ', is_numeric($one)? STR_PAD_LEFT : STR_PAD_RIGHT);
        $row []= preg_replace('/[\r\n\t]/', ' ', $one);
        $key  += 1;
      }

      $row []= '';
      $out []= trim(join(' | ', $row));
    }

    cli::write("\n" . join("\n", $out));
    cli::write("\n$sep\n");
    cli::flush();
  }



  /**#@+
   * @ignore
   */

  // flush output to stdout
  final private static function flush($test = 0)
  {
    if ($test > 0)
    {
      cli::write(str_repeat("\n", $test));
    }

    ob_get_level() && ob_flush();

    flush();
  }

  //
  function strips($test)
  {
    return preg_replace("/\033\[.*?m/", '', $test);
  }

  //
  function duration($secs)
  {
    $out = sprintf('%d:%02d:%02d', floor($secs / 3600), floor($secs % 3600 / 60), $secs % 60);

    return preg_replace('/^0+:/', '', $out);
  }

  //
  function call($command, $args = array())
  {
    foreach (cli::$stack as $key => $val)
    {
      $test = array($key);

      if ( ! empty($val['aliases']))
      {
        $test = array_merge($test, $val['aliases']);
      }

      if ( ! in_array($command, $test))
      {
        continue;
      }
      elseif ( ! empty($val['callback']) && is_callable($val['callback']))
      {
        apply($val['callback'], $args);
      }
      break;
    }
  }

  //
  function ln($str)
  {
    return ln( ! is_false(strpos($str, '.')) ? $str : "cli.$str");
  }

  /**#@-*/
}

/* EOF: ./lib/tetl/console/system.php */
