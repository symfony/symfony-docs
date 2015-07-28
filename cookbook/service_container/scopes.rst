.. index::
   single: DependencyInjection; Scopes

How to Work with Scopes
=======================

.. caution::

    The "container scopes" concept explained in this article has been deprecated
    in Symfony 2.8 and it will be removed in Symfony 3.0.

This article is all about scopes, a somewhat advanced topic related to the
:doc:`/book/service_container`. If you've ever gotten an error mentioning
"scopes" when creating services, then this article is for you.

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

``container`` (the default one):
    The same instance is used each time you ask for it from this container.

``prototype``:
    A new instance is created each time you ask for the service.

The
:class:`Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel`
also defines a third scope: ``request``. This scope is tied to the request,
meaning a new instance is created for each subrequest and is unavailable
outside the request (for instance in the CLI).

An Example: Client Scope
~~~~~~~~~~~~~~~~~~~~~~~~

Other than the ``request`` service (which has a simple solution, see the
above note), no services in the default Symfony container belong to any
scope other than ``container`` and ``prototype``. But for the purposes of
this article, imagine there is another scope ``client`` and a service ``client_configuration``
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
      you've designed your application in such a way that you handle this
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

There are two solutions to the scope problem:

* A) Put your service in the same scope as the dependency (or a narrower one). If
  you depend on the ``client_configuration`` service, this means putting your
  new service in the ``client`` scope (see :ref:`changing-service-scope`);

* B) Pass the entire container to your service and retrieve your dependency from
  the container each time you need it to be sure you have the right instance
  -- your service can live in the default ``container`` scope (see
  :ref:`passing-container`).

Each scenario is detailed in the following sections.

.. _using-synchronized-service:

.. note::

    Prior to Symfony 2.7, there was another alternative based on ``synchronized``
    services. However, these kind of services have been deprecated starting from
    Symfony 2.7.

.. _changing-service-scope:

A) Changing the Scope of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the scope of a service should be done in its definition. This example
assumes that the ``Mailer`` class has a ``__construct`` function whose first
argument is the ``ClientConfiguration`` object:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            my_mailer:
                class: AppBundle\Mail\Mailer
                scope: client
                arguments: ["@client_configuration"]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="my_mailer"
                    class="AppBundle\Mail\Mailer"
                    scope="client">
                    <argument type="service" id="client_configuration" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = $container->setDefinition(
            'my_mailer',
            new Definition(
                'AppBundle\Mail\Mailer',
                array(new Reference('client_configuration'),
            ))
        )->setScope('client');

.. _passing-container:

B) Passing the Container as a Dependency of your Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Setting the scope to a narrower one is not always possible (for instance, a
twig extension must be in the ``container`` scope as the Twig environment
needs it as a dependency). In these cases, you can pass the entire container
into your service::

    // src/AppBundle/Mail/Mailer.php
    namespace AppBundle\Mail;

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

The service configuration for this class would look something like this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            my_mailer:
                class:     AppBundle\Mail\Mailer
                arguments: ["@service_container"]
                # scope: container can be omitted as it is the default

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="my_mailer" class="AppBundle\Mail\Mailer">
                 <argument type="service" id="service_container" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('my_mailer', new Definition(
            'AppBundle\Mail\Mailer',
            array(new Reference('service_container'))
        ));

.. note::

    Injecting the whole container into a service is generally not a good
    idea (only inject what you need).
