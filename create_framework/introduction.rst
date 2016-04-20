Introduction
============

`Symfony`_ is a reusable set of standalone, decoupled and cohesive PHP
components that solve common web development problems.

Instead of using these low-level components, you can use the ready-to-be-used
Symfony full-stack web framework, which is based on these components... or
you can create your very own framework. This tutorial is about the latter.

Why would you Like to Create your Own Framework?
------------------------------------------------

Why would you like to create your own framework in the first place? If you
look around, everybody will tell you that it's a bad thing to reinvent the
wheel and that you'd better choose an existing framework and forget about
creating your own altogether. Most of the time, they are right but there are
a few good reasons to start creating your own framework:

* To learn more about the low level architecture of modern web frameworks in
  general and about the Symfony full-stack framework internals in particular;

* To create a framework tailored to your very specific needs (just be sure
  first that your needs are really specific);

* To experiment creating a framework for fun (in a learn-and-throw-away
  approach);

* To refactor an old/existing application that needs a good dose of recent web
  development best practices;

* To prove the world that you can actually create a framework on your own (...
  but with little effort).

This tutorial will gently guide you through the creation of a web framework,
one step at a time. At each step, you will have a fully-working framework that
you can use as is or as a start for your very own. It will start with a simple
framework and more features will be added with time. Eventually, you will have
a fully-featured full-stack web framework.

And of course, each step will be the occasion to learn more about some of the
Symfony Components.

.. tip::

    If you don't have time to read the whole book, or if you want to get
    started fast, you can also have a look at `Silex`_, a micro-framework
    based on the Symfony Components. The code is rather slim and it leverages
    many aspects of the Symfony Components.

Many modern web frameworks advertize themselves as being MVC frameworks. This
tutorial won't talk about the MVC pattern, as the Symfony Components are able to
create any type of frameworks, not just the ones that follow the MVC
architecture. Anyway, if you have a look at the MVC semantics, this book is
about how to create the Controller part of a framework. For the Model and the
View, it really depends on your personal taste and you can use any existing
third-party libraries (Doctrine, Propel or plain-old PDO for the Model; PHP or
Twig for the View).

When creating a framework, following the MVC pattern is not the right goal. The
main goal should be the **Separation of Concerns**; this is probably the only
design pattern that you should really care about. The fundamental principles of
the Symfony Components are focused on the HTTP specification. As such, the
framework that you are going to create should be more accurately labelled as a
HTTP framework or Request/Response framework.

Before You Start
----------------

Reading about how to create a framework is not enough. You will have to follow
along and actually type all the examples included in this tutorial. For that,
you need a recent version of PHP (5.5.9 or later is good enough), a web server
(like Apache, NGinx or PHP's built-in web server), a good knowledge of PHP and
an understanding of Object Oriented programming.

Ready to go? Read on!

Bootstrapping
-------------

Before you can even think of creating the first framework, you need to think
about some conventions: where you will store the code, how you will name the
classes, how you will reference external dependencies, etc.

To store your new framework, create a directory somewhere on your machine:

.. code-block:: bash

    $ mkdir framework
    $ cd framework

Dependency Management
~~~~~~~~~~~~~~~~~~~~~

To install the Symfony Components that you need for your framework, you are going
to use `Composer`_, a project dependency manager for PHP. If you don't have it
yet, :doc:`download and install Composer </cookbook/composer>` now.

Our Project
-----------

Instead of creating our framework from scratch, we are going to write the same
"application" over and over again, adding one abstraction at a time. Let's
start with the simplest web application we can think of in PHP::

    // framework/index.php
    $input = $_GET['name'];

    printf('Hello %s', $input);

You can use the PHP built-in server to test this great application in a browser
(``http://localhost:4321/index.php?name=Fabien``):

.. code-block:: bash

    $ php -S 127.0.0.1:4321

Otherwise, you can always use your own server (Apache, Nginx, etc.).

In the :doc:`next chapter </create_framework/http_foundation>`, we are going to
introduce the HttpFoundation Component and see what it brings us.

.. _`Symfony`: http://symfony.com/
.. _`Silex`: http://silex.sensiolabs.org/
.. _`Composer`: http://packagist.org/about-composer
