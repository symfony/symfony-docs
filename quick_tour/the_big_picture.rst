The Big Picture
===============

So, you want to try out Symfony2 but only have 10 minutes or so? This first
part of this tutorial has been written for you. It explains how to get started
fast with Symfony2 by showing you the structure of a simple ready-made
project.

If you have ever used a web framework before, you should feel right at home
with Symfony2.

.. index::
   pair: Sandbox; Download

Download and Install
--------------------

First, check that you have at least PHP 5.3.2 installed and correctly
configured to work with a web server like Apache.

Ready? Let's start by downloading Symfony. To get started even faster, we are
going to use the "Symfony sandbox". It is a Symfony project where all the
required libraries and some simple controllers are already included; the basic
configuration is also already done. The great advantage of the sandbox over
other types of installation is that you can start experimenting with Symfony
immediately.

Download the `sandbox`_, and unpack it in your root web directory. You
should now have a ``sandbox/`` directory::

    www/ <- your web root directory
        sandbox/ <- the unpacked archive
            app/
                cache/
                config/
                logs/
            src/
                Application/
                    HelloBundle/
                        Controller/
                        Resources/
                vendor/
                    symfony/
            web/

.. index::
   single: Installation; Check

Check the Configuration
-----------------------

To avoid some headaches further down the line, check that your configuration
can run a Symfony project smoothly by requesting the following URL:

    http://localhost/sandbox/web/check.php

Read the script output carefully and fix any problem that it finds.

Now, request your first "real" Symfony webpage:

    http://localhost/sandbox/web/index_dev.php/

Symfony should congratulate you for your hard work so far!

Your first Application
----------------------

The sandbox comes with a simple Hello World ":term:`application`" and that's
the application we will use to learn more about Symfony. Go to the following
URL to be greeted by Symfony (replace Fabien with your first name):

    http://localhost/sandbox/web/index_dev.php/hello/Fabien

What's going on here? Let's dissect the URL:

.. index:: Front Controller

* ``index_dev.php``: This is a "front controller". It is the unique entry
  point of the application and it responds to all user requests;

* ``/hello/Fabien``: This is the "virtual" path to the resource the user wants
  to access.

Your responsibility as a developer is to write the code that maps the user
request (``/hello/Fabien``) to the resource associated with it (``Hello
Fabien!``).

.. index::
   single: Configuration

Configuration
~~~~~~~~~~~~~

But how does Symfony route the request to your code? Simply by reading some
configuration file.

All Symfony2 configuration files can be written in either PHP, XML, or `YAML`_
(YAML is a simple format that makes the description of configuration settings
very easy).

.. tip::
   The sandbox defaults to YAML, but you can easily switch to XML or PHP by
   editing the ``app/AppKernel.php`` file. You can switch now by looking at the
   bottom of ``app/AppKernel.php`` for instructions (the tutorials show the
   configuration for all supported formats).

.. index::
   single: Routing
   pair: Configuration; Routing

Routing
~~~~~~~

So, Symfony routes the request by reading the routing configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        homepage:
            pattern:  /
            defaults: { _controller: FrameworkBundle:Default:index }

        hello:
            resource: HelloBundle/Resources/config/routing.yml

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <route id="homepage" pattern="/">
                <default key="_controller">FrameworkBundle:Default:index</default>
            </route>

            <import resource="HelloBundle/Resources/config/routing.xml" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addRoute('homepage', new Route('/', array(
            '_controller' => 'FrameworkBundle:Default:index',
        )));
        $collection->addCollection($loader->import("HelloBundle/Resources/config/routing.php"));

        return $collection;

The first few lines of the routing configuration file define which code to
call when the user requests the "``/``" resource. More interesting is the last
part, which imports another routing configuration file that reads as follows:

.. configuration-block::

    .. code-block:: yaml

        # src/Application/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:  /hello/:name
            defaults: { _controller: HelloBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Application/HelloBundle/Resources/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://www.symfony-project.org/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.symfony-project.org/schema/routing http://www.symfony-project.org/schema/routing/routing-1.0.xsd">

            <route id="hello" pattern="/hello/:name">
                <default key="_controller">HelloBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // src/Application/HelloBundle/Resources/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->addRoute('hello', new Route('/hello/:name', array(
            '_controller' => 'HelloBundle:Hello:index',
        )));

        return $collection;

Here we go! As you can see, the "``/hello/:name``" resource pattern (a string
beginning with a colon like ``:name`` is a placeholder) is mapped to a
controller, referenced by the ``_controller`` value.

.. index::
   single: Controller
   single: MVC; Controller

Controllers
~~~~~~~~~~~

The controller is responsible for returning a representation of the resource
(most of the time an HTML one) and it is defined as a PHP class:

.. code-block:: php
   :linenos:

    // src/Application/HelloBundle/Controller/HelloController.php

    namespace Application\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return $this->render('HelloBundle:Hello:index.php', array('name' => $name));
        }
    }

The code is pretty straightforward but let's explain this code line by line:

* *line 3*: Symfony takes advantage of new PHP 5.3 features and as such, all
  controllers are properly namespaced (the namespace is the first part of the
  ``_controller`` routing value: ``HelloBundle``).

* *line 7*: The controller name is the concatenation of the second part of the
  ``_controller`` routing value (``Hello``) and ``Controller``. It extends the
  built-in ``Controller`` class, which provides useful shortcuts (as we will
  see later in this tutorial).

* *line 9*: Each controller is made of several actions. As per the
  configuration, the hello page is handled by the ``index`` action (the third
  part of the ``_controller`` routing value). This method receives the
  resource placeholder values as arguments (``$name`` in our case).

* *line 11*: The ``render()`` method loads and renders a template
  (``HelloBundle:Hello:index``) with the variables passed as a second
  argument.

But what is a :term:`bundle`? All the code you write in a Symfony project is
organized in bundles. In Symfony speak, a bundle is a structured set of files
(PHP files, stylesheets, JavaScripts, images, ...) that implements a single
feature (a blog, a forum, ...) and which can be easily shared with other
developers. In our example, we only have one bundle, ``HelloBundle``.

Templates
~~~~~~~~~

So, the controller renders the ``HelloBundle:Hello:index.php`` template. But
what's in a template name? ``HelloBundle`` is the bundle name, ``Hello`` is
the controller, and ``index.php`` the template file name. The template itself
is made of HTML and simple PHP expressions:

.. code-block:: html+php

    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout.php') ?>

    Hello <?php echo $name ?>!

Congratulations! You have looked at your first Symfony piece of code. That was
not so hard, was it? Symfony makes it really easy to implement web sites
better and faster.

.. index::
   single: Environment
   single: Configuration; Environment

Environments
------------

Now that you have a better understanding on how Symfony works, have a closer
look at the bottom of the page; you will notice a small bar with the Symfony
and PHP logos. It is called the "Web Debug Toolbar" and it is the developer's
best friend. Of course, such a tool must not be displayed when you deploy your
application to your production servers. That's why you will find another front
controller in the ``web/`` directory (``index.php``), optimized for the production
environment:

    http://localhost/sandbox/web/index.php/hello/Fabien

And if you have ``mod_rewrite`` installed, you can even omit the ``index.php``
part of the URL:

    http://localhost/sandbox/web/hello/Fabien

Last but not least, on the production servers, you should point your web root
directory to the ``web/`` directory to secure your installation and have an even
better looking URL:

    http://localhost/hello/Fabien

To make the production environment as fast as possible, Symfony maintains a
cache under the ``app/cache/`` directory. When you make changes, you need to
manually remove the cached files. That's why you should always use the
development front controller (``index_dev.php``) when working on a project.

Final Thoughts
--------------

The 10 minutes are over. By now, you should be able to create your own simple
routes, controllers, and templates. As an exercise, try to build something
more useful than the Hello application! But if you are eager to learn more
about Symfony, you can read the next part of this tutorial right away, where
we dive more into the templating system.

.. _sandbox: http://symfony-reloaded.org/code#sandbox
.. _YAML:    http://www.yaml.org/
