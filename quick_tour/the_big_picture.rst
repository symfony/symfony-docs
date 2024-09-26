The Big Picture
===============

Start using Symfony in 10 minutes! Really! That's all you need to understand the
most important concepts and start building a real project!

If you've used a web framework before, you should feel right at home with
Symfony. If not, welcome to a whole new way of developing web applications. Symfony
*embraces* best practices, keeps backwards compatibility (Yes! Upgrading is always
safe & easy!) and offers long-term support.

.. _installing-symfony2:

Downloading Symfony
-------------------

First, make sure you've installed `Composer`_ and have PHP 8.1 or higher.

Ready? In a terminal, run:

.. code-block:: terminal

    $ composer create-project symfony/skeleton quick_tour

This creates a new ``quick_tour/`` directory with a small, but powerful new
Symfony application:

.. code-block:: text

    quick_tour/
    ├─ .env
    ├─ bin/console
    ├─ composer.json
    ├─ composer.lock
    ├─ config/
    ├─ public/index.php
    ├─ src/
    ├─ symfony.lock
    ├─ var/
    └─ vendor/

Can we already load the project in a browser? Yes! You can set up
:doc:`Nginx or Apache </setup/web_server_configuration>` and configure their
document root to be the ``public/`` directory. But, for development, it's better
to :doc:`install the Symfony local web server </setup/symfony_server>` and run
it as follows:

.. code-block:: terminal

    $ symfony server:start

Try your new app by going to ``http://localhost:8000`` in a browser!

.. image:: /_images/quick_tour/no_routes_page.png
    :alt: The default Symfony welcome page.
    :class: with-browser

Fundamentals: Route, Controller, Response
-----------------------------------------

Our project only has about 15 files, but it's ready to become a sleek API, a robust
web app, or a microservice. Symfony starts small, but scales with you.

But before we go too far, let's dig into the fundamentals by building our first page.

In ``src/Controller``, create a new ``DefaultController`` class and an ``index``
method inside::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class DefaultController
    {
        #[Route('/', name: 'index')]
        public function index(): Response
        {
            return new Response('Hello!');
        }
    }

That's it! Try going to the homepage: ``http://localhost:8000/``. Symfony sees
that the URL matches our route and then executes the new ``index()`` method.

A controller is just a normal function with *one* rule: it must return a Symfony
``Response`` object. But that response can contain anything: simple text, JSON or
a full HTML page.

But the routing system is *much* more powerful. So let's make the route more interesting:

.. code-block:: diff

      // src/Controller/DefaultController.php
      namespace App\Controller;

      use Symfony\Component\HttpFoundation\Response;
      use Symfony\Component\Routing\Attribute\Route;

      class DefaultController
      {
    -     #[Route('/', name: 'index')]
    +     #[Route('/hello/{name}', name: 'index')]
          public function index(): Response
          {
              return new Response('Hello!');
          }
      }

The URL to this page has changed: it is *now* ``/hello/*``: the ``{name}`` acts
like a wildcard that matches anything. And it gets better! Update the controller too:

.. code-block:: diff

      <?php
      // src/Controller/DefaultController.php
      namespace App\Controller;

      use Symfony\Component\HttpFoundation\Response;
      use Symfony\Component\Routing\Attribute\Route;

      class DefaultController
      {
          #[Route('/hello/{name}', name: 'index')]
    -     public function index()
    +     public function index(string $name): Response
          {
    -         return new Response('Hello!');
    +         return new Response("Hello $name!");
          }
      }

Try the page out by going to ``http://localhost:8000/hello/Symfony``. You should
see: Hello Symfony! The value of the ``{name}`` in the URL is available as a ``$name``
argument in your controller.

But by using attributes, the route and controller live right next to each
other. Need another page? Add another route and method in ``DefaultController``::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class DefaultController
    {
        // ...

        #[Route('/simplicity', methods: ['GET'])]
        public function simple(): Response
        {
            return new Response('Simple! Easy! Great!');
        }
    }

Routing can do *even* more, but we'll save that for another time! Right now, our
app needs more features! Like a template engine, logging, debugging tools and more.

Keep reading with :doc:`/quick_tour/flex_recipes`.

.. _`Composer`: https://getcomposer.org/
