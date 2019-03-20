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

First, make sure you've installed `Composer`_ and have PHP 7.1.3 or higher.

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

Can we already load the project in a browser? Of course! You can setup
:doc:`Nginx or Apache </setup/web_server_configuration>` and configure their document
root to be the ``public/`` directory. But, for development, Symfony has its own server.
Install and run it with:

.. code-block:: terminal

    $ composer require --dev server
    $ php bin/console server:start

Try your new app by going to ``http://localhost:8000`` in a browser!

.. image:: /_images/quick_tour/no_routes_page.png
   :align: center
   :class: with-browser

Fundamentals: Route, Controller, Response
-----------------------------------------

Our project only has about 15 files, but it's ready to become a sleek API, a robust
web app, or a microservice. Symfony starts small, but scales with you.

But before we go too far, let's dig into the fundamentals by building our first page.

Start in ``config/routes.yaml``: this is where *we* can define the URL to our new
page. Uncomment the example that already lives in the file:

.. code-block:: yaml

    # config/routes.yaml
    index:
        path: /
        controller: 'App\Controller\DefaultController::index'

This is called a *route*: it defines the URL to your page (``/``) and the "controller":
the *function* that will be called whenever anyone goes to this URL. That function
doesn't exist yet, so let's create it!

In ``src/Controller``, create a new ``DefaultController`` class and an ``index``
method inside::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class DefaultController
    {
        public function index()
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

    # config/routes.yaml
    index:
    -     path: /
    +     path: /hello/{name}
        controller: 'App\Controller\DefaultController::index'

The URL to this page has changed: it is *now* ``/hello/*``: the ``{name}`` acts
like a wildcard that matches anything. And it gets better! Update the controller too:

.. code-block:: diff

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class DefaultController
    {
    -     public function index()
    +     public function index($name)
        {
    -         return new Response('Hello!');
    +         return new Response("Hello $name!");
        }
    }

Try the page out by going to ``http://localhost:8000/hello/Symfony``. You should
see: Hello Symfony! The value of the ``{name}`` in the URL is available as a ``$name``
argument in your controller.

But this can be even simpler! So let's install annotations support:

.. code-block:: terminal

    $ composer require annotations

Now, comment-out the YAML route by adding the ``#`` character:

.. code-block:: yaml

    # config/routes.yaml
    # index:
    #     path: /hello/{name}
    #     controller: 'App\Controller\DefaultController::index'

Instead, add the route *right above* the controller method:

.. code-block:: diff

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    + use Symfony\Component\Routing\Annotation\Route;

    class DefaultController
    {
    +    /**
    +     * @Route("/hello/{name}")
    +     */
         public function index($name) {
             // ...
         }
    }

This works just like before! But by using annotations, the route and controller
live right next to each other. Need another page? Add another route and method
in ``DefaultController``::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController
    {
        // ...

        /**
         * @Route("/simplicity")
         */
        public function simple()
        {
            return new Response('Simple! Easy! Great!');
        }
    }

Routing can do *even* more, but we'll save that for another time! Right now, our
app needs more features! Like a template engine, logging, debugging tools and more.

Keep reading with :doc:`/quick_tour/flex_recipes`.

.. _`Composer`: https://getcomposer.org/
