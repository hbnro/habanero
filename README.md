Hello World!
===========

It's basically a php framework, useful to develop web applications, and also websites.

> The main idea here is expressiveness and simplicity, based on the most simple and beautiful concepts that I have learned.

Features
--------

  * It has a bootstrap mechanism with a detailed error reporting behavior.
  * Integrated i18n for most basic language operations
    with support for different file formats.
  * Core utilities to work with  configuration files,
    conditions, filesystem and hypertext.
  * [Heroku](http://heroku.com/) friendly, so it should work fine.

Installation
------------

Just download the latest zip file from the [downloads section](http://tinyurl.com/gettetl)
and unpack it or if you wish clone the entire repository.

    git clone git://github.com/pateketrueke/tetlphp.git

Now `cd` inside the extracted/cloned path and execute with **sudo** the install.sh file.

    cd <tetlphp>
    sudo sh install.sh

The installation will create a **command line** utility called `atl`,
the program to manage our **application** and **databases** among other goodies.

> These scripts only works on **Ubuntu** 10.04LTS+ and **Mac OS X** 10.5+ by now.
> Maybe not all commands available through the `atl` utility are **cross-platform**.

Also will try to configure the **include_path** from the **php.ini** file.

If not, please open an issue [right here](https://github.com/pateketrueke/tetlphp/issues).

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

> **TODO**: I'm working hard on documentation (in spanish hopefully), by now please check out the source
> of the generated skeleton application to get you ready with Tetl.

Finally you can execute the `atl` program without arguments to see the available options ¡try it!

Deploying to production
-----------------------

Tetl is intensively tested on top of **Heroku**, the git-based cloud hosting platform. In my
own opinion the first place where you should release your startup application.

    # NOTE: Make sure you are into git repository

    # Create the heroku app and get your default database settings
    $ heroku create --stack cedar
    $ heroku addons:add shared-database:5mb

    # Configure the default build-pack url for deployment
    $ heroku config:add BUILDPACK_URL=https://github.com/pateketrueke/heroku-buildpack-tetlphp

    # Push and go!
    $ git push heroku master
    $ heroku open

Frequently Asked Questions
-------------------------

If you're in trouble please read the [FAQ's](https://github.com/pateketrueke/tetlphp/wiki/Faq%27s), if your problem is not there please [contact me](http://twitter.com/pateketrueke)!
