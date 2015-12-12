The Architecture
================

You are my hero! Who would have thought that you would still be here after
the first three parts? Your efforts will be well rewarded soon. The first
three parts didn't look too deeply at the architecture of the framework.
Because it makes Symfony stand apart from the framework crowd, let's dive
into the architecture now.

Understanding the Directory Structure
-------------------------------------

The directory structure of a Symfony :term:`application` is rather flexible,
but the recommended structure is as follows:

``app/``
    The application configuration, templates and translations.
``bin/``
    Executable files (e.g. ``bin/console``).
``src/``
    The project's PHP code.
``tests/``
    Automatic tests (e.g. Unit tests).
``var/``
    Generated files (cache, logs, etc.).
``vendor/``
    The third-party dependencies.
``web/``
    The web root directory.

The ``web/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

The web root directory is the home of all public and static files like images,
stylesheets and JavaScript files. It is also where each :term:`front controller`
lives, such as the production controller shown here::

    // web/app.php
    require_once __DIR__.'/../var/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    use Symfony\Component\HttpFoundation\Request;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();

The controller first bootstraps the application using a kernel class (``AppKernel``
in this case). Then, it creates the ``Request`` object using the PHP's global
variables and passes it to the kernel. The last step is to send the response
contents returned by the kernel back to the user.

.. _the-app-dir:

The ``app/`` Directory
~~~~~~~~~~~~~~~~~~~~~~

The ``AppKernel`` class is the main entry point of the application
configuration and as such, it is stored in the ``app/`` directory.

This class must implement two methods:

``registerBundles()``
    Must return an array of all bundles needed to run the application, as
    explained in the next section.
``registerContainerConfiguration()``
    Loads the application configuration (more on this later).

Autoloading is handled automatically via `Composer`_, which means that you
can use any PHP class without doing anything at all! All dependencies
are stored under the ``vendor/`` directory, but this is just a convention.
You can store them wherever you want, globally on your server or locally
in your projects.

Understanding the Bundle System
-------------------------------

This section introduces one of the greatest and most powerful features of
Symfony, the :term:`bundle` system.

A bundle is kind of like a plugin in other software. So why is it
called a *bundle* and not a *plugin*? This is because *everything* is a
bundle in Symfony, from the core framework features to the code you write
for your application.

All the code you write for your application is organized in bundles. In
Symfony speak, a bundle is a structured set of files (PHP files, stylesheets,
JavaScripts, images, ...) that implements a single feature (a blog, a forum,
...) and which can be easily shared with other developers.

Bundles are first-class citizens in Symfony. This gives you the flexibility
to use pre-built features packaged in third-party bundles or to distribute
your own bundles. It makes it easy to pick and choose which features to
enable in your application and optimize them the way you want. And at the
end of the day, your application code is just as *important* as the core
framework itself.

Symfony already includes an AppBundle that you may use to start developing
your application. Then, if you need to split the application into reusable
components, you can create your own bundles.

Registering a Bundle
~~~~~~~~~~~~~~~~~~~~

An application is made up of bundles as defined in the ``registerBundles()``
method of the ``AppKernel`` class. Each bundle is a directory that contains
a single Bundle class that describes it::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

In addition to the AppBundle that was already talked about, notice that
the kernel also enables other bundles that are part of Symfony, such as
FrameworkBundle, DoctrineBundle, SwiftmailerBundle and AsseticBundle.

Configuring a Bundle
~~~~~~~~~~~~~~~~~~~~

Each bundle can be customized via configuration files written in YAML, XML,
or PHP. Have a look at this sample of the default Symfony configuration:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: parameters.yml }
        - { resource: security.yml }
        - { resource: services.yml }

    framework:
        #esi:             ~
        #translator:      { fallbacks: ['%locale%'] }
        secret:          '%secret%'
        router:
            resource: '%kernel.root_dir%/config/routing.yml'
            strict_requirements: '%kernel.debug%'
        form:            true
        csrf_protection: true
        validation:      { enable_annotations: true }
        templating:      { engines: ['twig'] }
        default_locale:  '%locale%'
        trusted_proxies: ~
        session:         ~

    # Twig Configuration
    twig:
        debug:            '%kernel.debug%'
        strict_variables: '%kernel.debug%'

    # Swift Mailer Configuration
    swiftmailer:
        transport: '%mailer_transport%'
        host:      '%mailer_host%'
        username:  '%mailer_user%'
        password:  '%mailer_password%'
        spool:     { type: memory }

    # ...

Each first level entry like ``framework``, ``twig`` and ``swiftmailer``
defines the configuration for a specific bundle. For example, ``framework``
configures the FrameworkBundle while ``swiftmailer`` configures the
SwiftmailerBundle.

Each :term:`environment` can override the default configuration by providing
a specific configuration file. For example, the ``dev`` environment loads
the ``config_dev.yml`` file, which loads the main configuration (i.e.
``config.yml``) and then modifies it to add some debugging tools:

.. code-block:: yaml

    # app/config/config_dev.yml
    imports:
        - { resource: config.yml }

    framework:
        router:   { resource: '%kernel.root_dir%/config/routing_dev.yml' }
        profiler: { only_exceptions: false }

    web_profiler:
        toolbar: true
        intercept_redirects: false

    # ...

Extending a Bundle
~~~~~~~~~~~~~~~~~~

In addition to being a nice way to organize and configure your code, a bundle
can extend another bundle. Bundle inheritance allows you to override any
existing bundle in order to customize its controllers, templates, or any
of its files.

Logical File Names
..................

When you want to reference a file from a bundle, use this notation:
``@BUNDLE_NAME/path/to/file``; Symfony will resolve ``@BUNDLE_NAME``
to the real path to the bundle. For instance, the logical path
``@AppBundle/Controller/DefaultController.php`` would be converted to
``src/AppBundle/Controller/DefaultController.php``, because Symfony knows
the location of the AppBundle.

Logical Controller Names
........................

For controllers, you need to reference actions using the format
``BUNDLE_NAME:CONTROLLER_NAME:ACTION_NAME``. For instance,
``AppBundle:Default:index`` maps to the ``indexAction`` method from the
``AppBundle\Controller\DefaultController`` class.

Extending Bundles
.................

If you follow these conventions, then you can use
:doc:`bundle inheritance </cookbook/bundles/inheritance>` to override files,
controllers or templates. For example, you can create a bundle - NewBundle
- and specify that it overrides AppBundle. When Symfony loads the
``AppBundle:Default:index`` controller, it will first look for the
``DefaultController`` class in NewBundle and, if it doesn't exist, then
look inside AppBundle. This means that one bundle can override almost any
part of another bundle!

Do you understand now why Symfony is so flexible? Share your bundles between
applications, store them locally or globally, your choice.

.. _using-vendors:

Using Vendors
-------------

Odds are that your application will depend on third-party libraries. Those
should be stored in the ``vendor/`` directory. You should never touch anything
in this directory, because it is exclusively managed by Composer. This directory
already contains the Symfony libraries, the SwiftMailer library, the Doctrine
ORM, the Twig templating system and some other third party libraries and
bundles.

Understanding the Cache and Logs
--------------------------------

Symfony applications can contain several configuration files defined in
several formats (YAML, XML, PHP, etc.) Instead of parsing and combining
all those files for each request, Symfony uses its own cache system. In
fact, the application configuration is only parsed for the very first request
and then compiled down to plain PHP code stored in the ``var/cache/``
directory.

In the development environment, Symfony is smart enough to update the cache
when you change a file. But in the production environment, to speed things
up, it is your responsibility to clear the cache when you update your code
or change its configuration. Execute this command to clear the cache in
the ``prod`` environment:

.. code-block:: bash

    $ php bin/console cache:clear --env=prod

When developing a web application, things can go wrong in many ways. The
log files in the ``var/logs/`` directory tell you everything about the requests
and help you fix the problem quickly.

Using the Command Line Interface
--------------------------------

Each application comes with a command line interface tool (``bin/console``)
that helps you maintain your application. It provides commands that boost
your productivity by automating tedious and repetitive tasks.

Run it without any arguments to learn more about its capabilities:

.. code-block:: bash

    $ php bin/console

The ``--help`` option helps you discover the usage of a command:

.. code-block:: bash

    $ php bin/console debug:router --help

Final Thoughts
--------------

Call me crazy, but after reading this part, you should be comfortable with
moving things around and making Symfony work for you. Everything in Symfony
is designed to get out of your way. So, feel free to rename and move directories
around as you see fit.

And that's all for the quick tour. From testing to sending emails, you still
need to learn a lot to become a Symfony master. Ready to dig into these
topics now? Look no further - go to the official :doc:`/book/index` and
pick any topic you want.

.. _Composer:   https://getcomposer.org
