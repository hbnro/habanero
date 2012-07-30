Hello World!
===========

It's basically a php framework, useful to develop web applications, and also websites.

> The main idea here is expressiveness and simplicity, based on the most simple and beautiful concepts that I have learned.


Features
--------

  * It has a bootstrap mechanism with a detailed error reporting behavior.
  * Integrated i18n for most basic language operations with support for different file formats.
  * Core utilities to work with configuration files, arrays, conditions, filesystem, hypertext and so on.
  * Also comes with tasks, generators, migrations and vhost setup scripts.
  * [Heroku](http://heroku.com/) friendly, so it should work fine.


Automatic install
-----------------

    curl get.hbnro.com | sh

The code is not evil, so don't worry about it, it will do the job for you. ;-)

> These scripts only works on **Ubuntu** 10.04LTS+ and **Mac OS X** 10.5+ by now.
> Maybe not all commands available through the `hs` utility are **cross-platform**.

Have [an issue](https://github.com/pateketrueke/habanero/issues)?


Deploying to Heroku
-------------------

Habanero is intensively tested on top of Heroku, the git-based cloud hosting platform. In my
own opinion the first place where you should release your startup application.

    # NOTE: Make sure you are into git repository

    # Create the heroku app and get your default database settings
    $ heroku create

    # Configure the default build-pack url for deployment
    $ heroku config:add BUILDPACK_URL=https://github.com/pateketrueke/heroku-buildpack-habanero

    # Push and go!
    $ git push heroku master
    $ heroku open


    # Set up your database at first
    $ heroku run "bin/atl migrate --schema"


Frequently Asked Questions
-------------------------

If you're in trouble please read the [FAQ's](https://github.com/pateketrueke/habanero/wiki/Faq%27s),
if your problem is not there please [contact me](http://twitter.com/pateketrueke)!
