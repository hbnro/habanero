<?php

/**
 * Partial helper functions
 */

/**
 * Render shortcut
 *
 * @param  string Path
 * @param  array  Vars
 * @return string
 */
function partial($path, array $vars = array()) {
  $view_file = APP_PATH.DS.'views'.DS.str_replace('/', DS, $path);
  return partial::apply(is_file($view_file) ? 'render' : 'load', array($view_file, $vars));
}


/**
 * Clear section shortcut
 *
 * @param  string Name
 * @return void
 */
function clear($name) {
  partial::clear($name);
}


/**
 * Section shortcut
 *
 * @param  string Name
 * @param  mixed  Content
 * @return void
 */
function section($name, $content) {
  partial::section($name, $content);
}


/**
 * Prepend shortcut
 *
 * @param  string Name
 * @param  mixed  Content
 * @return void
 */
function prepend($name, $content) {
  partial::prepend($name, $content);
}


/**
 * Append shortcut
 *
 * @param  string Name
 * @param  mixed  Content
 * @return void
 */
function append($name, $content) {
  partial::append($name, $content);
}


/**
 * Yield shortcut
 *
 * @param  string Name
 * @return string
 */
function yield($section) {
  return partial::yield($section);
}


/**
 * Escaping shortcut
 *
 * @param  string Input
 * @return string
 */
function e($text) {
  return ents($text, TRUE);
}

/* EOF: ./library/partial/functions.php */
