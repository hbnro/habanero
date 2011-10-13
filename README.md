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

Just clone the GitHub read-only repository.

    $ git clone git://github.com/pateketrueke/tetlphp.git ~/tetlphp

Then move or link the entire directory within php **include_path**.

    $ sudo mv ~/tetlphp /usr/share/php

or

    $ sudo ln -s ~/tetlphp /usr/share/php

This is the best approach of the framework installation rather than using manually.

Command line utility
--------------------

To achieve this we must give execution permissions to file **stack/bin**:

    $ chmod +x /usr/share/php/tetlphp/stack/bin

Next we must create a symbolic link to the executable file:

    $ sudo ln -s /usr/share/php/tetlphp/stack/bin /usr/local/bin/tetl

To create a project using the bundled `app/mvc` middleware first execute:

    $ cd /www/vhosts
    $ mkdir -p sandbox && cd sandbox
    $ tetl app.gen

This will create some directories and some blank files.

Then we modify our **/etc/hosts** configuration to get something like:

    127.0.0.1	localhost sandbox.dev

Depending in your OS configuration you will need create and enable a
virtual host pointing to the path previously created:

    <VirtualHost *:80>
      ServerName sandbox.dev
      DocumentRoot /var/www/vhosts/sandbox/public

      <Directory /var/www/vhosts/sandbox/public/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
    </VirtualHost>

Quick start
-----------

You can write simple applications using code like this:

    require 'tetlphp/library/initialize.php';

    run(function () {

      import('tetl/server');

      route('*', function () {
        echo 'Hello world!';
      });

    });

Within bundled `app/base` everything is organized into conventional paths:

    /app
      /controllers
      /helpers
      /models
      /views
        /assets
          /css
          /js
            /lib
        /errors
        /layouts
        /styles
    /config
      /environments
    /db
      /backup
      /migrate
    /lib
      /tasks
        /rsync
    /public
      /js

To start navigating just open your browser pointing to the virtual host you created.

Currently **tetl** support making of controllers, models, actions and basic
database migrations via the command line utility.
