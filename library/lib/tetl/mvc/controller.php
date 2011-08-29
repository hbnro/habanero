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
