.. index::
   single: Service Container
   single: DependencyInjection; Container

Service Container
=================

A modern PHP application is full of objects. One object may facilitate the
delivery of email messages while another may allow you to persist information
into a database. In your application, you may create an object that manages
your product inventory, or another object that processes data from a third-party
API. The point is that a modern application does many things and is organized
into many objects that handle each task.

This chapter is about a special PHP object in Symfony that helps
you instantiate, organize and retrieve the many objects of your application.
This object, called a service container, will allow you to standardize and
centralize the way objects are constructed in your application. The container
makes your life easier, is super fast, and emphasizes an architecture that
promotes reusable and decoupled code. Since all core Symfony classes
use the container, you'll learn how to extend, configure and use any object
in Symfony. In large part, the service container is the biggest contributor
to the speed and extensibility of Symfony.

Finally, configuring and using the service container is easy. By the end
of this chapter, you'll be comfortable creating your own objects via the
container and customizing objects from any third-party bundle. You'll begin
writing code that is more reusable, testable and decoupled, simply because
the service container makes writing good code so easy.

.. tip::

    If you want to know a lot more after reading this chapter, check out
    the :doc:`DependencyInjection component documentation </components/dependency_injection/introduction>`.

.. index::
   single: Service Container; What is a service?

What is a Service?
------------------

Put simply, a :term:`Service` is any PHP object that performs some sort of
"global" task. It's a purposefully-generic name used in computer science
to describe an object that's created for a specific purpose (e.g. delivering
emails). Each service is used throughout your application whenever you need
the specific functionality it provides. You don't have to do anything special
to make a service: simply write a PHP class with some code that accomplishes
a specific task. Congratulations, you've just created a service!

.. note::

    As a rule, a PHP object is a service if it is used globally in your
    application. A single ``Mailer`` service is used globally to send
    email messages whereas the many ``Message`` objects that it delivers
    are *not* services. Similarly, a ``Product`` object is not a service,
    but an object that persists ``Product`` objects to a database *is* a service.

So what's the big deal then? The advantage of thinking about "services" is
that you begin to think about separating each piece of functionality in your
application into a series of services. Since each service does just one job,
you can easily access each service and use its functionality wherever you
need it. Each service can also be more easily tested and configured since
it's separated from the other functionality in your application. This idea
is called `service-oriented architecture`_ and is not unique to Symfony
or even PHP. Structuring your application around a set of independent service
classes is a well-known and trusted object-oriented best-practice. These skills
are key to being a good developer in almost any language.

.. index::
   single: Service Container; What is a service container?

What is a Service Container?
----------------------------

A :term:`Service Container` (or *dependency injection container*) is simply
a PHP object that manages the instantiation of services (i.e. objects).

For example, suppose you have a simple PHP class that delivers email messages.
Without a service container, you must manually create the object whenever
you need it::

    use Acme\HelloBundle\Mailer;

    $mailer = new Mailer('sendmail');
    $mailer->send('ryan@example.com', ...);

This is easy enough. The imaginary ``Mailer`` class allows you to configure
the method used to deliver the email messages (e.g. ``sendmail``, ``smtp``, etc).
But what if you wanted to use the mailer service somewhere else? You certainly
don't want to repeat the mailer configuration *every* time you need to use
the ``Mailer`` object. What if you needed to change the ``transport`` from
``sendmail`` to ``smtp`` everywhere in the application? You'd need to hunt
down every place you create a ``Mailer`` service and change it.

.. index::
   single: Service Container; Configuring services

.. _service-container-creating-service:

Creating/Configuring Services in the Container
----------------------------------------------

A better answer is to let the service container create the ``Mailer`` object
for you. In order for this to work, you must *teach* the container how to
create the ``Mailer`` service. This is done via configuration, which can
be specified in YAML, XML or PHP:

.. include:: includes/_service_container_my_mailer.rst.inc

.. note::

    When Symfony initializes, it builds the service container using the
    application configuration (``app/config/config.yml`` by default). The
    exact file that's loaded is dictated by the ``AppKernel::registerContainerConfiguration()``
    method, which loads an environment-specific configuration file (e.g.
    ``config_dev.yml`` for the ``dev`` environment or ``config_prod.yml``
    for ``prod``).

An instance of the ``Acme\HelloBundle\Mailer`` object is now available via
the service container. The container is available in any traditional Symfony
controller where you can access the services of the container via the ``get()``
shortcut method::

    class HelloController extends Controller
    {
        // ...

        public function sendEmailAction()
        {
            // ...
            $mailer = $this->get('my_mailer');
            $mailer->send('ryan@foobar.net', ...);
        }
    }

When you ask for the ``my_mailer`` service from the container, the container
constructs the object and returns it. This is another major advantage of
using the service container. Namely, a service is *never* constructed until
it's needed. If you define a service and never use it on a request, the service
is never created. This saves memory and increases the speed of your application.
This also means that there's very little or no performance hit for defining
lots of services. Services that are never used are never constructed.

As a bonus, the ``Mailer`` service is only created once and the same
instance is returned each time you ask for the service. This is almost always
the behavior you'll need (it's more flexible and powerful), but you'll learn
later how you can configure a service that has multiple instances in the
":doc:`/cookbook/service_container/scopes`" cookbook article.

.. note::

    In this example, the controller extends Symfony's base Controller, which
    gives you access to the service container itself. You can then use the
    ``get`` method to locate and retrieve the ``my_mailer`` service from
    the service container. You can also define your :doc:`controllers as services </cookbook/controller/service>`.
    This is a bit more advanced and not necessary, but it allows you to inject
    only the services you need into your controller.

.. _book-service-container-parameters:

Service Parameters
------------------

The creation of new services (i.e. objects) via the container is pretty
straightforward. Parameters make defining services more organized and flexible:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            my_mailer.transport:  sendmail

        services:
            my_mailer:
                class:        Acme\HelloBundle\Mailer
                arguments:    ["%my_mailer.transport%"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="my_mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="my_mailer" class="Acme\HelloBundle\Mailer">
                    <argument>%my_mailer.transport%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('my_mailer.transport', 'sendmail');

        $container->setDefinition('my_mailer', new Definition(
            'Acme\HelloBundle\Mailer',
            array('%my_mailer.transport%')
        ));

The end result is exactly the same as before - the difference is only in
*how* you defined the service. By surrounding the ``my_mailer.transport``
string in percent (``%``) signs, the container knows to look for a parameter
with that name. When the container is built, it looks up the value of each
parameter and uses it in the service definition.

.. note::

    If you want to use a string that starts with an ``@`` sign as a parameter
    value (e.g. a very safe mailer password) in a YAML file, you need to escape
    it by adding another ``@`` sign (this only applies to the YAML format):

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            # This will be parsed as string "@securepass"
            mailer_password: "@@securepass"

.. note::

    The percent sign inside a parameter or argument, as part of the string, must
    be escaped with another percent sign:

    .. code-block:: xml

        <argument type="string">http://symfony.com/?foo=%%s&bar=%%d</argument>

The purpose of parameters is to feed information into services. Of course
there was nothing wrong with defining the service without using any parameters.
Parameters, however, have several advantages:

* separation and organization of all service "options" under a single
  ``parameters`` key;

* parameter values can be used in multiple service definitions;

* when creating a service in a bundle (this follows shortly), using parameters
  allows the service to be easily customized in your application.

The choice of using or not using parameters is up to you. High-quality
third-party bundles will *always* use parameters as they make the service
stored in the container more configurable. For the services in your application,
however, you may not need the flexibility of parameters.

Array Parameters
~~~~~~~~~~~~~~~~

Parameters can also contain array values. See :ref:`component-di-parameters-array`.

Importing other Container Configuration Resources
-------------------------------------------------

.. tip::

    In this section, service configuration files are referred to as *resources*.
    This is to highlight the fact that, while most configuration resources
    will be files (e.g. YAML, XML, PHP), Symfony is so flexible that configuration
    could be loaded from anywhere (e.g. a database or even via an external
    web service).

The service container is built using a single configuration resource
(``app/config/config.yml`` by default). All other service configuration
(including the core Symfony and third-party bundle configuration) must
be imported from inside this file in one way or another. This gives you absolute
flexibility over the services in your application.

External service configuration can be imported in two different ways. The
first - and most common method - is via the ``imports`` directive. Later, you'll
learn about the second method, which is the flexible and preferred method
for importing service configuration from third-party bundles.

.. index::
   single: Service Container; Imports

.. _service-container-imports-directive:

Importing Configuration with ``imports``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So far, you've placed your ``my_mailer`` service container definition directly
in the application configuration file (e.g. ``app/config/config.yml``). Of
course, since the ``Mailer`` class itself lives inside the AcmeHelloBundle, it
makes more sense to put the ``my_mailer`` container definition inside the
bundle as well.

First, move the ``my_mailer`` container definition into a new container resource
file inside AcmeHelloBundle. If the ``Resources`` or ``Resources/config``
directories don't exist, create them.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            my_mailer.transport:  sendmail

        services:
            my_mailer:
                class:        Acme\HelloBundle\Mailer
                arguments:    ["%my_mailer.transport%"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="my_mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="my_mailer" class="Acme\HelloBundle\Mailer">
                    <argument>%my_mailer.transport%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('my_mailer.transport', 'sendmail');

        $container->setDefinition('my_mailer', new Definition(
            'Acme\HelloBundle\Mailer',
            array('%my_mailer.transport%')
        ));

The definition itself hasn't changed, only its location. Of course the service
container doesn't know about the new resource file. Fortunately, you can
easily import the resource file using the ``imports`` key in the application
configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: "@AcmeHelloBundle/Resources/config/services.yml" }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="@AcmeHelloBundle/Resources/config/services.xml"/>
            </imports>
        </container>

    .. code-block:: php

        // app/config/config.php
        $loader->import('@AcmeHelloBundle/Resources/config/services.php');

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

The ``imports`` directive allows your application to include service container
configuration resources from any other location (most commonly from bundles).
The ``resource`` location, for files, is the absolute path to the resource
file. The special ``@AcmeHelloBundle`` syntax resolves the directory path
of the AcmeHelloBundle bundle. This helps you specify the path to the resource
without worrying later if you move the AcmeHelloBundle to a different directory.

.. index::
   single: Service Container; Extension configuration

.. _service-container-extension-configuration:

Importing Configuration via Container Extensions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When developing in Symfony, you'll most commonly use the ``imports`` directive
to import container configuration from the bundles you've created specifically
for your application. Third-party bundle container configuration, including
Symfony core services, are usually loaded using another method that's more
flexible and easy to configure in your application.

Here's how it works. Internally, each bundle defines its services very much
like you've seen so far. Namely, a bundle uses one or more configuration
resource files (usually XML) to specify the parameters and services for that
bundle. However, instead of importing each of these resources directly from
your application configuration using the ``imports`` directive, you can simply
invoke a *service container extension* inside the bundle that does the work for
you. A service container extension is a PHP class created by the bundle author
to accomplish two things:

* import all service container resources needed to configure the services for
  the bundle;

* provide semantic, straightforward configuration so that the bundle can
  be configured without interacting with the flat parameters of the bundle's
  service container configuration.

In other words, a service container extension configures the services for
a bundle on your behalf. And as you'll see in a moment, the extension provides
a sensible, high-level interface for configuring the bundle.

Take the FrameworkBundle - the core Symfony framework bundle - as an
example. The presence of the following code in your application configuration
invokes the service container extension inside the FrameworkBundle:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            secret:          xxxxxxxxxx
            form:            true
            csrf_protection: true
            router:        { resource: "%kernel.root_dir%/config/routing.yml" }
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config secret="xxxxxxxxxx">
                <framework:form />
                <framework:csrf-protection />
                <framework:router resource="%kernel.root_dir%/config/routing.xml" />
                <!-- ... -->
            </framework>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'secret'          => 'xxxxxxxxxx',
            'form'            => array(),
            'csrf-protection' => array(),
            'router'          => array(
                'resource' => '%kernel.root_dir%/config/routing.php',
            ),

            // ...
        ));

When the configuration is parsed, the container looks for an extension that
can handle the ``framework`` configuration directive. The extension in question,
which lives in the FrameworkBundle, is invoked and the service configuration
for the FrameworkBundle is loaded. If you remove the ``framework`` key
from your application configuration file entirely, the core Symfony services
won't be loaded. The point is that you're in control: the Symfony framework
doesn't contain any magic or perform any actions that you don't have control
over.

Of course you can do much more than simply "activate" the service container
extension of the FrameworkBundle. Each extension allows you to easily
customize the bundle, without worrying about how the internal services are
defined.

In this case, the extension allows you to customize the ``error_handler``,
``csrf_protection``, ``router`` configuration and much more. Internally,
the FrameworkBundle uses the options specified here to define and configure
the services specific to it. The bundle takes care of creating all the necessary
``parameters`` and ``services`` for the service container, while still allowing
much of the configuration to be easily customized. As a bonus, most
service container extensions are also smart enough to perform validation -
notifying you of options that are missing or the wrong data type.

When installing or configuring a bundle, see the bundle's documentation for
how the services for the bundle should be installed and configured. The options
available for the core bundles can be found inside the :doc:`Reference Guide </reference/index>`.

.. note::

   Natively, the service container only recognizes the ``parameters``,
   ``services``, and ``imports`` directives. Any other directives
   are handled by a service container extension.

If you want to expose user friendly configuration in your own bundles, read the
":doc:`/cookbook/bundles/extension`" cookbook recipe.

.. index::
   single: Service Container; Referencing services

Referencing (Injecting) Services
--------------------------------

So far, the original ``my_mailer`` service is simple: it takes just one argument
in its constructor, which is easily configurable. As you'll see, the real
power of the container is realized when you need to create a service that
depends on one or more other services in the container.

As an example, suppose you have a new service, ``NewsletterManager``,
that helps to manage the preparation and delivery of an email message to
a collection of addresses. Of course the ``my_mailer`` service is already
really good at delivering email messages, so you'll use it inside ``NewsletterManager``
to handle the actual delivery of the messages. This pretend class might look
something like this::

    // src/Acme/HelloBundle/Newsletter/NewsletterManager.php
    namespace Acme\HelloBundle\Newsletter;

    use Acme\HelloBundle\Mailer;

    class NewsletterManager
    {
        protected $mailer;

        public function __construct(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

Without using the service container, you can create a new ``NewsletterManager``
fairly easily from inside a controller::

    use Acme\HelloBundle\Newsletter\NewsletterManager;

    // ...

    public function sendNewsletterAction()
    {
        $mailer = $this->get('my_mailer');
        $newsletter = new NewsletterManager($mailer);
        // ...
    }

This approach is fine, but what if you decide later that the ``NewsletterManager``
class needs a second or third constructor argument? What if you decide to
refactor your code and rename the class? In both cases, you'd need to find every
place where the ``NewsletterManager`` is instantiated and modify it. Of course,
the service container gives you a much more appealing option:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            my_mailer:
                # ...

            newsletter_manager:
                class:     Acme\HelloBundle\Newsletter\NewsletterManager
                arguments: ["@my_mailer"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="my_mailer">
                <!-- ... -->
                </service>

                <service id="newsletter_manager" class="Acme\HelloBundle\Newsletter\NewsletterManager">
                    <argument type="service" id="my_mailer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('my_mailer', ...);

        $container->setDefinition('newsletter_manager', new Definition(
            'Acme\HelloBundle\Newsletter\NewsletterManager',
            array(new Reference('my_mailer'))
        ));

In YAML, the special ``@my_mailer`` syntax tells the container to look for
a service named ``my_mailer`` and to pass that object into the constructor
of ``NewsletterManager``. In this case, however, the specified service ``my_mailer``
must exist. If it does not, an exception will be thrown. You can mark your
dependencies as optional - this will be discussed in the next section.

Using references is a very powerful tool that allows you to create independent service
classes with well-defined dependencies. In this example, the ``newsletter_manager``
service needs the ``my_mailer`` service in order to function. When you define
this dependency in the service container, the container takes care of all
the work of instantiating the classes.

.. _book-services-expressions:

Using the Expression Language
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The service container also supports an "expression" that allows you to inject
very specific values into a service.

For example, suppose you have a third service (not shown here), called ``mailer_configuration``,
which has a ``getMailerMethod()`` method on it, which will return a string
like ``sendmail`` based on some configuration. Remember that the first argument
to the ``my_mailer`` service is the simple string ``sendmail``:

.. include:: includes/_service_container_my_mailer.rst.inc

But instead of hardcoding this, how could we get this value from the ``getMailerMethod()``
of the new ``mailer_configuration`` service? One way is to use an expression:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            my_mailer:
                class:        Acme\HelloBundle\Mailer
                arguments:    ["@=service('mailer_configuration').getMailerMethod()"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
            >

            <services>
                <service id="my_mailer" class="Acme\HelloBundle\Mailer">
                    <argument type="expression">service('mailer_configuration').getMailerMethod()</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\ExpressionLanguage\Expression;

        $container->setDefinition('my_mailer', new Definition(
            'Acme\HelloBundle\Mailer',
            array(new Expression('service("mailer_configuration").getMailerMethod()'))
        ));

To learn more about the expression language syntax, see :doc:`/components/expression_language/syntax`.

In this context, you have access to 2 functions:

``service``
    Returns a given service (see the example above).
``parameter``
    Returns a specific parameter value (syntax is just like ``service``).

You also have access to the :class:`Symfony\\Component\\DependencyInjection\\ContainerBuilder`
via a ``container`` variable. Here's another example:

.. configuration-block::

    .. code-block:: yaml

        services:
            my_mailer:
                class:     Acme\HelloBundle\Mailer
                arguments: ["@=container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
            >

            <services>
                <service id="my_mailer" class="Acme\HelloBundle\Mailer">
                    <argument type="expression">container.hasParameter('some_param') ? parameter('some_param') : 'default_value'</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\ExpressionLanguage\Expression;

        $container->setDefinition('my_mailer', new Definition(
            'Acme\HelloBundle\Mailer',
            array(new Expression(
                "container.hasParameter('some_param') ? parameter('some_param') : 'default_value'"
            ))
        ));

Expressions can be used in ``arguments``, ``properties``, as arguments with
``configurator`` and as arguments to ``calls`` (method calls).

Optional Dependencies: Setter Injection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Injecting dependencies into the constructor in this manner is an excellent
way of ensuring that the dependency is available to use. If you have optional
dependencies for a class, then "setter injection" may be a better option. This
means injecting the dependency using a method call rather than through the
constructor. The class would look like this::

    namespace Acme\HelloBundle\Newsletter;

    use Acme\HelloBundle\Mailer;

    class NewsletterManager
    {
        protected $mailer;

        public function setMailer(Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

Injecting the dependency by the setter method just needs a change of syntax:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            my_mailer:
                # ...

            newsletter_manager:
                class:     Acme\HelloBundle\Newsletter\NewsletterManager
                calls:
                    - [setMailer, ["@my_mailer"]]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="my_mailer">
                <!-- ... -->
                </service>

                <service id="newsletter_manager" class="Acme\HelloBundle\Newsletter\NewsletterManager">
                    <call method="setMailer">
                        <argument type="service" id="my_mailer" />
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('my_mailer', ...);

        $container->setDefinition('newsletter_manager', new Definition(
            'Acme\HelloBundle\Newsletter\NewsletterManager'
        ))->addMethodCall('setMailer', array(
            new Reference('my_mailer'),
        ));

.. note::

    The approaches presented in this section are called "constructor injection"
    and "setter injection". The Symfony service container also supports
    "property injection".

.. _book-container-request-stack:

Injecting the Request
~~~~~~~~~~~~~~~~~~~~~

As of Symfony 2.4, instead of injecting the ``request`` service, you should
inject the ``request_stack`` service and access the ``Request`` by calling
the :method:`Symfony\\Component\\HttpFoundation\\RequestStack::getCurrentRequest`
method::

    namespace Acme\HelloBundle\Newsletter;

    use Symfony\Component\HttpFoundation\RequestStack;

    class NewsletterManager
    {
        protected $requestStack;

        public function __construct(RequestStack $requestStack)
        {
            $this->requestStack = $requestStack;
        }

        public function anyMethod()
        {
            $request = $this->requestStack->getCurrentRequest();
            // ... do something with the request
        }

        // ...
    }

Now, just inject the ``request_stack``, which behaves like any normal service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            newsletter_manager:
                class:     Acme\HelloBundle\Newsletter\NewsletterManager
                arguments: ["@request_stack"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service
                    id="newsletter_manager"
                    class="Acme\HelloBundle\Newsletter\NewsletterManager"
                >
                    <argument type="service" id="request_stack"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setDefinition('newsletter_manager', new Definition(
            'Acme\HelloBundle\Newsletter\NewsletterManager',
            array(new Reference('request_stack'))
        ));

.. sidebar:: Why not Inject the ``request`` Service?

    Almost all Symfony2 built-in services behave in the same way: a single
    instance is created by the container which it returns whenever you get it or
    when it is injected into another service. There is one exception in a standard
    Symfony2 application: the ``request`` service.

    If you try to inject the ``request`` into a service, you will probably receive
    a
    :class:`Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
    exception. That's because the ``request`` can **change** during the life-time
    of a container (when a sub-request is created for instance).


.. tip::

    If you define a controller as a service then you can get the ``Request``
    object without injecting the container by having it passed in as an
    argument of your action method. See
    :ref:`book-controller-request-argument` for details.

Making References optional
--------------------------

Sometimes, one of your services may have an optional dependency, meaning
that the dependency is not required for your service to work properly. In
the example above, the ``my_mailer`` service *must* exist, otherwise an exception
will be thrown. By modifying the ``newsletter_manager`` service definition,
you can make this reference optional. The container will then inject it if
it exists and do nothing if it doesn't:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            newsletter_manager:
                class:     Acme\HelloBundle\Newsletter\NewsletterManager
                arguments: ["@?my_mailer"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="my_mailer">
                <!-- ... -->
                </service>

                <service id="newsletter_manager" class="Acme\HelloBundle\Newsletter\NewsletterManager">
                    <argument type="service" id="my_mailer" on-invalid="ignore" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        $container->setDefinition('my_mailer', ...);

        $container->setDefinition('newsletter_manager', new Definition(
            'Acme\HelloBundle\Newsletter\NewsletterManager',
            array(
                new Reference(
                    'my_mailer',
                    ContainerInterface::IGNORE_ON_INVALID_REFERENCE
                )
            )
        ));

In YAML, the special ``@?`` syntax tells the service container that the dependency
is optional. Of course, the ``NewsletterManager`` must also be rewritten to
allow for an optional dependency::

        public function __construct(Mailer $mailer = null)
        {
            // ...
        }

Core Symfony and Third-Party Bundle Services
--------------------------------------------

Since Symfony and all third-party bundles configure and retrieve their services
via the container, you can easily access them or even use them in your own
services. To keep things simple, Symfony by default does not require that
controllers be defined as services. Furthermore, Symfony injects the entire
service container into your controller. For example, to handle the storage of
information on a user's session, Symfony provides a ``session`` service,
which you can access inside a standard controller as follows::

    public function indexAction($bar)
    {
        $session = $this->get('session');
        $session->set('foo', $bar);

        // ...
    }

In Symfony, you'll constantly use services provided by the Symfony core or
other third-party bundles to perform tasks such as rendering templates (``templating``),
sending emails (``mailer``), or accessing information on the request (``request``).

You can take this a step further by using these services inside services that
you've created for your application. Beginning by modifying the ``NewsletterManager``
to use the real Symfony ``mailer`` service (instead of the pretend ``my_mailer``).
Also pass the templating engine service to the ``NewsletterManager``
so that it can generate the email content via a template::

    namespace Acme\HelloBundle\Newsletter;

    use Symfony\Component\Templating\EngineInterface;

    class NewsletterManager
    {
        protected $mailer;

        protected $templating;

        public function __construct(
            \Swift_Mailer $mailer,
            EngineInterface $templating
        ) {
            $this->mailer = $mailer;
            $this->templating = $templating;
        }

        // ...
    }

Configuring the service container is easy:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            newsletter_manager:
                class:     Acme\HelloBundle\Newsletter\NewsletterManager
                arguments: ["@mailer", "@templating"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service id="newsletter_manager" class="Acme\HelloBundle\Newsletter\NewsletterManager">
                <argument type="service" id="mailer"/>
                <argument type="service" id="templating"/>
            </service>
        </container>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        $container->setDefinition('newsletter_manager', new Definition(
            'Acme\HelloBundle\Newsletter\NewsletterManager',
            array(
                new Reference('mailer'),
                new Reference('templating'),
            )
        ));

The ``newsletter_manager`` service now has access to the core ``mailer``
and ``templating`` services. This is a common way to create services specific
to your application that leverage the power of different services within
the framework.

.. tip::

    Be sure that the ``swiftmailer`` entry appears in your application
    configuration. As was mentioned in :ref:`service-container-extension-configuration`,
    the ``swiftmailer`` key invokes the service extension from the
    SwiftmailerBundle, which registers the ``mailer`` service.

.. _book-service-container-tags:

Tags
----

In the same way that a blog post on the Web might be tagged with things such
as "Symfony" or "PHP", services configured in your container can also be
tagged. In the service container, a tag implies that the service is meant
to be used for a specific purpose. Take the following example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            foo.twig.extension:
                class: Acme\HelloBundle\Extension\FooExtension
                public: false
                tags:
                    -  { name: twig.extension }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service
                id="foo.twig.extension"
                class="Acme\HelloBundle\Extension\FooExtension"
                public="false">

                <tag name="twig.extension" />
            </service>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Acme\HelloBundle\Extension\FooExtension');
        $definition->setPublic(false);
        $definition->addTag('twig.extension');
        $container->setDefinition('foo.twig.extension', $definition);

The ``twig.extension`` tag is a special tag that the TwigBundle uses
during configuration. By giving the service this ``twig.extension`` tag,
the bundle knows that the ``foo.twig.extension`` service should be registered
as a Twig extension with Twig. In other words, Twig finds all services tagged
with ``twig.extension`` and automatically registers them as extensions.

Tags, then, are a way to tell Symfony or other third-party bundles that
your service should be registered or used in some special way by the bundle.

For a list of all the tags available in the core Symfony Framework, check
out :doc:`/reference/dic_tags`. Each of these has a different effect on your
service and many tags require additional arguments (beyond just the ``name``
parameter).

Debugging Services
------------------

You can find out what services are registered with the container using the
console. To show all services and the class for each service, run:

.. code-block:: bash

    $ php app/console debug:container

.. versionadded:: 2.6
    Prior to Symfony 2.6, this command was called ``container:debug``.

By default, only public services are shown, but you can also view private services:

.. code-block:: bash

    $ php app/console debug:container --show-private

.. note::

    If a private service is only used as an argument to just *one* other service,
    it won't be displayed by the ``debug:container`` command, even when using
    the ``--show-private`` option. See :ref:`Inline Private Services <inlined-private-services>`
    for more details.

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: bash

    $ php app/console debug:container my_mailer

Learn more
----------

* :doc:`/components/dependency_injection/parameters`
* :doc:`/components/dependency_injection/compilation`
* :doc:`/components/dependency_injection/definitions`
* :doc:`/components/dependency_injection/factories`
* :doc:`/components/dependency_injection/parentservices`
* :doc:`/components/dependency_injection/tags`
* :doc:`/cookbook/controller/service`
* :doc:`/cookbook/service_container/scopes`
* :doc:`/cookbook/service_container/compiler_passes`
* :doc:`/components/dependency_injection/advanced`

.. _`service-oriented architecture`: http://wikipedia.org/wiki/Service-oriented_architecture
