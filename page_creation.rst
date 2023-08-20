.. _creating-pages-in-symfony2:
.. _creating-pages-in-symfony:

Create your First Page in Symfony
=================================

Creating a new page - whether it's an HTML page or a JSON endpoint - is a
two-step process:

#. **Create a controller**: A controller is the PHP function you write that
   builds the page. You take the incoming request information and use it to
   create a Symfony ``Response`` object, which can hold HTML content, a JSON
   string or even a binary file like an image or PDF;

#. **Create a route**: A route is the URL (e.g. ``/about``) to your page and
   points to a controller.

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Harmonious Development with Symfony`_
    screencast series.

.. seealso::

    Symfony *embraces* the HTTP Request-Response lifecycle. To find out more,
    see :doc:`/introduction/http_fundamentals`.

Creating a Page: Route and Controller
-------------------------------------

.. tip::

    Before continuing, make sure you've read the :doc:`Setup </setup>`
    article and can access your new Symfony app in the browser.

Suppose you want to create a page - ``/lucky/number`` - that generates a lucky (well,
random) number and prints it. To do that, create a "Controller" class and a
"controller" method inside of it::

    // src/Controller/LuckyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class LuckyController
    {
        public function number(): Response
        {
            $number = random_int(0, 100);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

.. _annotation-routes:

Now you need to associate this controller function with a public URL (e.g. ``/lucky/number``)
so that the ``number()`` method is called when a user browses to it. This association
is defined with the ``#[Route]`` attribute (in PHP, `attributes`_ are used to add
metadata to code):

.. code-block:: diff

      // src/Controller/LuckyController.php

      // ...
    + use Symfony\Component\Routing\Annotation\Route;

      class LuckyController
      {
    +     #[Route('/lucky/number')]
          public function number(): Response
          {
              // this looks exactly the same
          }
      }

That's it! If you are using Symfony web server, try it out by going to: http://localhost:8000/lucky/number

.. tip::

    Symfony recommends defining routes as attributes to have the controller code
    and its route configuration at the same location. However, if you prefer, you can
    :doc:`define routes in separate files </routing>` using YAML, XML and PHP formats.

If you see a lucky number being printed back to you, congratulations! But before
you run off to play the lottery, check out how this works. Remember the two steps
to create a page?

#. *Create a controller and a method*: This is a function where *you* build the page and ultimately
   return a ``Response`` object. You'll learn more about :doc:`controllers </controller>`
   in their own section, including how to return JSON responses;

#. *Create a route*: In ``config/routes.yaml``, the route defines the URL to your
   page (``path``) and what ``controller`` to call. You'll learn more about :doc:`routing </routing>`
   in its own section, including how to make *variable* URLs.

The bin/console Command
-----------------------

Your project already has a powerful debugging tool inside: the ``bin/console`` command.
Try running it:

.. code-block:: terminal

    $ php bin/console

You should see a list of commands that can give you debugging information, help generate
code, generate database migrations and a lot more. As you install more packages,
you'll see more commands.

To get a list of *all* of the routes in your system, use the ``debug:router`` command:

.. code-block:: terminal

    $ php bin/console debug:router

You should see your ``app_lucky_number`` route in the list:

.. code-block:: terminal

    ----------------  -------  -------  -----  --------------
    Name              Method   Scheme   Host   Path
    ----------------  -------  -------  -----  --------------
    app_lucky_number  ANY      ANY      ANY    /lucky/number
    ----------------  -------  -------  -----  --------------

You will also see debugging routes besides ``app_lucky_number`` -- more on
the debugging routes in the next section.

You'll learn about many more commands as you continue!

.. tip::

    If your shell is supported, you can also set up console completion support.
    This autocompletes commands and other input when using ``bin/console``.
    See :ref:`the Console document <console-completion-setup>` for more
    information on how to set up completion.

.. _web-debug-toolbar:

The Web Debug Toolbar: Debugging Dream
--------------------------------------

One of Symfony's *amazing* features is the Web Debug Toolbar: a bar that displays
a *huge* amount of debugging information along the bottom of your page while
developing. This is all included out of the box using a :ref:`Symfony pack <symfony-packs>`
called ``symfony/profiler-pack``.

You will see a dark bar along the bottom of the page. You'll learn more about
all the information it holds along the way, but feel free to experiment: hover
over and click the different icons to get information about routing,
performance, logging and more.

Rendering a Template
--------------------

If you're returning HTML from your controller, you'll probably want to render
a template. Fortunately, Symfony comes with `Twig`_: a templating language that's
minimal, powerful and actually quite fun.

Install the twig package with:

.. code-block:: terminal

    $ composer require twig

Make sure that ``LuckyController`` extends Symfony's base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController` class:

.. code-block:: diff

      // src/Controller/LuckyController.php

      // ...
    + use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    - class LuckyController
    + class LuckyController extends AbstractController
      {
          // ...
      }

Now, use the handy ``render()`` method to render a template. Pass it a ``number``
variable so you can use it in Twig::

    // src/Controller/LuckyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    // ...

    class LuckyController extends AbstractController
    {
        #[Route('/lucky/number')]
        public function number(): Response
        {
            $number = random_int(0, 100);

            return $this->render('lucky/number.html.twig', [
                'number' => $number,
            ]);
        }
    }

Template files live in the ``templates/`` directory, which was created for you automatically
when you installed Twig. Create a new ``templates/lucky`` directory with a new
``number.html.twig`` file inside:

.. code-block:: html+twig

    {# templates/lucky/number.html.twig #}
    <h1>Your lucky number is {{ number }}</h1>

The ``{{ number }}`` syntax is used to *print* variables in Twig. Refresh your browser
to get your *new* lucky number!

    http://localhost:8000/lucky/number

Now you may wonder where the Web Debug Toolbar has gone: that's because there is
no ``</body>`` tag in the current template. You can add the body element yourself,
or extend ``base.html.twig``, which contains all default HTML elements.

In the :doc:`templates </templates>` article, you'll learn all about Twig: how
to loop, render other templates and leverage its powerful layout inheritance system.

Checking out the Project Structure
----------------------------------

Great news! You've already worked inside the most important directories in your
project:

``config/``
    Contains... configuration!. You will configure routes,
    :doc:`services </service_container>` and packages.

``src/``
    All your PHP code lives here.

``templates/``
    All your Twig templates live here.

Most of the time, you'll be working in ``src/``, ``templates/`` or ``config/``.
As you keep reading, you'll learn what can be done inside each of these.

So what about the other directories in the project?

``bin/``
    The famous ``bin/console`` file lives here (and other, less important
    executable files).

``var/``
    This is where automatically-created files are stored, like cache files
    (``var/cache/``) and logs (``var/log/``).

``vendor/``
    Third-party (i.e. "vendor") libraries live here! These are downloaded via the `Composer`_
    package manager.

``public/``
    This is the document root for your project: you put any publicly accessible files
    here.

And when you install new packages, new directories will be created automatically
when needed.

What's Next?
------------

Congrats! You're already starting to master Symfony and learn a whole new
way of building beautiful, functional, fast and maintainable applications.

OK, time to finish mastering the fundamentals by reading these articles:

* :doc:`/routing`
* :doc:`/controller`
* :doc:`/templates`
* :doc:`/configuration`

Then, learn about other important topics like the
:doc:`service container </service_container>`,
the :doc:`form system </forms>`, using :doc:`Doctrine </doctrine>`
(if you need to query a database) and more!

Have fun!

Go Deeper with HTTP & Framework Fundamentals
--------------------------------------------

.. toctree::
    :maxdepth: 1
    :glob:

    introduction/*

.. _`Twig`: https://twig.symfony.com
.. _`Composer`: https://getcomposer.org
.. _`Harmonious Development with Symfony`: https://symfonycasts.com/screencast/symfony/setup
.. _`attributes`: https://www.php.net/manual/en/language.attributes.overview.php
