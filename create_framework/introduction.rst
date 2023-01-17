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
look around, everybody will tell you that it is a bad thing to reinvent the
wheel and that you would better choose an existing framework and forget about
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

And each step will be the occasion to learn more about some of the Symfony
Components.

Many modern web frameworks advertise themselves as being MVC frameworks. This
tutorial will not talk about the MVC pattern, as the Symfony Components are able to
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
you need a recent version of PHP (7.4 or later is good enough), a web server
(like Apache, nginx or PHP's built-in web server), a good knowledge of PHP and
an understanding of Object Oriented Programming.

Ready to go? Read on!

Bootstrapping
-------------

Before you can even think of creating the first framework, you need to think
about some conventions: where you will store the code, how you will name the
classes, how you will reference external dependencies, etc.

To store your new framework, create a directory somewhere on your machine:

.. code-block:: terminal

    $ mkdir framework
    $ cd framework

Dependency Management
~~~~~~~~~~~~~~~~~~~~~

To install the Symfony Components that you need for your framework, you are going
to use `Composer`_, a project dependency manager for PHP. If you don't have it
yet, `download and install Composer`_ now.

Our Project
-----------

Instead of creating our framework from scratch, we are going to write the same
"application" over and over again, adding one abstraction at a time. Let's
start with the simplest web application we can think of in PHP::

    // framework/index.php
    $name = $_GET['name'];

    printf('Hello %s', $name);

You can use the :doc:`Symfony Local Web Server </setup/symfony_server>` to test
this great application in a browser
(``http://localhost:8000/index.php?name=Fabien``):

.. code-block:: terminal

    $ symfony server:start

In the :doc:`next chapter </create_framework/http_foundation>`, we are going to
introduce the HttpFoundation Component and see what it brings us.

.. _`Symfony`: https://symfony.com/
.. _`Composer`: https://getcomposer.org/
.. _`download and install Composer`: https://getcomposer.org/download/
