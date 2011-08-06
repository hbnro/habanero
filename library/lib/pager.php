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
      pager::$defs += $key;
    }
    elseif (array_key_exists($key, pager::$defs))
    {
      pager::$defs[$key] = $value;
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
    $index = pager::offset(sizeof($set));
    $set   = array_slice($set, $index, pager::count_page());
    
    return $set;
  }
  
  
  /**
   * Retrieve total items
   *
   * @return integer
   */
  final public static function total()
  {
    return (int) pager::$count;
  }


  /**
   * Number of pages
   *
   * @return integer
   */
  final public static function pages()
  {
    return ceil(pager::$count / pager::$defs['count_page']);
  }
  
  
  /**
   * Items per page
   *
   * @return integer
   */
  final public static function count_page()
  {
    return (int) pager::$defs['count_page'];
  }
  
  
  /**
   * Maximum visible pages
   *
   * @return integer
   */
  final public static function count_max()
  {
    return (int) pager::$defs['count_max'];
  }


  /**
   * Number of current page
   *
   * @return integer
   */
  final public static function current()
  {
    return pager::$current ?  (int) pager::$current : 1;
  }
  
  
  /**
   * Set the current page
   *
   * @return void
   */
  final public static function index($num)
  {
    pager::$current = (int) $num;
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
    pager::$count = (int) $count;
    
    if ( ! is_false($current))
    {
      pager::$current = (int) $current;
    }
    
    $index = pager::$current ? pager::$current - 1 : pager::$current;
    $index = floor($index * pager::$defs['count_page']);

    return $index;
  }
  
  
  /**
   * All links pager
   *
   * @return array
   */
  final public static function page_all()
  {
    $out = array();
    $end = pager::pages();
    $cur = pager::current();
    
    for ($i = 1; $i <= $end; $i += 1)
    {
      $link = pager::page_link($i, pager::$defs['link_text']);
      
      if ($cur === $i)
      {
        $link = "[$link]";
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
  final public static function page_link($num, $text = '', array $args = array())
  {
    $text = $text ? sprintf(pager::$defs['link_text'], number_format($num)) : number_format($num);
    
    $args['href'] = sprintf($num <= 1 ? pager::$defs['link_root'] : pager::$defs['link_href'], $num);
    
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
    $max = pager::count_max();
    $end = pager::current() + $from;
    
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
  pager::method('select', function($table, $fields = ALL, array $where = array(), array $options = array())
  {
    return db::paginate(db::select($table, $fields, $where, $options, TRUE));
  });
  
  db::method('paginate', function($sql, $offset = 0, $limit = 10)
  {
    $sql  = sql::query_repare(preg_replace('/\bLIMIT\s+[\d,]+\s*$/s', '', $sql));
    $tmp  = sql::execute("SELECT COUNT(*) FROM ($sql) AS c");
    
    $sql .= "\nLIMIT " . pager::offset(sql::result($tmp));
    $sql .= ',' . pager::count_page();
    
    return db::query($sql);
  });
}

/* EOF: ./lib/pager.php */
