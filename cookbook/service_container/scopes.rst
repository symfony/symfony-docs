How to Work with scopes
=======================

Understanding the Scopes
------------------------

The scope of a service controls how long an instance of a service is used
by the container. the Dependency Injection component provides the two generic
scopes:

- `container` (the default one): The same instance is used each time you
  request it from this container.
- `prototype`: A new instance is created each time you request the service.

FrameworkBundle defines the `request` scope between them. This scopes is
tied to the request, so a new instance will be created for each subrequest
and is unavailable outside the request (for instance in the CLI).

The scope adds a constraint on the dependencies of a service: a service cannot
depend on services from a narrower scope. Using such a pattern will lead
to a :class:Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
when compiling the container.

.. sidebar:: Understanding the constraint on the scope of dependencies

    Let's imagine that your service A has a dependency to a service B from
    a narrower scope. Here is what occurs:

    - When requesting A, an instance B1 is created for B and injected in A1.
    - When entering the new narrow scope (doing a subrequest for the `request`
      scope for instance), the container will need to create a B2 instance
      for the service B as B1 is now obsolete.
    - When requesting A, the container will reuse A1 (as it is still the
      good scope) which still contains the obsolete B1 instance.

.. note::

    A service can of course depend on a service from a wider scope without
    any issue.

Setting the Scope in the Definition
-----------------------------------

The scope of a service is defined in the definition of the service:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/services.yml
        services:
            greeting_card_manager:
                class: Acme\HelloBundle\Mail\GreetingCardManager
                scope: request

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
        <services>
            <service id="greeting_card_manager" class="Acme\HelloBundle\Mail\GreetingCardManager" scope="request" />
        </services>

    .. code-block:: php

        // src/Acme/HelloBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition(
            'greeting_card_manager',
            new Definition('Acme\HelloBundle\Mail\GreetingCardManager')
        )->setScope('request');

Using a Service from a narrower Scope
-------------------------------------

If your service depends of a scoped service, the best solution is to put
it in the same scope (or a narrower one). But this is not always possible
(for instance, a twig extension must be in the `container` scope as the Twig
environment needs it as a dependency).

Using a service from a narrower scope requires retrieving it from the container
each time we need it to be sure to have the good instance.

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
            // Do something using the request here
        }
    }

.. warning::

    Take care not to store the request in a property of the object for a
    future call of the service as it would be the same issue than described
    in the first section (except that symfony cannot detect that you are
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
                class:     %my_mailer.class%
                arguments:
                    - "@service_container"
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

    Injecting the whole container in a service is generally a sign of an
    issue in the design but this is a valid use case.
