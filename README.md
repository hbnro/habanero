Hello World!
===========

It's basically a php framework, useful to develop web applications, and also websites.

The main idea here is expressiveness and simplicity, based on the most simple and beautiful concepts that I have learned.


Features
--------

  * By default the main core is extensible, configurable and hookable.
  * Primarly provides a routing system, request/response is are friendly-url based.
  * It has a bootstrap mechanism and a detailed error reporting behavior.
  * The helpers are function libraries or prototyped static classes.
  * Integrated i18n for most basic language operations.
  * Many more utilities, and so on.


Installation
------------

Just clone the repo from Github.

    $ git clone git@github.com:pateketrueke/tetlphp.git ~/tetlphp

Then move or link the entire directory within php **include_path**.

    $ sudo mv ~/tetlphp /usr/share/php

or

    $ sudo ln -s ~/tetlphp /usr/share/php

Or download the latest version and extract it wherever you want, remember include always.

Quick start
-----------

    require 'tetlphp/library/initialize';

    run(function()
    {

      import('tetl/server');
      import('tetl/router');

      route('*', function()
      {
        echo 'Hello world!';
      });

    });


Follow
------

The developer ([@tetlphp](http://twitter.com/tetlphp))

The human ([@pateketrueke](http://twitter.com/pateketrueke))
