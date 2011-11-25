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

Just run the next code in your command line.

    curl -fsSL http://is.gd/gettetl | sudo sh

The installation will create a **command line** utility called `atl`,
the program to manage our **application** and **databases** among other goodies.

> These scripts only works on **Ubuntu** 10.04LTS+ and **Mac OS X** 10.5+ by now.
> Maybe not all commands available through the `atl` utility are **cross-platform**.

The skeleton files
------------------

Lets asume our **web-docs** directory as `/var/www/vhosts` so move on it.

Create the **new** application with `atl`, then `cd` inside the created **sandbox** directory.

    $ cd /var/www/vhosts
    $ atl new sandbox
    $ cd sandbox

Now, let's create the virtual host to view our application.

    $ sudo atl --vhost
    $ atl --open

By default the name of our local domain is taken from the application path.

> **TODO**: I'm working hard on documentation, by now please check out the source
> of the generated skeleton application to get you ready with Tetl.

Finally you can execute the `atl` program without arguments to see the available options ¡try it!
