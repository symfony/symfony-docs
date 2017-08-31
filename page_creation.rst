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
"controller" method inside of it that will be executed when someone goes to
``/lucky/number``::

    <?php
    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;

    class LuckyController
    {
        /**
         * @Route("/lucky/number")
         */
        public function numberAction()
        {
            $number = mt_rand(0, 100);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

Before diving into this, test it out! If you are using PHP's internal web server
go to:

    http://localhost:8000/lucky/number

If you see a lucky number being printed back to you, congratulations! But before
you run off to play the lottery, check out how this works. Remember the two steps
to creating a page?

#. *Create a route*: The ``@Route`` above ``numberAction()`` is the *route*: it
   defines the URL pattern for this page. You'll learn more about :doc:`routing </routing>`
   in its own section, including how to make *variable* URLs;

#. *Create a controller*: The method below the route - ``numberAction()`` - is called
   the *controller*. This is a function where *you* build the page and ultimately
   return a ``Response`` object. You'll learn more about :doc:`controllers </controller>`
   in their own section, including how to return JSON responses.

The Web Debug Toolbar: Debugging Dream
--------------------------------------

If your page is working, then you should *also* see a bar along the bottom of your
browser. This is called the Web Debug Toolbar: and it's your debugging best friend.
You'll learn more about all the information it holds along the way, but feel free
to experiment: hover over and click the different icons to get information about
routing, performance, logging and more.

Rendering a Template (with the Service Container)
-------------------------------------------------

If you're returning HTML from your controller, you'll probably want to render
a template. Fortunately, Symfony comes with `Twig`_: a templating language that's
easy, powerful and actually quite fun.

First, make sure that ``LuckyController`` extends Symfony's base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class::

    // src/AppBundle/Controller/LuckyController.php

    // ...
    // --> add this new use statement
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        // ...
    }

Now, use the handy ``render()`` function to render a template. Pass it our ``number``
variable so we can render that::

    <?php
    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        /**
         * @Route("/lucky/number")
         */
        public function numberAction()
        {
            $number = mt_rand(0, 100);

            return $this->render('lucky/number.html.twig', array(
                'number' => $number,
            ));
        }
    }

Finally, template files should live in the ``app/Resources/views`` directory. Create
a new ``app/Resources/views/lucky`` directory with a new ``number.html.twig`` file
inside:

.. code-block:: twig

    {# app/Resources/views/lucky/number.html.twig #}

    <h1>Your lucky number is {{ number }}</h1>

The ``{{ number }}`` syntax is used to *print* variables in Twig. Refresh your browser
to get your *new* lucky number!

    http://localhost:8000/lucky/number

In the :doc:`/templating` article, you'll learn all about Twig: how to loop, render
other templates and leverage its powerful layout inheritance system.

Checking out the Project Structure
----------------------------------

Great news! You've already worked inside the two most important directories in your
project:

``app/``
    Contains things like configuration and templates. Basically, anything
    that is *not* PHP code goes here.

``src/``
    Your PHP code lives here.

99% of the time, you'll be working in ``src/`` (PHP files) or ``app/`` (everything
else). As you keep reading, you'll learn what can be done inside each of these.

So what about the other directories in the project?

``bin/``
    The famous ``bin/console`` file lives here (and other, less important
    executable files).

``tests/``
    The automated tests (e.g. Unit tests) for your application live here.

``var/``
    This is where automatically-created files are stored, like cache files
    (``var/cache/``), logs (``var/logs/``) and sessions (``var/sessions/``).

``vendor/``
    Third-party (i.e. "vendor") libraries live here! These are downloaded via the `Composer`_
    package manager.

``web/``
    This is the document root for your project: put any publicly accessible files
    here (e.g. CSS, JS and images).

Bundles & Configuration
-----------------------

Your Symfony application comes pre-installed with a collection of *bundles*, like
``FrameworkBundle`` and ``TwigBundle``. Bundles are similar to the idea of a *plugin*,
but with one important difference: *all* functionality in a Symfony application comes
from a bundle.

Bundles are registered in your ``app/AppKernel.php`` file (a rare PHP file in the
``app/`` directory) and each gives you more *tools*, sometimes called *services*::

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new Symfony\Bundle\TwigBundle\TwigBundle(),
                // ...
            );
            // ...

            return $bundles;
        }

        // ...
    }

For example, ``TwigBundle`` is responsible for adding the Twig tool to your app!

Eventually, you'll download and add more third-party bundles to your app in order
to get even more tools. Imagine a bundle that helps you create paginated lists.
That exists!

You can control how your bundles behave via the ``app/config/config.yml`` file.
That file - and other details like environments & parameters - are discussed in
the :doc:`/configuration` article.

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
