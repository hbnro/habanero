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

Just clone the repo from Github.

    $ git clone git://github.com/pateketrueke/tetlphp.git ~/tetlphp

Then move or link the entire directory within php **include_path**.

    $ sudo mv ~/tetlphp /usr/share/php

or

    $ sudo ln -s ~/tetlphp /usr/share/php

Or download the latest version and extract it wherever you want.

Command line utility
--------------------

To achieve this we must give execution permissions to file **console/bin**

    $ chmod +x /usr/share/php/tetlphp/console/bin

Next we must create a symbolic link to the executable file

    $ sudo ln -s /usr/share/php/tetlphp/console/bin /usr/local/bin/tetl

To create a project using the bundled mvc middleware first execute

    $ cd /www/vhosts
    $ mkdir -p sandbox && cd sandbox
    $ tetl app.gen

This will create some directories and some sample files

Then we modify our **/etc/hosts** configuration to get something like

    127.0.0.1	localhost sandbox.dev

Depending in your OS configuration you will need create and enable a
virtual host pointing to the path previously created

    <VirtualHost *:80>
      ServerName sandbox.dev
      DocumentRoot /home/vhosts/sandbox/public

      <Directory /home/vhosts/sandbox/public/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
    </VirtualHost>



Quick start
-----------

    require 'tetlphp/library/initialize.php';

    run(function()
    {

      import('tetl/server');

      route('*', function()
      {
        echo 'Hello world!';
      });

    });

Within bundled mvc everything is organized into conventional paths

    /app
      /controllers
      /helpers
      /models
      /views
        /errors
        /layouts
        /scripts
          /home
        /styles
    /config
      /environments
    /db
      /backup
      /migrate
    /public
      /css
      /js

Currently tetl support making of controllers, models, actions and basic
database migrations via the command line utility.

Follow [@tetlphp](http://twitter.com/tetlphp)
------

Our contributors:

  * [@Sourcegeek](http://twitter.com/Sourcegeek)
  * [@pateketrueke](http://twitter.com/pateketrueke)
