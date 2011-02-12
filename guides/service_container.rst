.. index::
   single: Service Container
   single: Dependency Injection Container

The Service Container
=====================

A modern PHP application is full of objects. One object may facilitate the
delivery of email messages while another may allow you to persist information
into a database. In your application, you may create an object that manages
your product inventory, or another object that processes data from a third-party
API. The point is that a modern application does many things and is organized
into many object that handle each task.

In this guide, we'll talk about a special PHP object in Symfony2 that helps
your instantiate, organize and retrieve the many object of your application.
This object, called a service container, will allow you to standardize and
centralize the way objects are constructed in your application. The container
makes your life easier, is super fast, and emphasizes and architecture that
promotes reusable and decoupled code. And since all core Symfony2 classes
use the container, you'll learn how to extend, configure and use any object
in Symfony2. In large part, the service container is the biggest contributor
to the speed and extensability of Symfony2.

Finally, configuring and using the service container is easy. By the end
of this chapter, you'll be comfortable creating your own objects via the
container and customizing objects from any third-party bundle. You'll begin
writing code that is more reusable, testable and decoupled, simply because
the service container makes writing good code so easy.

.. index::
   single: Service Container; What is a service?

What is a Service?
------------------

Put simply, a :term:`Service` is any PHP object that does something. It's
a purposefully-generic name used in computer science to describe an object
that's created for a specific purpose (e.g. delivering emails). You don't
have to do anything specialy to make a service: simply write a PHP class
with some code that accomplishes a specific task. Congrtulations, you've
just created a service!

So what's the big deal then? The advantage of thinking about "services" is
that you begin to think about separating each piece of functionality in your
application into a series of services. Since each service does just one job,
you can easily access each service and use its functionality wherever you
need it. Each service can also be more easily tested and configured since
it's separated from the other functionality in your application. This idea
is called `service-oriented architecture`_ and is not unique to Symfony2
or even PHP. Structuring your application around a set of independent service
classes is well-known and trusted object-oriented best-practice. These skills
are key to being a good developer in almost any language.

.. index::
   single: Service Container; What is?

What is a Service Container?
----------------------------

A :term:`Service Container` (or *dependency injection container*) is simply
a PHP object that manages the instantiation of services (i.e. objects).
For example, suppose we have a simple PHP class that delivers email messages.
Without a service container, we must manually create the object whenever
we need it:

.. code-block::

    use Sensio\HelloBundle\Mailer;

    $mailer = new Mailer('sendmail');
    $mailer->send('ryan@foobar.net', ... );

This is easy enough. The imaginary ``Mailer`` class allows us to configure
the method used to deliver the email messages (e.g. ``sendmail``, ``smtp``, etc).
But what if we wanted to use the mailer service somewhere else? We certainly
don't want to repeat the mailer configuration *every* time we need to use
the ``Mailer`` object. What if we needed to change the ``transport`` from
``sendmail`` to ``smtp`` everywhere in the application? We'd need to hunt
down every place we create a ``Mailer`` service and change it.

.. index::
   single: Service Container; Configuring services

Creating/Configuring Services in the Container
----------------------------------------------

A better answer is to let the service container create the ``Mailer`` object
for you. In order for this to work, we must *teach* the container how to
create the ``Mailer`` service. This is done via configuration, which can
be specified in YAML, XML or PHP:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            my_mailer:
                class:        Sensio\HelloBundle\Mailer
                arguments:    [sendmail]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="my_mailer" class="Sensio\HelloBundle\Mailer">
                <argument>sendmail</argument>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('my_mailer', new Definition(
            'Sensio\HelloBundle\Mailer',
            array('sendmail')
        ));

.. note::

    When Symfony2 initializes, it builds the service container using the
    application configuration (``app/config/config.yml`` by default). The
    exact file that's loaded is dicated by the ``AppKernel::loadConfig()``
    method, which loads an environment-specific configuration file (e.g.
    ``config_dev.yml`` for the ``dev`` environment or ``config_prod.yml``
    for ``prod``).

An instance of the ``Sensio\HelloBundle\Mailer`` object is now available via
the service container. Since the container is available in any traditional
Symfony2 controller, we can easily access the new ``my_mailer`` service::

    class HelloController extends Controller
    {
        // ...

        public function sendEmailAction()
        {
            // ...
            $mailer = $this->container->get('my_mailer');
            $mailer->send('ryan@foobar.net', ... );
        }
    }

.. tip::

    When using a tranditional controller, there's an even shorter way to
    access a service from the container. This is exactly equivalent to the
    above method, but with less keystrokes::

        $mailer = $this->get('my_mailer');

When we ask for the ``my_mailer`` service from the container, the container
constructs the object and returns it. This is another major advantage of
using the service container. Namely, a service is *never* constructed until
its needed. If you define a service and never use it on a request, the service
is never created. This saves memory and increases the speed of your application.
This also means that there's very little or no performance hit for defining
lot's of services. Services that are never used are never constructed.

As an added bonus, the ``Mailer`` service is only created once and the same
instance is returned each time you ask for the service. This is almost always
the behavior you'll need (it's more flexible and performant), but we'll learn
later how you can configure a service that has multiple instances.

Service Parameters
------------------

The creation of new services (i.e. objects) via the container is pretty
straightforward. Parameters make defining services more organized and flexible:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            my_mailer.class:      Sensio\HelloBundle\Mailer
            my_mailer.transport:  sendmail

        services:
            my_mailer:
                class:        %my_mailer.class%
                arguments:    [%my_mailer.transport%]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="my_mailer.class">Sensio\HelloBundle\Mailer</parameter>
            <parameter key="my_mailer.transport">sendmailxml</parameter>
        </parameters>

        <services>
            <service id="my_mailer" class="%my_mailer.class%">
                <argument>%my_mailer.transport%</argument>
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('my_mailer.class', 'Sensio\HelloBundle\Mailer');
        $container->setParameter('my_mailer.transport', 'sendmailphp');

        $container->setDefinition('my_mailer', new Definition(
            '%my_mailer.class%',
            array('%my_mailer.transport%')
        ));

The end result is exactly the same as before - the difference is only in
*how* we defined the service. By surrounding the ``my_mailer.class`` and
``my_mailer.transport`` strings in percent (``%``) signs, the container knows
to look for parameters with those names. When the container is built, it
looks up the value of each parameter and uses it in the service definition.

The purpose of parameters is to feed information into services. Of course
there was nothing wrong with defining the service without using any parameters.
Parameters, however, have several advantages:

* separation and organization of all of the service "options" under a single
  ``parameters`` key;

* parameter values can be used in multiple service definitions;

* when creating a service in a bundle (we'll show this shortly), using parameters
  allows the service to be easily customized in your application;

The choice of using or not using parameters is up to you. High-quality
third-party bundles will *always* use parameters as they make the service
stored in the container more configurable. For the services in your application,
however, you may not need the flexibility of parameters.

Importing other Container Configuration Resources
-------------------------------------------------

.. tip::

    In this section, we'll refer to service configuration files as *resources*.
    This is to highlight that fact that, while most configuration resources
    will be files (e.g. YAML, XML, PHP), Symfony2 is so flexible that configuration
    could be loaded from anywhere (e.g. a database or even via an external
    web service).

The service container is built using a single configuration resource
(``app/config/config.yml`` by default). All other service configuration(
(including the core Symfony2 and third-party bundle configuration) must
be imported form inside this file in one way or another. This gives you absolute
flexibility over the services in your application.

External service configuration can be imported in two different ways. First,
we'll talk about the method that you'll use most commonly in your application
- the ``imports`` directive. In the next section, we'll introduce the second
method, which is the flexible and preferred method for importing service
configuration from third-party bundles.

.. index::
   single: Service Container; imports

Importing Configuration with ``imports``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So far, we've placed our ``my_mailer`` service container definition directly
in the application configuration file (e.g. ``app/config/config.yml``). Of
course, since the ``Mailer`` class itself lives inside ``HelloBundle``, it
makes more sense to put the ``my_mailer`` container definition inside the
bundle as well.

First, move the ``my_mailer`` container definition into a new container resource
file in ``HelloBundle``. If the ``Resources`` or ``Resources/config`` directories
don't exist, create them.

.. configuration-block::

    .. code-block:: yaml

        # src/Sensio/HelloBundle/Resources/config/services.yml
        parameters:
            my_mailer.class:      Sensio\HelloBundle\Mailer
            my_mailer.transport:  sendmail

        services:
            my_mailer:
                class:        %my_mailer.class%
                arguments:    [%my_mailer.transport%]

    .. code-block:: xml

        <!-- src/Sensio/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <parameter key="my_mailer.class">Sensio\HelloBundle\Mailer</parameter>
            <parameter key="my_mailer.transport">sendmailxml</parameter>
        </parameters>

        <services>
            <service id="my_mailer" class="%my_mailer.class%">
                <argument>%my_mailer.transport%</argument>
            </service>
        </services>

    .. code-block:: php

        // src/Sensio/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('my_mailer.class', 'Sensio\HelloBundle\Mailer');
        $container->setParameter('my_mailer.transport', 'sendmailphp');

        $container->setDefinition('my_mailer', new Definition(
            '%my_mailer.class%',
            array('%my_mailer.transport%')
        ));

The definition itself hasn't changed, only its location. Of course the service
container doesn't know about the new resource file. Fortunately, we can
easily import the resource file using the ``imports`` key in the application
configuration.

.. configuration-block::

    .. code-block:: yaml

        imports:
            hello_bundle:
                resource: @HelloBundle/Resources/config/services.yml

    .. code-block:: xml

        <imports>
            <import resource="@HelloBundle/Resources/config/services.xml"/>
        </imports>

    .. code-block:: php

        $this->import('@HelloBundle/Resources/config/services.php');

The ``imports`` directive allows your application to include service container
configuration resources from any other location (most commonly from bundles).
The ``resource`` location, for files, is the absolute path to the resource
file. The special ``@HelloBundle`` syntax resolves to the directory path to
the ``HelloBundle``. This helps you specify the path to the resource without
worrying later if you move the ``HelloBundle`` to a different directory.

.. index::
   single: Service Container; Extension configuration

.. _`service-container-extension-configuration`

Importing Configuration via Container Extensions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When developing in Symfony2, you'll most commonly use the ``imports`` directive
to import container configuration from the bundles in your project. Third
-party bundle container configuration (including the Symfony2 core services),
are usually loaded using another method that's more flexible, but easy to
configure in your application.

Here's how it works. Internally, each bundle defines defines its services
very much like we've seen so far in this guide. Namely, the bundle uses one
or more configuration resource files (usually XML) to specify the parameters
and services for that bundle. However, instead of importing each of these
resources from your application configuration, you can simply invoke a *service
container extension* inside the bundle that does all the work for you.

Take the ``FrameworkBundle`` - the core Symfony2 framework bundle - as an
example. The presence of the following code in your application configuration
invokes the service container extension inside the ``FrameworkBundle``::

.. configuration-block::

    code-block:: yaml

        app.config:  ~

    code-block:: xml

        <app:config />

    code-block:: php

        $container->loadFromExtension('app', 'config');

When the configuration is parsed, the container looks for an extension that
can handle the ``app.config`` configuration directive. The extension in question,
which lives in the ``FrameworkBundle`` is invoked and the service configuration
for the ``FrameworkBundle`` is loaded. If you remove the ``app.config`` key
from your application configuration file, the core Symfony2 services won't
be loaded. The point is that you're in control: the Symfony2 framework doesn't
doesn't perform any magic or perform any actions that you don't have control
over.

Of course you can do much more than simply "activate" the service container
extension of the ``FrameworkBundle``. Each extension allows you to easily
customize the bundle, without worrying about how the internal services are
defined. In fact, the default configuration beneath ``app.config`` looks much
more like this:

.. configuration-block::

    .. code-block:: yaml

        app.config:
            charset:       UTF-8
            error_handler: null
            csrf_protection:
                enabled: true
                secret: xxxxxxxxxx
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }
            # ...

    .. code-block:: xml

        <app:config charset="UTF-8" error-handler="null">
            <app:csrf-protection enabled="true" secret="xxxxxxxxxx" />
            <app:router resource="%kernel.root_dir%/config/routing.xml" cache-warmer="true" />
            <!-- ... -->
        </app:config>

    .. code-block:: php

        $container->loadFromExtension('app', 'config', array(
            'charset'         => 'UTF-8',
            'error_handler'   => null,
            'csrf-protection' => array('enabled' => true, 'secret' => 'xxxxxxxxxx'),
            'router'          => array('resource' => '%kernel.root_dir%/config/routing.php'),
            // ...
        ));

In this case, the extension allows you to customize the ``charset``, ``error_handerl``,
``csrf_protection``, ``router`` configuration and much more. Internally,
the ``FrameworkBundle`` uses the options specified here to define and configure
the services specific to it. The bundle takes care of creating all the necessary
``parameters`` and ``services`` for the service container, while still allowing
much of the configuration to be easily customized.

When installing or configuring a bundle, see the bundle's documentation for
how the services for the bundle should be installed and configured. The options
available for the core bundles can be found inside the :doc:`Reference Guide</reference/index>`.

.. note::

   By default, the service container only recognizes the ``parameters``,
   ``services``, ``imports`` and ``interfaces`` directives. Any other directives
   are handled by a service container extension.

.. index::
   single: Service Container; Referencing services

Referencing (Injecting) Services
--------------------------------

So far, our original ``my_mailer`` service is simple: it takes just one argument
in its constructor, which is easily configurable. As you'll see, the real
power of the container is realized when you need to create a service that
depends on one or more other services in the container.

Let's start with an example. Suppose we have a new service, ``NewsletterManager``,
that helps to manage the preparating and delivery of an email message to
a collection of addresses. Of course the ``my_mailer`` service is already
really good at deliver email messages, so we'll use it inside ``NewsletterManager``
to handle the actual delivery of the messages. This pretend class might look
something like this::

    namespace Sensio\HelloBundle\Newsletter;
    use Sensio\HelloBundle\Mailer;

    class NewsletterManager
    {
        protected $mailer;

        public function __construct(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

Without using the service container, we can create a new ``NewsletterManager``
fairly easily from inside a controller::

    public function sendNewsletterAction()
    {
        $mailer = $this->container->get('my_mailer');
        $newsletter = new Sensio\HelloBundle\Newsletter\NewsletterManager($mailer);
        // ...
    }

This approach is fine, but what if we decide later that the ``NewsletterManager``
class needs a second or third constructor argument? What if we decide to
refactor our code and rename the class? In both cases, you'd need to find every
place where the ``NewsletterManager`` is instantiated and modify it. Of course,
the service container gives us a much more appealing option:

.. configuration-block::

    .. code-block:: yaml

        # src/Sensio/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            newsletter_manager.class: Sensio\HelloBundle\Newsletter\NewsletterManager

        services:
            my_mailer:
                # ...
            newsletter_manager:
                class:     %newsletter_manager.class%
                arguments: [@my_mailer]

    .. code-block:: xml

        <!-- src/Sensio/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="newsletter_manager.class">Sensio\HelloBundle\Newsletter\NewsletterManager</parameter>
        </parameters>

        <services>
            <service id="my_mailer" ... >
              <!-- ... -->
            </service>
            <service id="newsletter_manager" class="%newsletter_manager.class%">
                <argument type="service" id="my_mailer"/>
            </service>
        </services>

    .. code-block:: php

        // src/Sensio/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->setParameter('newsletter_manager.class', 'Sensio\HelloBundle\Newsletter\NewsletterManager');

        $container->setDefinition('my_mailer', ... );
        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%',
            array(new Reference('my_mailer'))
        ));

In YAML, the special ``@my_mailer`` syntax tells the container to look for
a service named ``my_mailer`` and to pass that object into the constructor
of ``NewsletterManager``.

This is a very powerful tool that allows you to create independent service
classes with well-defined dependencies. In this example, the ``newsletter_manager``
service needs the ``my_mailer`` service in order to function. When you define
this dependency in the service container, the container takes care of all
the work of instantiating the objects.

Core Symfony and Third-Party Bundle Services
--------------------------------------------

Since Symfony2 and all third-party bundles configure and retrieve their services
via the container, you can easily access them or even use them in your own
services. For example, to handle the storage of information on a user's
session, Symfony2 provides a ``session`` service::

    public function indexAction($bar)
    {
        $session = $this->container->get('session');
        $session->set('foo', $bar);

        // ...
    }

In Symfony2, youll constantly use services provided by the Symfony core or
other third-party bundles to perform tasks such as rendering templates (``templating``),
sending emails (``mailer``), or accessing information on the request (``request``).

We can take this a step further by using these services inside services that
you've created for your application. Let's modify the ``NewsletterManager``
to use the real Symfony2 ``mailer`` service (instead of the pretend ``my_mailer``).
Let's also pass the templating engine service to the ``NewsletterManager``
so that it can generate the email content via a template::

    namespace Sensio\HelloBundle\Newsletter;
    use Symfony\Component\Templating\EngineInterface;

    class NewsletterManager
    {
        protected $mailer;

        protected $templating;

        public function __construct(\Swift_Mailer $mailer, EngineInterface $templating)
        {
            $this->mailer = $mailer;
            $this->templating = $templating;
        }

        // ...
    }

Configuring the service container is easy:

.. configuration-block::

    .. code-block:: yaml

        services:
            newsletter_manager:
                class:     %newsletter_manager.class%
                arguments: [@mailer, @templating]

    .. code-block:: xml

        <service id="newsletter_manager" class="%newsletter_manager.class%">
            <argument type="service" id="mailer"/>
            <argument type="service" id="templating"/>
        </service>

    .. code-block:: php

        $container->setDefinition('newsletter_manager', new Definition(
            '%newsletter_manager.class%',
            array(
                new Reference('mailer'),
                new Reference('templating')
            )
        ));

The ``newsletter_manager`` service now has access to the core ``mailer``
and ``templating`` services. This is a common way to create services specific
to your application that leverage the power of different services within
the framework.

.. tip::

    Be sure that ``swift_mailer.config`` entry appears in your application
    configuration. As we mentioned in :ref:`service-container-extension-configuration`,
    the ``swift_mailer.config`` invokes the service configuration from the
    ``SwiftmailerBundle``, which registers the ``mailer`` service.

.. index::
   single: Service Container; Advanced configuration

Advanced Container Configuration
--------------------------------

As we've seen, defining services inside the container is easy, generally
involving a ``service`` configuration key and a few parameters. However,
the container has several other tools available that help to *tag* services
for special functionality, create more complex services, and perform operations
after the container is built.

Tags (``tags``)
~~~~~~~~~~~~~~~

In the same way that a blog post on the Web might be tagged with things such
as "Symfony" or "PHP", services configured in your container can also be
tagged. In the service container, a tag implies that the service is meant
to be used for a specific purpose. Take the following example:

.. configuration-block::

    .. code-block:: yaml

        services:
            foo.twig.extension:
                class: Sensio\HelloBundle\Extension\FooExtension
                tags:
                    twig.extension:
                      name:    twig.extension

    .. code-block:: xml

        <service id="foo.twig.extension" class="Sensio\HelloBundle\Extension\RadiusExtension">
            <tag name="twig.extension" />
        </service>

    .. code-block:: php

        $definition = new Definition('Sensio\HelloBundle\Extension\RadiusExtension');
        $definition->addTag('twig.extension');
        $container->setDefinition('foo.twig.extension', $definition);

The ``twig.extension`` tag is a special tag that the ``TwigBundle`` uses
during configuration. By giving the service this ``twig.extension`` tag,
the bundle knows that the ``foo.twig.extension`` service should be registered
as a Twig extension with Twig. In other words, Twig finds all services tagged
with ``twig.extension`` and automatically registers them as extensions.

Tags, then, are a way to tell Symfony2 or other third-party bundles that
your service should be registered or used in some specialy way by the bundle.

The following is a list of the tags available with the core Symfony2 bundles.
Each of these has a different affect on your service and many tags require
additional arguments (beyond just the tag ``name``).

* assetic.filter
* assetic.templating.php
* data_collector
* form.field_factory.guesser
* kernel.cache_warmer
* kernel.listener
* routing.loader
* security.listener.factory
* security.voter
* templating.helper
* twig.extension
* translation.loader
* validator.constraint_validator
* zend.logger.writer

.. _`service-oriented architecture`: http://wikipedia.org/wiki/Service-oriented_architecture