.. index::
   single: Dependency Injection; Scopes

How to work with Scopes
=======================

This entry is all about scopes, a somewhat advanced topic related to the
:doc:`/book/service_container`. If you've ever gotten an error mentioning
"scopes" when creating services, or need to create a service that depends
on the ``request`` service, then this entry is for you.

Understanding Scopes
--------------------

The scope of a service controls how long an instance of a service is used
by the container. The Dependency Injection component provides two generic
scopes:

- ``container`` (the default one): The same instance is used each time you
  request it from this container.

- ``prototype``: A new instance is created each time you request the service.

The
:class:`Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel`
also defines a third scope: ``request``. This scope is tied to the request,
meaning a new instance is created for each subrequest and is unavailable
outside the request (for instance in the CLI).

Scopes add a constraint on the dependencies of a service: a service cannot
depend on services from a narrower scope. For example, if you create a generic
``my_foo`` service, but try to inject the ``request`` service, you will receive
a :class:`Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
when compiling the container. Read the sidebar below for more details.

.. sidebar:: Scopes and Dependencies

    Imagine you've configured a ``my_mailer`` service. You haven't configured
    the scope of the service, so it defaults to ``container``. In other words,
    every time you ask the container for the ``my_mailer`` service, you get
    the same object back. This is usually how you want your services to work.

    Imagine, however, that you need the ``request`` service in your ``my_mailer``
    service, maybe because you're reading the URL of the current request.
    So, you add it as a constructor argument. Let's look at why this presents
    a problem:

    * When requesting ``my_mailer``, an instance of ``my_mailer`` (let's call
      it *MailerA*) is created and the ``request`` service (let's call it
      *RequestA*) is passed to it. Life is good!

    * You've now made a subrequest in Symfony, which is a fancy way of saying
      that you've called, for example, the ``{{ render(...) }}`` Twig function,
      which executes another controller. Internally, the old ``request`` service
      (*RequestA*) is actually replaced by a new request instance (*RequestB*).
      This happens in the background, and it's totally normal.

    * In your embedded controller, you once again ask for the ``my_mailer``
      service. Since your service is in the ``container`` scope, the same
      instance (*MailerA*) is just re-used. But here's the problem: the
      *MailerA* instance still contains the old *RequestA* object, which
      is now **not** the correct request object to have (*RequestB* is now
      the current ``request`` service). This is subtle, but the mis-match could
      cause major problems, which is why it's not allowed.

      So, that's the reason *why* scopes exist, and how they can cause
      problems. Keep reading to find out the common solutions.

.. note::

    A service can of course depend on a service from a wider scope without
    any issue.

Using a Service from a narrower Scope
-------------------------------------

If your service has a dependency on a scoped service (like the ``request``),
you have three ways to deal with it:

* Use setter injection if the dependency is "synchronized"; this is the
  recommended way and the best solution for the ``request`` instance as it is
  synchronized with the ``request`` scope (see
  :ref:`using-synchronized-service`).

* Put your service in the same scope as the dependency (or a narrower one). If
  you depend on the ``request`` service, this means putting your new service
  in the ``request`` scope (see :ref:`changing-service-scope`);

* Pass the entire container to your service and retrieve your dependency from
  the container each time you need it to be sure you have the right instance
  -- your service can live in the default ``container`` scope (see
  :ref:`passing-container`);

Each scenario is detailed in the following sections.

.. _using-synchronized-service:

Using a synchronized Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    Synchronized services are new in Symfony 2.3.

Injecting the container or setting your service to a narrower scope have
drawbacks. For synchronized services (like the ``request``), using setter
injection is the best option as it has no drawbacks and everything works
without any special code in your service or in your definition::

    // src/Acme/HelloBundle/Mail/Mailer.php
    namespace Acme\HelloBundle\Mail;

    use Symfony\Component\HttpFoundation\Request;

    class Mailer
    {
        protected $request;

        public function setRequest(Request $request = null)
        {
            $this->request = $request;
        }

        public function sendEmail()
        {
            if (null === $this->request) {
                // throw an error?
            }

            // ... do something using the request here
        }
    }

Whenever the ``request`` scope is entered or left, the service container will
automatically call the ``setRequest()`` method with the current ``request``
instance.

You might have noticed that the ``setRequest()`` method accepts ``null`` as a
valid value for the ``request`` argument. That's because when leaving the
``request`` scope, the ``request`` instance can be ``null`` (for the master
request for instance). Of course, you should take care of this possibility in
your code. This should also be taken into account when declaring your service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            greeting_card_manager:
                class: Acme\HelloBundle\Mail\GreetingCardManager
                calls:
                    - [setRequest, ['@?request=']]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <services>
            <service id="greeting_card_manager"
                class="Acme\HelloBundle\Mail\GreetingCardManager"
            />
            <call method="setRequest">
                <argument type="service" id="request" on-invalid="null" strict="false" />
            </call>
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        $definition = $container->setDefinition(
            'greeting_card_manager',
            new Definition('Acme\HelloBundle\Mail\GreetingCardManager')
        )
        ->addMethodCall('setRequest', array(
            new Reference('request', ContainerInterface::NULL_ON_INVALID_REFERENCE, false)
        ));

.. tip::

    You can declare your own ``synchronized`` services very easily; here is
    the declaration of the ``request`` service for reference:

    .. configuration-block::

        .. code-block:: yaml

            services:
                request:
                    scope: request
                    synthetic: true
                    synchronized: true

        .. code-block:: xml

            <services>
                <service id="request" scope="request" synthetic="true" synchronized="true" />
            </services>

        .. code-block:: php

            use Symfony\Component\DependencyInjection\Definition;
            use Symfony\Component\DependencyInjection\ContainerInterface;

            $definition = $container->setDefinition('request')
                ->setScope('request')
                ->setSynthetic(true)
                ->setSynchronized(true);

.. _changing-service-scope:

Changing the Scope of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the scope of a service should be done in its definition:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            greeting_card_manager:
                class: Acme\HelloBundle\Mail\GreetingCardManager
                scope: request
                arguments: [@request]

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <services>
            <service id="greeting_card_manager"
                class="Acme\HelloBundle\Mail\GreetingCardManager"
                scope="request"
            />
            <argument type="service" id="request" />
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = $container->setDefinition(
            'greeting_card_manager',
            new Definition(
                'Acme\HelloBundle\Mail\GreetingCardManager',
                array(new Reference('request'),
            ))
        )->setScope('request');

.. _passing-container:

Passing the Container as a Dependency of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
            $request = $this->container->get('request');
            // ... do something using the request here
        }
    }

.. caution::

    Take care not to store the request in a property of the object for a
    future call of the service as it would cause the same issue described
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

.. tip::

    If you define a controller as a service then you can get the ``Request``
    object without injecting the container by having it passed in as an
    argument of your action method. See
    :ref:`book-controller-request-argument` for details.
