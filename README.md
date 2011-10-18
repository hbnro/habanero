Hello World!
===========

It's basically a php framework, useful to develop web applications, and also websites.

The main idea here is expressiveness and simplicity, based on the most simple and beautiful concepts that I have learned.

Features
--------

  * By default the main core is extensible, configurable and hookable using middleware.
  * Primarly provides a routing system, request/response is are friendly-url based.
  * It has a bootstrap mechanism and a detailed error reporting behavior.
  * The helpers are function libraries or prototyped static classes.
  * Integrated i18n for most basic language operations.
  * Many more utilities, and so on.

Installation
------------

¿Do you like magic? Just copy+paste in your command line.

    $ curl -L http://is.gd/gettetl | sudo sh

If everything is all right the **tetl** executable should be available.

    $ tetl -s

Command line utility
--------------------

To create a project using the bundled `app/mvc` middleware first execute:

    $ cd /www/vhosts
    $ tetl -s

This will create some directories and some blank files.

By default the generator script will attempt to find out where
are the vhost directories and will perform modifications on their files.

Alternate start
---------------

Also you can write simple applications from scratch using code like this:

    require 'tetlphp/framework/initialize.php';

    run(function () {

      import('tetl/server');

      route('*', function () {
        echo 'Hello world!';
      });

    });
