Create your own framework... on top of the Symfony2 Components (part 1)
=======================================================================

Symfony2 is a reusable set of standalone, decoupled, and cohesive PHP
components that solve common web development problems.

Instead of using these low-level components, you can use the ready-to-be-used
Symfony2 full-stack web framework, which is based on these components... or
you can create your very own framework. This series is about the latter.

.. note::

    If you just want to use the Symfony2 full-stack framework, you'd better
    read its official `documentation`_ instead.

Why would you like to create your own framework?
------------------------------------------------

Why would you like to create your own framework in the first place? If you
look around, everybody will tell you that it's a bad thing to reinvent the
wheel and that you'd better choose an existing framework and forget about
creating your own altogether. Most of the time, they are right but I can think
of a few good reasons to start creating your own framework:

* To learn more about the low level architecture of modern web frameworks in
  general and about the Symfony2 full-stack framework internals in particular;

* To create a framework tailored to your very specific needs (just be sure
  first that your needs are really specific);

* To experiment creating a framework for fun (in a learn-and-throw-away
  approach);

* To refactor an old/existing application that needs a good dose of recent web
  development best practices;

* To prove the world that you can actually create a framework on your own (...
  but with little effort).

I will gently guide you through the creation of a web framework, one step at a
time. At each step, you will have a fully-working framework that you can use
as is or as a start for your very own. We will start with simple frameworks
and more features will be added with time. Eventually, you will have a
fully-featured full-stack web framework.

And of course, each step will be the occasion to learn more about some of the
Symfony2 Components.

.. tip::

    If you don't have time to read the whole series, or if you want to get
    started fast, you can also have a look at `Silex`_, a micro-framework
    based on the Symfony2 Components. The code is rather slim and it leverages
    many aspects of the Symfony2 Components.

Many modern web frameworks call themselves MVC frameworks. We won't talk about
MVC here as the Symfony2 Components are able to create any type of frameworks,
not just the ones that follow the MVC architecture. Anyway, if you have a look
at the MVC semantics, this series is about how to create the Controller part
of a framework. For the Model and the View, it really depends on your personal
taste and I will let you use any existing third-party libraries (Doctrine,
Propel, or plain-old PDO for the Model; PHP or Twig for the View).

When creating a framework, following the MVC pattern is not the right goal.
The main goal should be the Separation of Concerns; I actually think that this
is the only design pattern that you should really care about. The fundamental
principles of the Symfony2 Components are centered around the HTTP
specification. As such, the frameworks that we are going to create should be
more accurately labelled as HTTP frameworks or Request/Response frameworks.

Before we start
---------------

Reading about how to create a framework is not enough. You will have to follow
along and actually type all the examples we will work on. For that, you need a
recent version of PHP (5.3.8 or later is good enough), a web server (like
Apache or NGinx), a good knowledge of PHP and an understanding of Object
Oriented programming.

Ready to go? Let's start.

Bootstrapping
-------------

Before we can even think of creating our first framework, we need to talk
about some conventions: where we will store our code, how we will name our
classes, how we will reference external dependencies, etc.

To store our framework, create a directory somewhere on your machine:

.. code-block: sh

    $ mkdir framework
    $ cd framework

Coding Standards
~~~~~~~~~~~~~~~~

Before anyone starts a flame war about coding standards and why the one used
here suck hard, let's all admit that this does not matter that much as long as
you are consistent. For this book, we are going to use the `Symfony2 Coding
Standards`_.

Components Installation
~~~~~~~~~~~~~~~~~~~~~~~

To install the Symfony2 Components that we need for our framework, we are
going to use `Composer`_, a project dependency manager for PHP. First, list
your dependencies in a ``composer.json`` file:

.. code-block:: json

    # framework/composer.json
    {
        "require": {
            "symfony/class-loader": "2.1.*"
        }
    }

Here, we tell Composer that our project depends on the Symfony2 ClassLoader
component, version 2.1.0 or later. To actually install the project
dependencies, download the composer binary and run it:

.. code-block:: sh

    $ wget http://getcomposer.org/composer.phar
    $ # or
    $ curl -O http://getcomposer.org/composer.phar

    $ php composer.phar install

After running the ``install`` command, you must see a new ``vendor/``
directory that must contain the Symfony2 ClassLoader code.

.. note::

    Even if we highly recommend you the use of Composer, you can also download
    the archives of the components directly or use Git submodules. That's
    really up to you.

Naming Conventions and Autoloading
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We are going to `autoload`_ all our classes. Without autoloading, you need to
require the file where a class is defined before being able to use it. But
with some conventions, we can just let PHP do the hard work for us.

Symfony2 follows the de-facto PHP standard, `PSR-0`_, for class names and
autoloading. The Symfony2 ClassLoader Component provides an autoloader that
implements this PSR-0 standard and most of the time, the Symfony2 ClassLoader
is all you need to autoload all your project classes.

Create and empty autoloader in a new ``autoload.php`` file:

.. code-block:: php

    <?php

    // framework/autoload.php

    require_once __DIR__.'/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

    use Symfony\Component\ClassLoader\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->register();

You can now run the ``autoload.php`` on the CLI, it should not do anything and
should not throw any error:

.. code-block:: sh

    $ php autoload.php

.. tip::

    The Symfony website has more information about the `ClassLoader`_
    component.

Our Project
-----------

Instead of creating our framework from scratch, we are going to write the same
"application" over and over again, adding one abstraction at a time. Let's
start with the simplest web application we can think of in PHP::

    <?php

    $input = $_GET['name'];

    printf('Hello %s', $input);

That's all for the first part of this series. Next time, we will introduce the
HttpFoundation Component and see what it will brings us.

.. _`documentation`:             http://symfony.com/doc
.. _`Silex`:                     http://silex.sensiolabs.org/
.. _`autoload`:                  http://fr.php.net/autoload
.. _`Composer`:                  http://packagist.org/about-composer
.. _`PSR-0`:                     https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
.. _`Symfony2 Coding Standards`: http://symfony.com/doc/current/contributing/code/standards.html
.. _`ClassLoader`:               http://symfony.com/doc/current/components/class_loader.html
