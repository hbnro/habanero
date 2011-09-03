<?php

class view extends prototype
{
 // TODO: huh?

  final public static function load($file, array $vars = array())
  {
    $extension = ext($file);

    switch ($extension)
    {
      case 'taml';
        import('tetl/taml');
        return taml::render($view_file, $vars);
      break;
      case 'php';
      case 'phtml';
        return render(array(
          'partial' => $file,
          'locals' => $vars,
        ));
      break;
      default;
        die("Unknown extension: $extension");
      break;
    }
  }

}

/* EOF: ./lib/tetl/mvc/view.php */
