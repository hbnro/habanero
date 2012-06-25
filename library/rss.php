<?php

/**
 * RSS class
 */

if ( ! function_exists('curl_init')) {
  raise(ln('extension_missing', array('name' => 'cURL')));
}

class rss extends prototype
{
  /**#@+
   * @ignore
   */

  // xml tree
  protected $xml;

  /**#@-*/

  // shortcuts
  final public function __get($name) {
    return $this->xml->{$name};
  }
  final public function __set($name, $value) {
    raise(ln('not_implemented', array('name' => '__set')));
  }
  protected function __construct() {}



  /**
   *
   */
  final public static function load($url) {
    $xml = new SimpleXMLElement(read($url), LIBXML_NOWARNING | LIBXML_NOERROR);

    if ( ! $xml->channel) {
      return FALSE;
    }

    self::ns($xml->channel);

    foreach ($xml->channel->item as $item) {
      self::ns($item);

      if (isset($item->{'dc:date'})) {
        $item->timestamp = strtotime($item->{'dc:date'});
      } elseif (isset($item->pubDate)) {
        $item->timestamp = strtotime($item->pubDate);
      }
    }

    $out = new self;
    $out->xml = $xml->channel;

    return $out;
  }



  /**#@+
   * @ignore
   */

  // accesible namespaces
  final private static function ns($el) {
    foreach ($el->getNamespaces(TRUE) as $key => $val) {
      $tmp = $el->children($val);
      foreach ($tmp as $k => $v) {
        $el->{"$key:$k"} = $v;
      }
    }
  }

  /**#@-*/

}

/* EOF: ./library/rss.php */
