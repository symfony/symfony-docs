.. index::
   single: Create your First Page in Symfony

.. _creating-pages-in-symfony2:
.. _creating-pages-in-symfony:

Create your First Page in Symfony
=================================

Creating a new page - whether it's an HTML page or a JSON endpoint - is a
simple two-step process:

#. **Create a route**: A route is the URL (e.g. ``/about``) to your page and
   points to a controller;

#. **Create a controller**: A controller is the PHP function you write that
   builds the page. You take the incoming request information and use it to
   create a Symfony ``Response`` object, which can hold HTML content, a JSON
   string or even a binary file like an image or PDF.

.. seealso::

    Do you prefer video tutorials? Check out the `Joyful Development with Symfony`_
    screencast series from KnpUniversity.

.. seealso::

    Symfony *embraces* the HTTP Request-Response lifecycle. To find out more,
    see :doc:`/introduction/http_fundamentals`.

.. index::
   single: Page creation; Example

Creating a Page: Route and Controller
-------------------------------------

.. tip::

    Before continuing, make sure you've read the :doc:`Setup </setup>`
    article and can access your new Symfony app in the browser.

Suppose you want to create a page - ``/lucky/number`` - that generates a lucky (well,
random) number and prints it. To do that, create a "Controller class" and a
"controller" method inside of it::

    // src/Controller/LuckyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class LuckyController
    {
        public function number()
        {
            $number = mt_rand(0, 100);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

To map a URL to this controller, create a route in ``config/routes.yaml``:

.. code-block:: yml

    # config/routes.yaml

    # the "app_lucky_number" route name is not important yet
    app_lucky_number:
        path: /lucky/number
        controller: App\Controller\LuckyController::number

That's it! If you are using Symfony web server, try it out by going to:

    http://localhost:8000/lucky/number

If you see a lucky number being printed back to you, congratulations! But before
you run off to play the lottery, check out how this works. Remember the two steps
to creating a page?

#. *Create a route*: In ``config/routes.yaml``, the route defines the URL to your
    page (``path``) and what ``controller`` to call. You'll learn more about :doc:`routing </routing>`
    in its own section, including how to make *variable* URLs;

#. *Create a controller*: This is a function where *you* build the page and ultimately
   return a ``Response`` object. You'll learn more about :doc:`controllers </controller>`
   in their own section, including how to return JSON responses.

Annotation Routes
-----------------

Instead of defining your route in YAML, Symfony also allows you to use *annotation*
routes. First, install the annotations package:

.. code-block:: terminal

    $ composer require annotations

Then, in ``config/routes.yaml``, remove the route we just created and uncomment
the annotation route import at the bottom:

.. code-block:: yaml

    controllers:
        resource: ../src/Controller/
        type: annotation

After this one-time setup, you can nowadd your route directly *above* the controller:

.. code-block:: diff

    // src/Controller/LuckyController.php
    // ...

    + use Symfony\Component\Routing\Annotation\Route;

    + /**
    +  * @Route("/lucky/number")
    +  */
    class LuckyController
    {
        public function number()
        {
            // this looks exactly the same
        }
    }

That's it! The page - ``http://localhost:8000/lucky/number`` will work exactly
like before! Annotations are the recommended way to configure routes.

Auto-Installing Recipes with Symfony Flex
-----------------------------------------

You may not have noticed, but when you ran ``composer require annotations``, two
special things happened, both thanks to a powerful Composer plugin called
:doc:`Flex </setup/flex>`.

First, ``annotations`` isn't a real package name: it's an *alias* (i.e. shortcut)
that Flex resolves to ``sensio/framework-extra-bundle``.

Second, after this package was downloaded, Flex executed a *recipe*, which automatically
enabled the bundle. Flex recipes exist for many packages (not just bundles) and have
have the ability to do a lot, like adding configuration files, creating directories,
updating ``.gitignore`` and adding new config to your ``.env`` file. Flex *automates*
the installation of packages so you can get back to coding.

You can learn more about Flex by reading ":doc:`/setup/flex`". But that's not necessary:
Flex works automatically in the background when you add packages.

The bin/console Utility
-----------------------

Your project already has a powerful debugging tool inside: the ``bin/console`` command.
Try running it:

.. code-block:: terminal

    php bin/console

You should see a list of commands that can give you debugging information, help generate
code, generate database migrations and a lot more. As you install more packages,
you'll see more commands.

To get a list of *all* of the routes in your system, use the ``debug:router`` command:

.. code-block:: terminal

    php bin/console debug:router

You'll learn about many more commands as you continue!

The Web Debug Toolbar: Debugging Dream
--------------------------------------

One of Symfony's *killer* features is the Web Debug Toolbar: a bar that displays
a *huge* amount of debugging information along the bottom of your page while developing.

To use the web debug toolbar, just install it:

.. code-block:: terminal

    $ composer require profiler

As soon as this finishes, refresh your page. You should see a black bar along the
bottom of the page. You'll learn more about all the information it holds along the
way, but feel free to experiment: hover over and click the different icons to get
information about routing, performance, logging and more.

The ``profiler`` package is also a great example of Flex! After downloading the
package, the recipe created several configuration files so that the web debug toolbar
worked instantly.

Rendering a Template
--------------------

If you're returning HTML from your controller, you'll probably want to render
a template. Fortunately, Symfony comes with `Twig`_: a templating language that's
easy, powerful and actually quite fun.

First, install Twig::

.. code-block:: terminal

    $ composer require twig

Second, make sure that ``LuckyController`` extends Symfony's base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class::

    // src/Controller/LuckyController.php

    // ...
    // --> add this new use statement
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        // ...
    }

Now, use the handy ``render()`` function to render a template. Pass it a ``number``
variable so you can use it in Twig::

    // src/Controller/LuckyController.php

    // ...
    class LuckyController extends Controller
    {
        /**
         * @Route("/lucky/number")
         */
        public function number()
        {
            $number = mt_rand(0, 100);

            return $this->render('lucky/number.html.twig', array(
                'number' => $number,
            ));
        }
    }

Template files live in the ``templates/`` directory, which was created for your automatically
when you installed Twig. Create a new ``templates/lucky`` directory with a new 
``number.html.twig`` file inside:

.. code-block:: twig

    {# templates/lucky/number.html.twig #}

    <h1>Your lucky number is {{ number }}</h1>

The ``{{ number }}`` syntax is used to *print* variables in Twig. Refresh your browser
to get your *new* lucky number!

    http://localhost:8000/lucky/number

In the :doc:`/templating` article, you'll learn all about Twig: how to loop, render
other templates and leverage its powerful layout inheritance system.

Checking out the Project Structure
----------------------------------

Great news! You've already worked inside the most important directories in your
project:

``config/``
    Contains... configuration of course!. You will configure routes, :doc:`services </service_container>`
    and pckages.

``src/``
    All your PHP code lives here.

99% of the time, you'll be working in ``src/`` (PHP files) or ``config/`` (everything
else). As you keep reading, you'll learn what can be done inside each of these.

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

What's Next?
------------

Congrats! You're already starting to master Symfony and learn a whole new
way of building beautiful, functional, fast and maintainable apps.

Ok, time to finish mastering the fundamentals by reading these articles:

* :doc:`/routing`
* :doc:`/controller`
* :doc:`/templating`

Then, learn about other important topics like the
:doc:`service container </service_container>`,
the :doc:`form system </forms>`, using :doc:`Doctrine </doctrine>`
(if you need to query a database) and more!

Have fun!

Go Deeper with HTTP & Framework Fundamentals
--------------------------------------------

.. toctree::
    :hidden:

    routing


.. toctree::
    :maxdepth: 1
    :glob:

    introduction/*

.. _`Twig`: http://twig.sensiolabs.org
.. _`Composer`: https://getcomposer.org
.. _`Joyful Development with Symfony`: http://knpuniversity.com/screencast/symfony/first-page
