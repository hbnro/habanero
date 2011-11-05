<?php

/**
 * Command line functions library
 */

class cli extends prototype
{

  /**#@+
   * @ignore
   */

  // commands stack
  private static $set = array();

  // main loop control
  private static $loop = FALSE;

  // default screen width
  private static $width = 80;

  // default screen height
  private static $height = 20;

  // arguments input
  private static $flags = NULL;

  // background
  private static $bgcolors = array(
                    'black' => 40,
                    'red' => 41,
                    'green' => 42,
                    'yellow' => 43,
                    'blue' => 44,
                    'magenta' => 45,
                    'cyan' => 46,
                    'light_gray' => 47,
                  );

  // foreground
  private static $fgcolors = array(
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

  /**#@-*/



  /**
   * Retrieve input args
   *
   * @return array
   */
  final public static function args() {
    if (is_null(static::$flags)) {
      static::$flags = array();

      $test   = $_SERVER['argv'];
      $length = sizeof($test);


      for ($i = 0; $i < $length; $i += 1) {
        $str = $test[$i];

        if ((strlen($str) > 2) && (substr($str, 0, 2) === '--')) {// --does-nothing
          $str   = substr($str, 2);
          $parts = explode('=', $str);

          static::$flags[$parts[0]] = TRUE;

          if ((sizeof($parts) === 1) && isset($test[$i + 1])) {// --foo bar
            if ( ! preg_match('/^--?.+/', $test[$i + 1])) {
              static::$flags[$parts[0]] = $test[$i + 1];
            }
          } else {
            static::$flags[$parts[0]] = isset($parts[1]) ? $parts[1] : TRUE;
          }
        } elseif ((strlen($str) === 2) && ($str[0] === '-')) {// -a
          static::$flags[$str[1]] = TRUE;

          if (isset($test[$i + 1])) {
            if ( ! preg_match('/^--?.+/', $test[$i + 1])) {
              static::$flags[$str[1]] = $test[$i + 1];
            }
          }
        } elseif ((strlen($str) > 1) && ($str[0] === '-')) {// -xyz
          $k = strlen($str);

          for ($j = 1; $j < $k; $j += 1) {
            static::$flags[substr($str, $j, 1)] = TRUE;
          }
        }
      }

      foreach (array_reverse(array_slice($test, 1)) as $i => $val) {
        if ( ! is_num($i) OR (substr($val, 0, 1) === '-')) {
          continue;
        }

        array_unshift(static::$flags, $val);
      }
    }

    return static::$flags;
  }


  /**
   * Register handler
   *
   * @param  mixed Command|Aliases
   * @param  mixed Function callback
   * @return void
   */
  final public static function register($command, Closure $callback) {
    static::$set []= array(
      'aliases' => (array) $command,
      'callback' => $callback,
    );
  }


  /**
   * Execute handler
   *
   * @param  string Command
   * @param  array  Arguments
   * @return void
   */
  final public static function execute($command, array $args = array()) {
    foreach (static::$set as $one) {
      if ( ! in_array($command, $one['aliases'])) {
        continue;
      }

      call_user_func_array($one['callback'], $args);
      break;
    }

    static::help(static::ln('available_commands'), static::$set);
  }


  /**
   * Main loop
   *
   * @param  mixed Function callback
   * @return void
   */
  final public static function main(Closure $callback) { // TODO: echo start?
    static::$loop = TRUE;

    while (static::$loop) {
      $callback();
    }
  }


  /**
   * Stop main loop
   *
   * @return void
   */
  final public static function quit() {
    static::$loop = FALSE;
  }


  /**
   * Wait for user input
   *
   * @param  mixed Message|Secs
   * @return void
   */
  final public static function wait($text = 'press_any_key') {
    if (is_num($text)) {
      while (1) {
        if (($text -= 1) < 0) {
          break;
        }

        static::write($len = strlen("$text..."));
        static::back($len);

        pause(1);
      }
    } else {
      static::writeln(static::ln($text));
      static::readln();
    }
  }


  /**
   * Print out formatted text
   *
   * @param  string Text to print out
   * @return void
   */
  final public static function printf($text) {
    fwrite(STDOUT, vsprintf(static::format($text), array_slice(func_get_args(), 1)));
  }


  /**
   * Read user input
   *
   * @param  string Text to prompt
   * @return mixed
   */
  final public static function readln($text = "\n") {
    $args = func_get_args();

    if (function_exists('readline')) {
      return trim(readline(join('', $args)));
    }

    static::printf(join('', $args));

    return trim(fgets(STDIN, 128));
  }


  /**
   * Print out a line of text
   *
   * @param  string Text to print out
   * @return void
   */
  final public static function writeln($text = "\n") {
    $args = func_get_args();

    static::printf(join('', $args) . "\n");
    static::flush();
  }


  /**
   * Print out raw text
   *
   * @param  string Text to print out
   * @return void
   */
  final public static function write($text = '') {
    fwrite(STDOUT, $text);
    static::flush();
  }


  /**
   * Error output
   *
   * @param  string  Error message
   * @param  boolean Plain output
   * @return void
   */
  final public static function error($text, $plain = FALSE) {
    $error = static::ln('error');
    $text  = is_true($plain) ? $text : "\n\bred($error)\b $text\n";

    fwrite(STDERR, static::format($text));

    static::flush();
  }


  /**
   * Clear screen
   *
   * @param  integer Spaces to back
   * @return void
   */
  final public static function clear($num = 0) {
    if ($num) {
      return static::write(str_repeat("\x08", $num));
    } elseif ( ! IS_WIN) {
      static::write("\033[H\033[2J");
    } else {
      $c = static::$height;

      while($c -= 1) {
        static::writeln();
      }
    }

    static::flush();
  }


  /**
   * Basic text formatting
   *
   * @param     string Text to print out
   * @staticvar string RegExp to match format
   * @return    string
   */
  final public static function format($text) {
    static $regex = NULL;


    if (is_null($regex)) {
      $expr  = '/(\\\[cbuh]{1,3})((?:%s|)(?:,(?:%s))?)\(\s*(.*?)\s*\)\\1/s';
      $regex = sprintf($expr, join('|', array_keys(static::$fgcolors)), join('|', array_keys(static::$bgcolors)));
    }


    while (preg_match_all($regex, $text, $match)) {
      foreach ($match[0] as $i => $val) {
        $out  = array();
        $test = explode(',', $match[2][$i]); // fg,bg

        if ($key = array_shift($test)) {
          $out []= static::$fgcolors[$key];
        }


        if (strstr($match[1][$i], 'b')) {
          $out []= 1;
        }

        if (strstr($match[1][$i], 'u')) {
          $out []= 4;
        }

        if (strstr($match[1][$i], 'h')) {
          $out []= 7;
        }


        if ($key = array_shift($test)) {
          $out []= static::$bgcolors[$key];
        }

        $color = "\033[" . ( $out ? join(';', $out) : 0) . 'm';
        $color = is_false(IS_WIN) ? "{$color}{$match[3][$i]}\033[0m" : $match[3][$i];
        $text  = str_replace($val, $color, $text);
      }
    }
    return $text;
  }


  /**
   * Retrieve flag
   *
   * @param  string  Item name
   * @param  mixed   Default value
   * @return boolean
   */
  final public static function flag($name, $or = FALSE) {
    $set  = static::args();
    $name = ! is_array($name) ? explode(' ', $name) : $name;

    foreach ($name as $one) {
      if ( ! empty($set[$one])) {
        return $set[$one];
      }
    }
    return $or;
  }


  /**
   * User prompt
   *
   * @param  string Question
   * @param  string Default
   * @return mixed
   */
  final public static function prompt($text, $default = '') {
    $default && $text .= " [$default]";

    return static::readln($text, ': ') ?: $default;
  }


  /**
   * Options
   *
   * @param  string Question
   * @param  string Options
   * @param  string Default
   * @return mixed
   */
  final public static function choice($text, $value = 'yn', $default = 'n') {
    $value = strtolower(str_replace($default, '', $value)) . strtoupper($default);
    $value = str_replace('\\', '/', trim(addcslashes($value, $value), '\\'));

    $out   = static::readln(sprintf('%s [%s]: ', $text, $value)) ?: $default;

    return ($out && strstr($value, strtolower($out))) ? $out : $default;
  }


  /**
   * Options menu
   *
   * @param  array  Options hash
   * @param  mixed  Default value
   * @param  string Menu title
   * @param  string Warn text
   * @return mixed
   */
  final public static function menu(array $set, $default = '', $title = 'choose_one_option', $warn = 'unknown_option') {
    $old = array_values($set);
    $pad = strlen(sizeof($set)) + 2;

    foreach ($old as $i => $val) {
      $test = array_search($val, $set) == $default ? ' [*]' : '';

      static::writeln("\n", str_pad($i + 1, $pad, ' ', STR_PAD_LEFT), '. ', $val, $test);
    }


    while (1) {
      $val = static::readln("\n", static::ln($title), ': ');

      if ( ! is_numeric($val)) {
        return $default;
      } else {
        if (isset($old[$val -= 1])) {
          return array_search($old[$val], $set);
        } elseif ($val < 0 OR $val >= sizeof($old)) {
          static::error(static::ln($warn));
        }
      }
    }
  }


  /**
   * Text wrap
   *
   * @param  mixed   Text to print out
   * @param  integer Text width
   * @param  integer Text align
   * @param  integer Horizontal margin
   * @param  string  Phrase separator
   * @return void
   */
  final public static function wrap($text, $width = -1, $align = 1, $margin = 2, $separator = ' ') {
    if (is_array($text)) {
      $text = join("\n", $text);
    }

    $max  = $width > 0 ? $width : static::$width + $width;
    $max -= $margin *2;
    $out  = array();
    $cur  = '';

    $sep  = strlen($separator);
    $left = str_repeat(' ', $margin);
    $pad  = $align < 0 ? 0 : ($align === 0 ? 2 : 1);
    $test = explode("\n", str_replace(' ', "\n", $text));

    foreach ($test as $i => $str) {
      if (strlen($str) > $max) {
        if ( ! empty($cur)) {
          $out []= $cur;
        }
        $out []= wordwrap($str, $max + 2, "\n$left", TRUE);
        $cur  = '';
      } else {
        if ((strlen($cur) + strlen($str) + $sep) >= $max) {
          $cur   = trim($cur, $separator);
          $out []= str_pad($cur, $max, ' ', $pad);
          $cur   = '';
        }
        $cur .= "$str$separator";
      }
    }

    if ( ! empty($cur)) {
      $out []= $cur;
    }

    $test = join("\n$left", $out);

    static::writeln("\n", "$left$test");
  }


  /**
   * Show help
   *
   * @param  string Help title
   * @param  array  Options
   * @return void
   */
  final public static function help($title, array $set = array()) {
    static::write("\n$title");

    $max = 0;

    foreach ($set as $one => $val) {
      $cur = ! empty($val['args']) ? strlen(join('> <', $val['args'])) : 0;

      if (($cur += strlen($one)) > $max) {
        $max = $cur;
      }
    }

    $max += 4;

    foreach ($set as $key => $val) {
      $args = $key . ( ! empty($val['args']) ? ' <' . join('> <', $val['args']) . '>' : '');
      $flag = ! empty($val['flag']) ? "-$val[flag]  " : '';

      static::write(sprintf("\n  %-{$max}s %s%s", $args, $flag, $val['title']));
    }
    static::flush(1);
  }


  /**
   * Progress bar
   *
   * @param     integer Current value
   * @param     integer Total value
   * @param     string  Title
   * @staticvar integer Timestamp
   * @return    void
   */
  final public static function progress($current, $total = 100, $title = '') {
    static $start = 0;


    $now = ticks();

    if ($current == 0) {
      $start = $now;
    }


    $diff = $current > 0 ? round((($now - $start) / $current) * ($total - $current)) : 0;
    $perc = min(100, str_pad(round(($current / $total) * 100), 4, ' ', STR_PAD_LEFT) +  1);

    $title  = str_replace('%{elapsed}', static::duration(round($now - $start)), $title);
    $title  = str_replace('%{remaining}', static::duration($diff), $title);
    $dummy  = static::strips($title = static::format($title));
    $length = static::$width - (strlen($dummy) + 7);

    if ($current > 0) {
      static::clear(static::$width);
    }

    if ( ! empty($title)) {
      static::write("$title ");
    }

    static::write($current == $total ? "\n" : '');

    $inc = 0;

    for ($i = 0; $i <= $length; $i += 1) {
      if ($i <= ($current / $total * $length)) {
        $char = $i === 0 ? '[' : ($i == $length ? ']' : '*');

        static::write(static::format("\ccyan($char)\c"));
      } else {
        $background = $perc > 99 ? 'green' : 'red';
        $char = ($inc += 1) == 0 ? '=' : ' ';

        static::write(static::format("\c{$background}" . ($i == $length ? ']' : $char) . '\c'));
      }
    }

    static::write(' %3d%%', $perc);
    static::flush($perc > 99);
  }


  /**
   * Data tabulation
   *
   * @param  array Matrix
   * @param  array Headers
   * @return void
   */
  final public static function table(array $set, array $heads = array()) {
    // TODO: make it work on large amount of data?
    $set  = array_values($set);
    $max  = static::$width / sizeof($set[0]);
    $max -= sizeof($set[0]);

    $head =
    $sep  =
    $col  = array();

    foreach ($set as $test) {// columns
      $key = 0;

      foreach (array_values($test) as $one) {
        $old = isset($col[$key]) ? $col[$key] : strlen($key);

        if ( ! isset($col[$key])) {
          $col[$key] = strlen($key);
        }

        if (strlen($one) > $old) {
          $num = strlen($one);
          $col[$key] = $num < $max ? $num : $max;
        }
        $key += 1;
      }
    }


    reset($set);

    $out = array();

    foreach (array_values($heads) as $key => $one) {
      $head []= str_pad($one, $col[$key], ' ', STR_PAD_RIGHT);
      $head []= ' ';

      if (strlen($one) > $col[$key]) {
        $col[$key] = strlen($one);
      }
    }


    $glue   = '';

    static::writeln();

    if ( ! empty($heads)) {
      $heads = join('', $head);
      $heads = preg_replace('/\b\w+\b/', '\cpurple(\\0)\c', $heads);

      static::write(cli::format(" $heads"));
      static::writeln();
    }


    foreach ($set as $test) { // data
      $key = 0;
      $row = array('');

      foreach ($test as $one) {
        $one   = substr($one, 0, strlen($one) > $max ? $max - 3 : $max) . (strlen($one) > $max ? '...' : '');
        $one   = str_pad($one, $col[$key], ' ', is_num($one) ? STR_PAD_LEFT : STR_PAD_RIGHT);
        $row []= preg_replace('/[\r\n\t]/', ' ', $one);
        $key  += 1;
      }

      $row []= '';
      $out []= ' ' . trim(join(' ', $row));
    }

    static::write("\n" . join("\n", $out));
    static::write("\n\n");
    static::flush();
  }



  /**#@+
   * @ignore
   */

  // flush output to stdout
  final private static function flush($test = 0) {
    if ($test > 0) {
      static::write(str_repeat("\n", $test));
    }

    ob_get_level() && ob_flush();

    flush();
  }

  // remove color codes
  final private static function strips($test) {
    return preg_replace("/\033\[.*?m/", '', $test);
  }

  // time formatting
  final private static function duration($secs) {
    $out = sprintf('%d:%02d:%02d', floor($secs / 3600), floor($secs % 3600 / 60), $secs % 60);

    return preg_replace('/^0+:/', '', $out);
  }

  // translations
  final private static function ln($str) {
    return ln( ! is_false(strpos($str, '.')) ? $str : "cli.$str");
  }

  /**#@-*/
}

/* EOF: ./library/tetl/console/system.php */
