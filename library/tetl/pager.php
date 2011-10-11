<?php

/**
 * Basic pagination library
 */

class pager extends prototype
{

  /**#@+
   * @ignore
   */

  // number of pages
  private static $count = 0;

  // number of current page
  private static $current = 0;

  // defaults
  private static $defs = array(
                    'link_text' => '%d',
                    'link_href' => '?p=%d',
                    'link_root' => ROOT,
                    'count_max' => 13,
                    'count_page' => 20,
                  );

  /**#@-*/



  /**
   * Set configuration
   *
   * @param  mixed Key|Hash
   * @param  mixed Value
   * @return void
   */
  final public static function setup($key, $value = '')
  {
    if (is_assoc($key))
    {
      static::$defs = array_merge(static::$defs, $key);
    }
    elseif (array_key_exists($key, static::$defs))
    {
      static::$defs[$key] = $value;
    }
  }


  /**
   * Paginate array values
   *
   * @param  array Result set
   * @return array
   */
  final public static function set_array(array $set)
  {
    $index = static::offset(sizeof($set));
    $set   = array_slice($set, $index, static::count_page());

    return $set;
  }


  /**
   * Retrieve total items
   *
   * @return integer
   */
  final public static function total()
  {
    return (int) static::$count;
  }


  /**
   * Number of pages
   *
   * @return integer
   */
  final public static function pages()
  {
    return ceil(static::$count / static::$defs['count_page']);
  }


  /**
   * Items per page
   *
   * @return integer
   */
  final public static function count_page()
  {
    return (int) static::$defs['count_page'];
  }


  /**
   * Maximum visible pages
   *
   * @return integer
   */
  final public static function count_max()
  {
    return (int) static::$defs['count_max'];
  }


  /**
   * Number of current page
   *
   * @return integer
   */
  final public static function current()
  {
    return static::$current ?  (int) static::$current : 1;
  }


  /**
   * Set the current page
   *
   * @return void
   */
  final public static function index($num)
  {
    static::$current = (int) $num;
  }


  /**
   * Calculate offset
   *
   * @param  integer Length of elements
   * @param  integer Number of current page
   * @return integer
   */
  final public static function offset($count, $current = FALSE)
  {
    static::$count = (int) $count;

    if ( ! is_false($current))
    {
      static::$current = (int) $current;
    }

    $index = static::$current ? static::$current - 1 : static::$current;
    $index = floor($index * static::$defs['count_page']);

    return $index;
  }


  /**
   * All links pager
   *
   * @param  string Wrapper
   * @return array
   */
  final public static function page_all($wrap = '[%s]')
  {
    $out = array();
    $end = static::pages();
    $cur = static::current();

    for ($i = 1; $i <= $end; $i += 1)
    {
      $link = static::page_link($i, static::$defs['link_text']);

      if ($cur === $i)
      {
        $link = sprintf($wrap, $link);
      }
      $out []= $link;
    }
    return $out;
  }


  /**
   * Generate page link
   *
   * @param  integer Number page
   * @param  string  Link text
   * @param  array   Attributes
   * @return string
   */
  final public static function page_link($num, $text = '', $args = array())
  {
    if (is_string($args))
    {
      $args = args(attrs($args));
    }

    $text = $text ? sprintf(static::$defs['link_text'], number_format($num)) : number_format($num);

    $args['href'] = sprintf($num <= 1 ? static::$defs['link_root'] : str_replace('%25d', '%d', static::$defs['link_href']), $num);//FIX

    return tag('a', $args, $text);
  }


  /**
   * Calculate page step
   *
   * @return integer
   */
  final public static function page_step($from = 0)
  {
    $out = 0;
    $max = static::count_max();
    $end = static::current() + $from;

    for ($i = 0; $i < $end; $i += 1)
    {
      if (($i % $max) === 1)
      {
        $out += 1;
      }
    }

    if ($out > 0)
    {
      $out -= 1;
    }

    return $out;
  }

}


// database hooks
if (class_exists('db'))
{
  pager::implement('select', function($table, $fields = ALL, array $where = array(), array $options = array())
  {
    return db::paginate(db::select($table, $fields, $where, $options, TRUE));
  });

  db::implement('paginate', function($sql, $offset = 0, $limit = 10)
  {
    $sql  = sql::query_repare(preg_replace('/\bLIMIT\s+[\d,]+\s*$/s', '', $sql));
    $tmp  = sql::execute("SELECT COUNT(*) FROM ($sql) AS c");

    $sql .= "\nLIMIT " . pager::offset(sql::result($tmp));
    $sql .= ',' . pager::count_page();

    return db::query($sql);
  });
}

/* EOF: ./library/tetl/pager.php */
