<?php

/**
 * MVC controller
 */

class controller extends prototype
{

  /**#@+
   * @ignore
   */

  // view instance vars
  public static $view = array();

  // head hooks?
  public static $head = array();

  // default title
  public static $title = 'untitled';

  // default layout
  public static $layout = 'default';

  // output response
  public static $response = array(
    'status' => 200,
    'headers' => array(
      'content-type' => 'text/html',
    ),
  );

  /**#@-*/

}

/* EOF: ./lib/tetl/mvc/controller.php */
