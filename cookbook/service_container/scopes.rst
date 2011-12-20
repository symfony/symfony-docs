How to work with Scopes
=======================

This entry is all about scopes, a somewhat advanced topic related to the
:doc:`/book/service_container`. If you've ever gotten an error mentioning
"scopes" when creating services, or need to create a service that depends
on the `request` service, then this entry is for you.

Understanding Scopes
--------------------

The scope of a service controls how long an instance of a service is used
by the container. the Dependency Injection component provides two generic
scopes:

- `container` (the default one): The same instance is used each time you
  request it from this container.

- `prototype`: A new instance is created each time you request the service.

The FrameworkBundle also defines a third scope: `request`. This scopes is
tied to the request, meaning a new instance is created for each subrequest
and is unavailable outside the request (for instance in the CLI).

Scopes add a constraint on the dependencies of a service: a service cannot
depend on services from a narrower scope. For example, if you create a generic
`my_foo` service, but try to inject the `request` component, you'll receive
a :class:`Symfony\\Component\\DependencyInjection\\Exception\\ScopeWideningInjectionException`
when compiling the container. Read the sidebar below for more details.

.. sidebar:: Scopes and Dependencies

    Imagine you've configured a `my_mailer` service. You haven't configured
    the scope of the service, so it defaults to `container`. In other words,
    everytime you ask the container for the `my_mailer` service, you get
    the same object back. This is usually how you want your services to work.
    
    Imagine, however, that you need the `request` service in your `my_mailer`
    service, maybe because you're reading the URL of the current request.
    So, you add it as a constructor argument. Let's look at why this presents
    a problem:

    * When requesting `my_mailer`, an instance of `my_mailer` (let's call
      it *MailerA*) is created and the `request` service (let's call it
      *RequestA*) is passed to it. Life is good!

    * You've now made a subrequest in Symfony, which is a fancy way of saying
      that you've called, for example, the `{% render ... %}` Twig function,
      which executes another controller. Internally, the old `request` service
      (*RequestA*) is actually replaced by a new request instance (*RequestB*).
      This happens in the background, and it's totally normal.

    * In your embedded controller, you once again ask for the `my_mailer`
      service. Since your service is in the `container` scope, the same
      instance (*MailerA*) is just re-used. But here's the problem: the
      *MailerA* instance still contains the old *RequestA* object, which
      is now **not** the correct request object to have (*RequestB* is now
      the current `request` service). This is subtle, but the mis-match could
      cause major problems, which is why it's not allowed.

      So, that's the reason *why* scopes exists, and how they can cause
      problems. Keep reading to find out the common solutions.

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

If you don't specify the scope, it defaults to `container`, which is what
you want most of the time. Unless your service depends on another service
that's scoped to a narrower scope (most commonly, the `request` service),
you probably don't need to set the scope.

Using a Service from a narrower Scope
-------------------------------------

If your service depends on a scoped service, the best solution is to put
it in the same scope (or a narrower one). Usually, this means putting your
new service in the `request` scope.

But this is not always possible (for instance, a twig extension must be in
the `container` scope as the Twig environment needs it as a dependency).
In these cases, you should pass the entire container into your service and
retrieve your dependency from the container each time we need it to be sure
you have the right instance::

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

.. caution::

    Take care not to store the request in a property of the object for a
    future call of the service as it would be the same issue described
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

    Injecting the whole container into a service is generally not a good
    idea (only inject what you need). In some rare cases, it's necessary
    when you have a service in the ``container`` scope that needs a service
    in the ``request`` scope.

If you define a controller as a service then you can get the ``Request`` object
without injecting the container by having it passed in as an argument of your
action method. See :ref:`book-controller-request-argument` for details.
