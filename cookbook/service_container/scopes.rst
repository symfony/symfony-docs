.. index::
   single: DependencyInjection; Scopes

How to Work with Scopes
=======================

This entry is all about scopes, a somewhat advanced topic related to the
:doc:`/book/service_container`. If you've ever gotten an error mentioning
"scopes" when creating services, then this entry is for you.

.. note::

    If you are trying to inject the ``request`` service, the simple solution
    is to inject the ``request_stack`` service instead and access the current
    Request by calling the
    :method:`Symfony\\Component\\HttpFoundation\\RequestStack::getCurrentRequest`
    method (see :ref:`book-container-request-stack`). The rest of this entry
    talks about scopes in a theoretical and more advanced way. If you're
    dealing with scopes for the ``request`` service, simply inject ``request_stack``.

Understanding Scopes
--------------------

The scope of a service controls how long an instance of a service is used
by the container. The DependencyInjection component provides two generic
scopes:

- ``container`` (the default one): The same instance is used each time you
  request it from this container.

- ``prototype``: A new instance is created each time you request the service.

The
:class:`Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel`
also defines a third scope: ``request``. This scope is tied to the request,
meaning a new instance is created for each subrequest and is unavailable
outside the request (for instance in the CLI).

An Example: Client Scope
~~~~~~~~~~~~~~~~~~~~~~~~

Other than the ``request`` service (which has a simple solution, see the
above note), no services in the default Symfony2 container belong to any
scope other than ``container`` and ``prototype``. But for the purposes of
this entry, imagine there is another scope ``client`` and a service ``client_configuration``
that belongs to it. This is not a common situation, but the idea is that
you may enter and exit multiple ``client`` scopes during a request, and each
has its own ``client_configuration`` service.

Scopes add a constraint on the dependencies of a service: a service cannot
depend on services from a narrower scope. For example, if you create a generic
``my_foo`` service, but try to inject the ``client_configuration`` service,
you will receive a
:class:`Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
when compiling the container. Read the sidebar below for more details.

.. sidebar:: Scopes and Dependencies

    Imagine you've configured a ``my_mailer`` service. You haven't configured
    the scope of the service, so it defaults to ``container``. In other words,
    every time you ask the container for the ``my_mailer`` service, you get
    the same object back. This is usually how you want your services to work.

    Imagine, however, that you need the ``client_configuration`` service
    in your ``my_mailer`` service, maybe because you're reading some details
    from it, such as what the "sender" address should be. You add it as a
    constructor argument. There are several reasons why this presents a problem:

    * When requesting ``my_mailer``, an instance of ``my_mailer`` (called
      *MailerA* here) is created and the ``client_configuration`` service (
      called *ConfigurationA* here) is passed to it. Life is good!

    * Your application now needs to do something with another client, and
      you've architected your application in such a way that you handle this
      by entering a new ``client_configuration`` scope and setting a new
      ``client_configuration`` service into the container. Call this
      *ConfigurationB*.

    * Somewhere in your application, you once again ask for the ``my_mailer``
      service. Since your service is in the ``container`` scope, the same
      instance (*MailerA*) is just re-used. But here's the problem: the
      *MailerA* instance still contains the old *ConfigurationA* object, which
      is now **not** the correct configuration object to have (*ConfigurationB*
      is now the current ``client_configuration`` service). This is subtle,
      but the mis-match could cause major problems, which is why it's not
      allowed.

      So, that's the reason *why* scopes exist, and how they can cause
      problems. Keep reading to find out the common solutions.

.. note::

    A service can of course depend on a service from a wider scope without
    any issue.

Using a Service from a Narrower Scope
-------------------------------------

There are several solutions to the scope problem:

* A) Use setter injection if the dependency is ``synchronized`` (see
  :ref:`using-synchronized-service`);

* B) Put your service in the same scope as the dependency (or a narrower one). If
  you depend on the ``client_configuration`` service, this means putting your
  new service in the ``client`` scope (see :ref:`changing-service-scope`);

* C) Pass the entire container to your service and retrieve your dependency from
  the container each time you need it to be sure you have the right instance
  -- your service can live in the default ``container`` scope (see
  :ref:`passing-container`).

Each scenario is detailed in the following sections.

.. _using-synchronized-service:

A) Using a Synchronized Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    Synchronized services were introduced in Symfony 2.3.

Both injecting the container and setting your service to a narrower scope have
drawbacks. Assume first that the ``client_configuration`` service has been
marked as ``synchronized``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            client_configuration:
                class:        Acme\HelloBundle\Client\ClientConfiguration
                scope:        client
                synchronized: true
                synthetic:    true
                # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
            >

            <services>
                <service
                    id="client_configuration"
                    scope="client"
                    synchronized="true"
                    synthetic="true"
                    class="Acme\HelloBundle\Client\ClientConfiguration"
                />
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(
            'Acme\HelloBundle\Client\ClientConfiguration',
            array()
        );
        $definition->setScope('client');
        $definition->setSynchronized(true);
        $definition->setSynthetic(true);
        $container->setDefinition('client_configuration', $definition);

Now, if you inject this service using setter injection, there are no drawbacks
and everything works without any special code in your service or in your definition::

    // src/Acme/HelloBundle/Mail/Mailer.php
    namespace Acme\HelloBundle\Mail;

    use Acme\HelloBundle\Client\ClientConfiguration;

    class Mailer
    {
        protected $clientConfiguration;

        public function setClientConfiguration(ClientConfiguration $clientConfiguration = null)
        {
            $this->clientConfiguration = $clientConfiguration;
        }

        public function sendEmail()
        {
            if (null === $this->clientConfiguration) {
                // throw an error?
            }

            // ... do something using the client configuration here
        }
    }

Whenever the ``client`` scope is active, the service container will
automatically call the ``setClientConfiguration()`` method when the
``client_configuration`` service is set in the container.

You might have noticed that the ``setClientConfiguration()`` method accepts
``null`` as a valid value for the ``client_configuration`` argument. That's
because when leaving the ``client`` scope, the ``client_configuration`` instance
can be ``null``. Of course, you should take care of this possibility in
your code. This should also be taken into account when declaring your service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            my_mailer:
                class: Acme\HelloBundle\Mail\Mailer
                calls:
                    - [setClientConfiguration, ["@?client_configuration="]]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <services>
            <service id="my_mailer"
                class="Acme\HelloBundle\Mail\Mailer"
            >
                <call method="setClientConfiguration">
                    <argument
                        type="service"
                        id="client_configuration"
                        on-invalid="null"
                        strict="false"
                    />
                </call>
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        $definition = $container->setDefinition(
            'my_mailer',
            new Definition('Acme\HelloBundle\Mail\Mailer')
        )
        ->addMethodCall('setClientConfiguration', array(
            new Reference(
                'client_configuration',
                ContainerInterface::NULL_ON_INVALID_REFERENCE,
                false
            )
        ));

.. _changing-service-scope:

B) Changing the Scope of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the scope of a service should be done in its definition. This example
assumes that the ``Mailer`` class has a ``__construct`` function whose first
argument is the ``ClientConfiguration`` object:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            my_mailer:
                class: Acme\HelloBundle\Mail\Mailer
                scope: client
                arguments: ["@client_configuration"]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <services>
            <service id="my_mailer"
                    class="Acme\HelloBundle\Mail\Mailer"
                    scope="client">
                    <argument type="service" id="client_configuration" />
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = $container->setDefinition(
            'my_mailer',
            new Definition(
                'Acme\HelloBundle\Mail\Mailer',
                array(new Reference('client_configuration'),
            ))
        )->setScope('client');

.. _passing-container:

C) Passing the Container as a Dependency of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Setting the scope to a narrower one is not always possible (for instance, a
twig extension must be in the ``container`` scope as the Twig environment
needs it as a dependency). In these cases, you can pass the entire container
into your service::

    // src/Acme/HelloBundle/Mail/Mailer.php
    namespace Acme\HelloBundle\Mail;

    use Symfony\Component\DependencyInjection\ContainerInterface;

    class Mailer
    {
        protected $container;

        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        public function sendEmail()
        {
            $request = $this->container->get('client_configuration');
            // ... do something using the client configuration here
        }
    }

.. caution::

    Take care not to store the client configuration in a property of the object
    for a future call of the service as it would cause the same issue described
    in the first section (except that Symfony cannot detect that you are
    wrong).

The service config for this class would look something like this:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        parameters:
            # ...
            my_mailer.class: Acme\HelloBundle\Mail\Mailer

        services:
            my_mailer:
                class:     "%my_mailer.class%"
                arguments: ["@service_container"]
                # scope: container can be omitted as it is the default

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <parameters>
            <!-- ... -->
            <parameter key="my_mailer.class">Acme\HelloBundle\Mail\Mailer</parameter>
        </parameters>

        <services>
            <service id="my_mailer" class="%my_mailer.class%">
                 <argument type="service" id="service_container" />
            </service>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('my_mailer.class', 'Acme\HelloBundle\Mail\Mailer');

        $container->setDefinition('my_mailer', new Definition(
            '%my_mailer.class%',
            array(new Reference('service_container'))
        ));

.. note::

    Injecting the whole container into a service is generally not a good
    idea (only inject what you need).
