.. index::
    single: Front-end; Bower

Using Bower with Symfony
========================

Symfony and all its packages are perfectly managed by Composer. Bower is a
dependency management tool for front-end dependencies, like Bootstrap or
jQuery. As Symfony is purely a back-end framework, it can't help you much with
Bower. Fortunately, it is very easy to use!

Installing Bower
----------------

Bower_ is built on top of `Node.js`_. Make sure you have that installed and
then run:

.. code-block:: bash

    $ npm install -g bower

After this command has finished, run ``bower`` in your terminal to find out if
it's installed correctly.

.. tip::

    If you don't want to have NodeJS on your computer, you can also use
    BowerPHP_ (an unofficial PHP port of Bower). Beware that this is still in
    an alpha state. If you're using BowerPHP, use ``bowerphp`` instead of
    ``bower`` in the examples.

Configuring Bower in your Project
---------------------------------

Normally, Bower downloads everything into a ``bower_components/`` directory. In
Symfony, only files in the ``web/`` directory are publicly accessible, so you
need to configure Bower to download things there instead. To do that, just
create a ``.bowerrc`` file with a new destination (like ``web/assets/vendor``):

.. code-block:: json

    {
        "directory": "web/assets/vendor/"
    }

.. tip::

    If you're using a front-end build system like `Gulp`_ or `Grunt`_, then
    you can set the directory to whatever you want. Typically, you'll use
    these tools to ultimately move all assets into the ``web/`` directory.

An Example: Installing Bootstrap
--------------------------------

Believe it or not, but you're now ready to use Bower in your Symfony
application. As an example, you'll now install Bootstrap in your project and
include it in your layout.

Installing the Dependency
~~~~~~~~~~~~~~~~~~~~~~~~~

To create a ``bower.json`` file, just run ``bower init``. Now you're ready to
start adding things to your project. For example, to add Bootstrap_ to your
``bower.json`` and download it, just run:

.. code-block:: bash

    $ bower install --save bootstrap

This will install Bootstrap and its dependencies in ``web/assets/vendor/`` (or
whatever directory you configured in ``.bowerrc``).

.. seealso::

    For more details on how to use Bower, check out `Bower documentation`_.

Including the Dependency in your Template
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that the dependencies are installed, you can include bootstrap in your
template like normal CSS/JS:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/layout.html.twig #}
        <!doctype html>
        <html>
            <head>
                {# ... #}

                <link rel="stylesheet"
                    href="{{ asset('assets/vendor/bootstrap/dist/css/bootstrap.min.css') }}">
            </head>

            {# ... #}
        </html>

    .. code-block:: html+php

        <!-- app/Resources/views/layout.html.php -->
        <!doctype html>
        <html>
            <head>
                {# ... #}

                <link rel="stylesheet" href="<?php echo $view['assets']->getUrl(
                    'assets/vendor/bootstrap/dist/css/bootstrap.min.css'
                ) ?>">
            </head>

            {# ... #}
        </html>

Great job! Your site is now using Bootstrap. You can now easily upgrade
bootstrap to the latest version and manage other front-end dependencies too.

Should I Git Ignore or Commit Bower Assets?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Currently, you should probably *commit* the assets downloaded by Bower instead
of adding the directory (e.g. ``web/assets/vendor``) to your ``.gitignore``
file:

.. code-block:: bash

    $ git add web/assets/vendor

Why? Unlike Composer, Bower currently does not have a "lock" feature, which
means that there's no guarantee that running ``bower install`` on a different
server will give you the *exact* assets that you have on other machines.
For more details, read the article `Checking in front-end dependencies`_.

But, it's very possible that Bower will add a lock feature in the future
(e.g. `bower/bower#1748`_).

If you don't care too much about having *exact* the same versions, you can only
commit the ``bower.json`` file. Running ``bower install`` will give you the
latest versions within the specified version range of each package in
``bower.json``. Using strict version constraints (e.g. ``1.10.*``) is often
enough to ensure only bringing in compatible versions.

.. _Bower: http://bower.io
.. _`Node.js`: https://nodejs.org
.. _BowerPHP: http://bowerphp.org/
.. _`Bower documentation`: http://bower.io/
.. _Bootstrap: http://getbootstrap.com/
.. _Gulp: http://gulpjs.com/
.. _Grunt: http://gruntjs.com/
.. _`Checking in front-end dependencies`: http://addyosmani.com/blog/checking-in-front-end-dependencies/
.. _`bower/bower#1748`: https://github.com/bower/bower/pull/1748
