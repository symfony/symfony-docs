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
    the :doc:`DependencyInjection component documentation </components/dependency_injection>`.

.. index::
   single: Service Container; What is a service?

What is a Service?
------------------

Put simply, a service is any PHP object that performs some sort of "global"
task. It's a purposefully-generic name used in computer science to describe an
object that's created for a specific purpose (e.g. delivering emails). Each
service is used throughout your application whenever you need the specific
functionality it provides. You don't have to do anything special to make a
service: simply write a PHP class with some code that accomplishes a specific
task. Congratulations, you've just created a service!

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

A service container (or *dependency injection container*) is simply a PHP
object that manages the instantiation of services (i.e. objects).

For example, suppose you have a simple PHP class that delivers email messages.
Without a service container, you must manually create the object whenever
you need it::

    use AppBundle\Mailer;

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

.. include:: /_includes/service_container/_my_mailer.rst.inc

.. note::

    When Symfony initializes, it builds the service container using the
    application configuration (``app/config/config.yml`` by default). The
    exact file that's loaded is dictated by the ``AppKernel::registerContainerConfiguration()``
    method, which loads an environment-specific configuration file (e.g.
    ``config_dev.yml`` for the ``dev`` environment or ``config_prod.yml``
    for ``prod``).

An instance of the ``AppBundle\Mailer`` class is now available via the service
container. The container is available in any traditional Symfony controller
where you can access the services of the container via the ``get()`` shortcut
method::

    class HelloController extends Controller
    {
        // ...

        public function sendEmailAction()
        {
            // ...
            $mailer = $this->get('app.mailer');
            $mailer->send('ryan@foobar.net', ...);
        }
    }

When you ask for the ``app.mailer`` service from the container, the container
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
":doc:`/service_container/scopes`" cookbook article.

.. note::

    In this example, the controller extends Symfony's base Controller, which
    gives you access to the service container itself. You can then use the
    ``get`` method to locate and retrieve the ``app.mailer`` service from
    the service container.

.. _book-service-container-parameters:

Service Parameters
------------------

The creation of new services (i.e. objects) via the container is pretty
straightforward. Parameters make defining services more organized and flexible:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        parameters:
            app.mailer.transport:  sendmail

        services:
            app.mailer:
                class:        AppBundle\Mailer
                arguments:    ['%app.mailer.transport%']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="app.mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="app.mailer" class="AppBundle\Mailer">
                    <argument>%app.mailer.transport%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('app.mailer.transport', 'sendmail');

        $container->setDefinition('app.mailer', new Definition(
            'AppBundle\Mailer',
            array('%app.mailer.transport%')
        ));

The end result is exactly the same as before - the difference is only in
*how* you defined the service. By enclosing the ``app.mailer.transport``
string with percent (``%``) signs, the container knows to look for a parameter
with that name. When the container is built, it looks up the value of each
parameter and uses it in the service definition.

.. note::

    If you want to use a string that starts with an ``@`` sign as a parameter
    value (e.g. a very safe mailer password) in a YAML file, you need to escape
    it by adding another ``@`` sign (this only applies to the YAML format):

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            # This will be parsed as string '@securepass'
            mailer_password: '@@securepass'

.. note::

    The percent sign inside a parameter or argument, as part of the string, must
    be escaped with another percent sign:

    .. code-block:: xml

        <argument type="string">http://symfony.com/?foo=%%s&amp;bar=%%d</argument>

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

.. index::
   single: Service Container; Referencing services

Referencing (Injecting) Services
--------------------------------

So far, the original ``app.mailer`` service is simple: it takes just one argument
in its constructor, which is easily configurable. As you'll see, the real
power of the container is realized when you need to create a service that
depends on one or more other services in the container.

As an example, suppose you have a new service, ``NewsletterManager``,
that helps to manage the preparation and delivery of an email message to
a collection of addresses. Of course the ``app.mailer`` service is already
really good at delivering email messages, so you'll use it inside ``NewsletterManager``
to handle the actual delivery of the messages. This pretend class might look
something like this::

    // src/AppBundle/Newsletter/NewsletterManager.php
    namespace AppBundle\Newsletter;

    use AppBundle\Mailer;

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

    use AppBundle\Newsletter\NewsletterManager;

    // ...

    public function sendNewsletterAction()
    {
        $mailer = $this->get('app.mailer');
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

        # app/config/services.yml
        services:
            app.mailer:
                # ...

            app.newsletter_manager:
                class:     AppBundle\Newsletter\NewsletterManager
                arguments: ['@app.mailer']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer">
                <!-- ... -->
                </service>

                <service id="app.newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <argument type="service" id="app.mailer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.mailer', ...);

        $container->setDefinition('app.newsletter_manager', new Definition(
            'AppBundle\Newsletter\NewsletterManager',
            array(new Reference('app.mailer'))
        ));

In YAML, the special ``@app.mailer`` syntax tells the container to look for
a service named ``app.mailer`` and to pass that object into the constructor
of ``NewsletterManager``. In this case, however, the specified service ``app.mailer``
must exist. If it does not, an exception will be thrown. You can mark your
dependencies as optional - this will be discussed in the next section.

Using references is a very powerful tool that allows you to create independent service
classes with well-defined dependencies. In this example, the ``app.newsletter_manager``
service needs the ``app.mailer`` service in order to function. When you define
this dependency in the service container, the container takes care of all
the work of instantiating the classes.

Optional Dependencies: Setter Injection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Injecting dependencies into the constructor in this manner is an excellent
way of ensuring that the dependency is available to use. If you have optional
dependencies for a class, then "setter injection" may be a better option. This
means injecting the dependency using a method call rather than through the
constructor. The class would look like this::

    namespace AppBundle\Newsletter;

    use AppBundle\Mailer;

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

        # app/config/services.yml
        services:
            app.mailer:
                # ...

            app.newsletter_manager:
                class:     AppBundle\Newsletter\NewsletterManager
                calls:
                    - [setMailer, ['@app.mailer']]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer">
                <!-- ... -->
                </service>

                <service id="app.newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <call method="setMailer">
                        <argument type="service" id="app.mailer" />
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.mailer', ...);

        $container->setDefinition('app.newsletter_manager', new Definition(
            'AppBundle\Newsletter\NewsletterManager'
        ))->addMethodCall('setMailer', array(
            new Reference('app.mailer'),
        ));

.. note::

    The approaches presented in this section are called "constructor injection"
    and "setter injection". The Symfony service container also supports
    "property injection".

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /service_container/*

* :doc:`/components/dependency_injection/parameters`
* :doc:`/components/dependency_injection/compilation`
* :doc:`/components/dependency_injection/definitions`
* :doc:`/components/dependency_injection/factories`
* :doc:`/components/dependency_injection/parentservices`
* :doc:`/components/dependency_injection/advanced`

.. _`service-oriented architecture`: https://en.wikipedia.org/wiki/Service-oriented_architecture
