.. index::
   single: Create your First Page in Symfony

.. _creating-pages-in-symfony2:
.. _creating-pages-in-symfony:

Create your First Page in Symfony
=================================

Creating a new page - whether it's an HTML page or a JSON endpoint - is a
simple two-step process:

#. *Create a route*: A route is the URL (e.g. ``/about``) to your page and
   points to a controller;

#. *Create a controller*: A :term:`controller` is the PHP function you write that
   builds the page. You take the incoming request information and use it to
   create a Symfony ``Response`` object, which can hold HTML content, a JSON
   string or even a binary file like an image or PDF. The only rule is that
   a controller *must* return a Symfony
   :ref:`Response <component-http-foundation-response>` object (and you'll even
   learn to bend this rule eventually).

Just like on the web, every interaction is initiated by an HTTP request.
Your job is pure and simple: understand that request and return a response.

Creating a Page: Route and Controller
-------------------------------------

.. tip::

    Before continuing, make sure you've read the
    :doc:`Installation </book/installation>` chapter, installed
    `Symfony Standard Edition`_ and can access your new Symfony app in the
    browser either using PHP's internal web server or by setting proper
    virtual host in Apache or Nginx.

    Using PHP's internal web server is only suitable while developing the
    application. In order to run Symfony applications on production servers,
    you'll have to configure your Apache or Nginx web server and virtual
    host as explained in :doc:`/cookbook/configuration/web_server_configuration`.

Suppose you want to create a page ``/lucky/number`` that generates a lucky (well,
random) number and prints it. To do that, create a "Controller class" and a
"controller" method inside of it that will be executed when someone goes to
``/lucky/number``::

.. configuration-block::

    .. code-block:: php-annotations

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
                $number = rand(0, 100);

                return new Response(
                    '<html><body>Lucky number: '.$number.'</body></html>'
                );
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        lucky_number:
            path:     /lucky/number
            defaults: { _controller: AppBundle:Lucky:number }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="lucky_number" path="/lucky/number">
                <default key="_controller">AppBundle:Lucky:number</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('lucky_number', new Route('/lucky/number', array(
            '_controller' => 'AppBundle:Lucky:number',
        )));

        return $collection;

"Controller class" is a convenient way to group several "controllers" together.

Before diving into this, test it out! If you are using PHP's internal web server
go to:

    http://localhost:8000/lucky/number

If you set up a proper virtual host in Apache or Nginx replace ``http://localhost:8000``
with your host name and add the name of the development front controller file you
are using ``web/app_dev.php``:

    http://symfony.dev/app_dev.php/lucky/number

If you see a lucky number being printed back to you, congratulations! But before
you run off to play the lottery, check out how this works.

The ``@Route`` above ``numberAction()`` method is called an :term:`annotation`
and defines the route so that :doc:`Router </book/routing>` can match the incoming URL
(``http://localhost:8000/lucky/number``) to a specific route (``/lucky/number``)
and therefore returns information about the route, including the controller method
that should be executed. The correct controller from the matched route is executed
and the code inside the controller creates and returns the appropriate ``Response``
object.

.. note::

    You can also write routes in YAML, XML or PHP: you will learn about
    this in the :doc:`Routing </book/routing>` chapter. If you look closer
    the above example has tabs that show you how each format looks - all three additional
    formats use routing configuration file to define routes. Actually, most
    routing examples in the docs have tabs that show you how each format looks.

Creating a JSON Response
~~~~~~~~~~~~~~~~~~~~~~~~

The ``Response`` object you return in your controller can contain HTML, JSON
or even a binary file like an image or PDF. You can easily set HTTP headers
or the status code.

Suppose you want to create a JSON endpoint that returns the lucky number.
Just add a second method to ``LuckyController``::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    class LuckyController
    {
        // ...

        /**
         * @Route("/api/lucky/number")
         */
        public function apiNumberAction()
        {
            $data = array(
                'lucky_number' => rand(0, 100),
            );

            return new Response(
                json_encode($data),
                200,
                array('Content-Type' => 'application/json')
            );
        }
    }

Try this out in your browser:

    http://localhost:8000/api/lucky/number

You can even shorten this with the handy
:class:`Symfony\\Component\\HttpFoundation\\JsonResponse`::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    // --> don't forget this new use statement
    use Symfony\Component\HttpFoundation\JsonResponse;

    class LuckyController
    {
        // ...

        /**
         * @Route("/api/lucky/number")
         */
        public function apiNumberAction()
        {
            $data = array(
                'lucky_number' => rand(0, 100),
            );

            // calls json_encode() and sets the Content-Type header
            return new JsonResponse($data);
        }
    }

Dynamic URL Patterns: /lucky/number/{count}
-------------------------------------------

Woh, you're doing great! But Symfony's routing can do a lot more. Suppose
now that you want a user to be able to go to ``/lucky/number/5`` to generate
*5* lucky numbers at once. Update the route to have a "wildcard" placeholders
``{counter}`` at the end::

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/LuckyController.php
        // ...

        class LuckyController
        {
            /**
             * @Route("/lucky/number/{count}")
             */
            public function numberAction()
            {
                // ...
            }

            // ...
        }

    .. code-block:: yaml

        # app/config/routing.yml
        lucky_number:
            path:     /lucky/number/{count}
            defaults: { _controller: AppBundle:Lucky:number }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="lucky_number" path="/lucky/number/{count}">
                <default key="_controller">AppBundle:Lucky:number</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('lucky_number', new Route('/lucky/number/{count}', array(
            '_controller' => 'AppBundle:Lucky:number',
        )));

        return $collection;

Because of the ``{count}`` "wildcard" placeholders, the URL to the page is *different*:
it now works for URLs matching ``/lucky/number/*`` - for example ``/lucky/number/5``.
The best part is that you can access this value and use it in your controller::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    class LuckyController
    {

        /**
         * @Route("/lucky/number/{count}")
         */
        public function numberAction($count)
        {
            $numbers = array();
            for ($i = 0; $i < $count; $i++) {
                $numbers[] = rand(0, 100);
            }
            $numbersList = implode(', ', $numbers);

            return new Response(
                '<html><body>Lucky numbers: '.$numbersList.'</body></html>'
            );
        }

        // ...
    }

Try it by printing *7* lucky numbers:

    http://localhost:8000/lucky/number/7

**You can get the value of any ``{placeholder}`` in your route by adding
a ``$placeholder`` argument to your controller. Just make sure they have
the same name.**

The routing system can do a *lot* more, like supporting multiple placeholders
(e.g. ``/blog/{category}/{page})``), making placeholders optional and forcing
placeholder to match a regular expression (e.g. so that ``{count}`` *must*
be a number). Find out about all of this and become a routing expert in the
:doc:`Routing </book/routing>` chapter.

Rendering a Template (with the Service Container)
-------------------------------------------------

If you're returning HTML from your controller, you'll probably want to render
a template. Fortunately, Symfony comes with `Twig`_: a templating language that's
easy, powerful and actually quite fun.

So far, ``LuckyController`` doesn't extend any base class. The easiest way
to use Twig - or many other tools in Symfony - is to extend Symfony's base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    // --> add this new use statement
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        // ...
    }

Using the ``templating`` Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This doesn't change anything, but it *does* give you access to Symfony's
:doc:`service container </book/service_container>`: an array-like object that
gives you access to *every* useful object in the system. These useful objects are
called *services*, and Symfony ships with a service object that can render
Twig templates, another that can log messages and many more.

To render a Twig template, use a service called ``templating``::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    class LuckyController extends Controller
    {
        /**
         * @Route("/lucky/number/{count}")
         */
        public function numberAction($count)
        {
            // ...
            $numbersList = implode(', ', $numbers);

            $html = $this->container->get('templating')->render(
                'lucky/number.html.twig',
                array('luckyNumberList' => $numbersList)
            );

            return new Response($html);
        }

        // ...
    }

You'll learn a lot more about the important "service container" as you keep
reading. For now, you just need to know that it holds a lot of objects, and
you can :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::get`
any object by using its nickname, like ``templating`` or ``logger``.
The ``templating`` service is an instance of
:class:`Symfony\\Bundle\\TwigBundle\\TwigEngine` and this has a
:method:`Symfony\\Bundle\\TwigBundle\\TwigEngine::render` method.

But this can get even easier! By extending the ``Controller`` class, you
also get a lot of shortcut methods, like
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::render`::

    // src/AppBundle/Controller/LuckyController.php
    // ...

    /**
     * @Route("/lucky/number/{count}")
     */
    public function numberAction($count)
    {
        // ...

        /*
        $html = $this->container->get('templating')->render(
            'lucky/number.html.twig',
            array('luckyNumberList' => $numbersList)
        );

        return new Response($html);
        */

        // render(): a shortcut that does the same as above
        return $this->render(
            'lucky/number.html.twig',
            array('luckyNumberList' => $numbersList)
        );
    }

You will learn more about these shortcut methods and how they work in the
:doc:`Controller </book/controller>` chapter.

Create the Template
~~~~~~~~~~~~~~~~~~~

If you refresh your browser now, you'll get an error:

    Unable to find template "lucky/number.html.twig"

Fix that by creating a new ``app/Resources/views/lucky`` directory and putting
a ``number.html.twig`` file inside of it::

.. configuration-block::

    .. code-block:: twig

        {# app/Resources/views/lucky/number.html.twig #}
        {% extends 'base.html.twig' %}

        {% block body %}
            <h1>Lucky Numbers: {{ luckyNumberList }}</h1>
        {% endblock %}

    .. code-block:: html+php

        <!-- app/Resources/views/lucky/number.html.php -->
        <?php $view->extend('base.html.php') ?>

        <?php $view['slots']->start('body') ?>
            <h1>Lucky Numbers: <?php echo $view->escape($luckyNumberList) ?>
        <?php $view['slots']->stop() ?>

Welcome to Twig! This simple file already shows off the basics:

* ``{{ variableName }}`` syntax is used to print a variable that you're
  passing into the template from the array list in ``render()`` method in your
  controller.

* The ``{% extends 'base.html.twig' %}`` points to a layout file that lives
  at `app/Resources/views/base.html.twig`_ and came with your new project
  Symfony Standard Edition. It's *really* basic (an unstyled HTML structure)
  and it's yours to customize.

* The ``{% block body %}`` part uses Twig's :ref:`inheritance system <twig-inheritance>`
  to put the content into the middle of the ``base.html.twig`` layout.

Refresh to see your template in action!

    http://localhost:8000/lucky/number/7

If you view the source code of the displayed page, you now have a basic HTML
structure thanks to ``base.html.twig``.

This is just the surface of Twig's power. When you're ready to master its
syntax, loop over arrays, render other templates and other cool things, read
the :doc:`Templating </book/templating>` chapter.

Exploring the Project
---------------------

You've already created a flexible URL, rendered a template that uses inheritance
and created a JSON endpoint. Nice!

It's time to explore and demystify the files in your project. You've already
worked inside the two most important directories:

``app/``
    Contains things like configuration and templates. Basically, anything
    that is *not* PHP code goes here.

``src/``
    Your PHP code lives here.

99% of the time, you'll be working in ``src/`` (PHP files) or ``app/`` (everything
else). As you get more advanced, you'll learn what can be done inside each
of these.

The ``app/`` directory also holds a few other things, like the cache directory
``app/cache/``, the logs directory ``app/logs/`` and ``app/AppKernel.php``,
which you'll use to enable new bundles (and one of a *very* short list of
PHP files in ``app/``).

The ``src/`` directory has just one directory ``src/AppBundle`` and everything
lives inside of it.  Is where your code lives. It's a set of files within a
directory that implement a single feature. Symfony Standard Edition comes with
``AppBundle`` but there is nothing special about ``AppBundle``. Your project can
have as many bundles as you want, you can even use third-party bundles written
by other that you can find at `KnpBundles.com`_. So, a :term:`bundle` is like a
"plugin" in other software, but even better. To find out more about bundles and
why you might create multiple bundles (hint: sharing code between projects), see
the :doc:`Bundles </book/bundles>` chapter.

So what about the other directories in the project?

``vendor/``
    Location to which vendor (i.e. third-party) libraries and bundles are
    downloaded by the `Composer`_ package manager.

``web/``
    This is the document root for the project and contains any publicly accessible
    files, like CSS, images and the Symfony development and production front controllers
    that execute the app (``app_dev.php`` and ``app.php``).

.. seealso::

    Symfony is flexible. If you need to, you can easily override the default
    directory structure. See cookbook article
    :doc:`/cookbook/configuration/override_dir_structure`.

Application Configuration
-------------------------

Symfony Standard Edition comes with several built-in bundles (open your
``app/AppKernel.php`` file) and you'll probably install more. The main configuration
file for bundles is ``app/config/config.yml``::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        # ...

        framework:
            secret: '%secret%'
            router:
                resource: '%kernel.root_dir%/config/routing.yml'
            # ...

        twig:
            debug:            '%kernel.debug%'
            strict_variables: '%kernel.debug%'

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <!-- ... -->

            <framework:config secret="%secret%">
                <framework:router resource="%kernel.root_dir%/config/routing.xml" />
                <!-- ... -->
            </framework:config>

            <!-- Twig Configuration -->
            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%" />

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        // ...

        $container->loadFromExtension('framework', array(
            'secret' => '%secret%',
            'router' => array(
                'resource' => '%kernel.root_dir%/config/routing.php',
            ),
            // ...
        ));

        // Twig Configuration
        $container->loadFromExtension('twig', array(
            'debug'            => '%kernel.debug%',
            'strict_variables' => '%kernel.debug%',
        ));

        // ...

The ``framework`` key configures ``FrameworkBundle``, the ``twig`` key configures
``TwigBundle`` and so on. A *lot* of behavior in Symfony can be controlled just
by changing one option in this configuration file. To find out how, see the
:doc:`Configuration Reference </reference/index>` section.

Or, to get a big example dump of all of the valid configuration under a key,
use the handy ``app/console`` command:

.. code-block:: bash

    $ app/console config:dump-reference framework

There's a lot more power behind Symfony's configuration system, including
environments, imports and parameters. To learn all of it, see the
:doc:`Configuring Symfony (and Environments) </book/configuration>` chapter.

What's Next?
------------

Congrats! You're already starting to master Symfony and learn a whole new
way of building beautiful, functional, fast and maintainable apps.

Ok, time to finish mastering the fundamentals by reading these chapters:

* :doc:`/book/controller`
* :doc:`/book/routing`
* :doc:`/book/templating`

Then, in the :doc:`Symfony Book </book/index>`, learn about the
:doc:`service container </book/service_container>`,
the :doc:`form system </book/forms>`, using :doc:`Doctrine </book/doctrine>`
(if you need to query a database) and more!

There's also a :doc:`Cookbook </cookbook/index>` *packed* with more advanced
"how to" articles to solve *a lot* of problems.

Have fun!

.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard
.. _`Twig`: http://twig.sensiolabs.org
.. _`app/Resources/views/base.html.twig`: https://github.com/symfony/symfony-standard/blob/2.7/app/Resources/views/base.html.twig
.. _`KnpBundles.com`: http://knpbundles.com
.. _`Composer`: https://getcomposer.org