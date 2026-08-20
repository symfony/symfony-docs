Service Subscribers & Locators
==============================

Sometimes, a service needs access to several other services without being sure
that all of them will actually be used; in those cases, creating all the
services from the start is a waste of resources.

You could inject each of those services as a :doc:`lazy service </service_container/lazy_services>`
or via a :ref:`service closure <autowiring_closures>`: both are good options to
delay the instantiation of one or a few specific dependencies. However, they
require configuring every dependency individually, which becomes impractical
when the number of services is large or not known in advance (e.g. all the
:doc:`services tagged </service_container/tags>` with a certain tag).

For those cases, Symfony provides a **service locator**: a lightweight
`PSR-11 container`_ that includes a predefined set of services, but only
instantiates each of them when it's actually used.

.. note::

    Injecting :ref:`the whole Symfony container <container-public>` might look
    like an alternative, but it's strongly discouraged: it hides the real
    dependencies of your class, it gives access to too many services and it
    requires making all those services :ref:`public <container-public>`.

You can define the list of services included in a **locator** in the place where
the locator is injected (using a PHP attribute or configuration files) or in
the class that uses the locator; in that case, the class is called a
**service subscriber**. This article explains all the options.

.. _service-locator_autowire-locator:
.. _the-autowirelocator-and-autowireiterator-attributes:

Injecting a Service Locator
---------------------------

Suppose your application provides several payment methods and each of them is
implemented in its own service. The ``Checkout`` service processes the payment
method selected by the user, so it needs access to all the payment services,
but it only uses one of them on each checkout. This is a perfect use case for
a service locator; define it with the ``#[AutowireLocator]`` attribute or the
``service_locator`` argument type:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Checkout.php
        namespace App;

        use App\Payment\CreditCardPayment;
        use App\Payment\PaypalPayment;
        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

        class Checkout
        {
            public function __construct(
                #[AutowireLocator([
                    'credit_card' => CreditCardPayment::class,
                    'paypal' => PaypalPayment::class,
                ])]
                private ContainerInterface $paymentHandlers,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Checkout:
                arguments:
                  - !service_locator
                      credit_card: '@App\Payment\CreditCardPayment'
                      paypal: '@App\Payment\PaypalPayment'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Checkout;
        use App\Payment\CreditCardPayment;
        use App\Payment\PaypalPayment;

        return App::config([
            'services' => [
                Checkout::class => [
                    'arguments' => [service_locator([
                        'credit_card' => service(CreditCardPayment::class),
                        'paypal' => service(PaypalPayment::class),
                    ])],
                ],
            ],
        ]);

Get the services from the locator using the keys defined for them. The
services are not instantiated until you get them::

    // src/Checkout.php
    // ...

    public function process(string $paymentMethod, int $amount): void
    {
        // if e.g. $paymentMethod = 'paypal', only the PaypalPayment
        // service is instantiated during this checkout
        $paymentHandler = $this->paymentHandlers->get($paymentMethod);
        $paymentHandler->pay($amount);
    }

If you omit the keys of the list, the class or interface name of each service
is used as its key (e.g. ``$this->paymentHandlers->get(PaypalPayment::class)``).
In addition, you can mark any service as optional by prefixing its type with
``?``: the locator won't fail when that service doesn't exist; use ``has()``
to check it before getting it::

    class Checkout
    {
        public function __construct(
            #[AutowireLocator([
                'credit_card' => CreditCardPayment::class,
                // optional service: no error even if PaypalPayment doesn't exist
                'paypal' => '?'.PaypalPayment::class,
            ])]
            private ContainerInterface $paymentHandlers,
        ) {
        }
    }

Counting and Iterating the Locator Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can type-hint the service locator argument with
:class:`Symfony\\Contracts\\Service\\ServiceCollectionInterface` instead of
``Psr\Container\ContainerInterface``. By doing so, you'll be able to
count and iterate over the services of the locator::

    // ...
    $numberOfHandlers = count($this->paymentHandlers);
    $nameOfHandlers = array_keys($this->paymentHandlers->getProvidedServices());

    // you can iterate through all services of the locator
    foreach ($this->paymentHandlers as $serviceId => $service) {
        // do something with the service, the service id or both
    }

Reusing a Service Locator in Multiple Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you inject the same service locator in several services, it's better to
define the service locator as a stand-alone service and then inject it in the
other services. To do so, create a new service definition using the
``ServiceLocator`` class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.payment_locator:
                class: Symfony\Component\DependencyInjection\ServiceLocator
                arguments:
                    -
                        credit_card: '@App\Payment\CreditCardPayment'
                        paypal: '@App\Payment\PaypalPayment'
                # if you are not using the default service autoconfiguration,
                # add the following tag to the service definition:
                # tags: ['container.service_locator']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Payment\CreditCardPayment;
        use App\Payment\PaypalPayment;
        use Symfony\Component\DependencyInjection\ServiceLocator;

        return App::config([
            'services' => [
                'app.payment_locator' => [
                    'class' => ServiceLocator::class,
                    'arguments' => [[
                        'credit_card' => service(CreditCardPayment::class),
                        'paypal' => service(PaypalPayment::class),
                    ]],
                    // if you are not using the default service autoconfiguration,
                    // add the following tag to the service definition:
                    // 'tags' => ['container.service_locator'],
                ],
            ],
        ]);

Now you can inject the service locator in other services:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Checkout.php
        namespace App;

        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\Autowire;

        class Checkout
        {
            public function __construct(
                #[Autowire(service: 'app.payment_locator')]
                private ContainerInterface $paymentHandlers,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Checkout:
                arguments: ['@app.payment_locator']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Checkout;

        return App::config([
            'services' => [
                Checkout::class => [
                    'arguments' => [service('app.payment_locator')],
                ],
            ],
        ]);

.. _service-locator_autowire-iterator:
.. _service-subscribers-locators_defining-service-locator:

Injecting Tagged Services
-------------------------

Service locators are also useful when working with the services that have a
certain :doc:`service tag </service_container/tags>`, because that set of
services is not known in advance. Pass a tag name (instead of a list of
services) to the ``#[AutowireLocator]`` attribute or use the ``tagged_locator``
argument type to create a locator with all the services tagged with that tag:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Checkout.php
        namespace App;

        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

        class Checkout
        {
            public function __construct(
                // creates a locator with all the services tagged with 'app.payment_handler'
                #[AutowireLocator('app.payment_handler')]
                private ContainerInterface $paymentHandlers,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Checkout:
                arguments: [!tagged_locator 'app.payment_handler']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Checkout;

        return App::config([
            'services' => [
                Checkout::class => [
                    'arguments' => [tagged_locator('app.payment_handler')],
                ],
            ],
        ]);

If the service needs to loop over all the tagged services instead of getting
only some of them, use the ``#[AutowireIterator]`` attribute or the
``tagged_iterator`` argument type instead. They inject an iterable with all
the tagged services:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Checkout.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

        class Checkout
        {
            public function __construct(
                // injects an iterable with all the services tagged with 'app.payment_handler'
                #[AutowireIterator('app.payment_handler')]
                private iterable $paymentHandlers,
            ) {
            }

            public function getAvailablePaymentMethods(): array
            {
                $methods = [];
                foreach ($this->paymentHandlers as $paymentHandler) {
                    if ($paymentHandler->isEnabled()) {
                        $methods[] = $paymentHandler->getName();
                    }
                }

                return $methods;
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Checkout:
                arguments: [!tagged_iterator 'app.payment_handler']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Checkout;

        return App::config([
            'services' => [
                Checkout::class => [
                    'arguments' => [tagged_iterator('app.payment_handler')],
                ],
            ],
        ]);

All these options provide extra features to sort, filter and index the
injected services. Read all about them in the
:ref:`service tags article <tags_reference-tagged-services>`.

Service Subscribers
-------------------

In the previous sections, the list of services included in the locator is
defined where the locator is injected: in the PHP attribute, in configuration
files or via a service tag. A **service subscriber** is a class that defines
that list by itself, so the list is always the same no matter where or how the
class is used.

Service subscribers are useful when other projects use your classes (e.g.
classes included in bundles) and, above all, when extending a class that
already subscribes to its own services, such as the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController` class
extended by Symfony controllers.

To turn a class into a service subscriber, implement
:class:`Symfony\\Contracts\\Service\\ServiceSubscriberInterface` and return the
list of needed services in the static ``getSubscribedServices()`` method::

    // src/Checkout.php
    namespace App;

    use App\Payment\CreditCardPayment;
    use App\Payment\PaypalPayment;
    use Psr\Container\ContainerInterface;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class Checkout implements ServiceSubscriberInterface
    {
        public function __construct(
            private ContainerInterface $paymentHandlers,
        ) {
        }

        public static function getSubscribedServices(): array
        {
            return [
                'credit_card' => CreditCardPayment::class,
                'paypal' => PaypalPayment::class,
            ];
        }
    }

There's nothing else to configure: when the container finds a service
implementing ``ServiceSubscriberInterface``, it creates a locator with the
subscribed services and injects it into the constructor argument type-hinted
with ``Psr\Container\ContainerInterface``. The rest of the class works exactly
the same as when injecting the locator explicitly.

.. tip::

    The container detects service subscribers via
    :ref:`autoconfiguration <services-autoconfigure>`. If you disabled it, add
    the ``container.service_subscriber`` tag to the definition of the service
    that implements ``ServiceSubscriberInterface`` (``App\Checkout`` in this
    example).

The entries of ``getSubscribedServices()`` support the same features as the
``#[AutowireLocator]`` attribute: omit the keys to use the type as the service
key and prefix any type with ``?`` to make that service optional::

    use Psr\Log\LoggerInterface;

    public static function getSubscribedServices(): array
    {
        return [
            'credit_card' => CreditCardPayment::class,
            // the key of this service is 'Psr\Log\LoggerInterface'
            LoggerInterface::class,
            // optional service: no error even if it doesn't exist
            '?'.PaypalPayment::class,
        ];
    }

When extending a class that also implements ``ServiceSubscriberInterface``,
it's your responsibility to call the parent when overriding the method, so the
subscribed services of both classes are merged. This typically happens when
extending ``AbstractController``::

    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class MyController extends AbstractController
    {
        public static function getSubscribedServices(): array
        {
            return array_merge(parent::getSubscribedServices(), [
                // ...
                'logger' => LoggerInterface::class,
            ]);
        }
    }

Customizing the Injected Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the container uses :ref:`autowiring <services-autowire>` to decide
which service to inject for each subscribed type. If you need to inject a
different service, use the ``key`` and ``id`` attributes of the
``container.service_subscriber`` tag. Each tag maps a single key, so repeat
the tag for each service to override; the subscribed services not included in
any tag keep using autowiring:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Checkout:
                tags:
                    - { name: 'container.service_subscriber', key: 'logger', id: 'monolog.logger.event' }
                    - { name: 'container.service_subscriber', key: 'paypal', id: 'app.payment.paypal_sandbox' }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Checkout;

        return App::config([
            'services' => [
                Checkout::class => [
                    'tags' => [
                        ['container.service_subscriber' => ['key' => 'logger', 'id' => 'monolog.logger.event']],
                        ['container.service_subscriber' => ['key' => 'paypal', 'id' => 'app.payment.paypal_sandbox']],
                    ],
                ],
            ],
        ]);

.. tip::

    The ``key`` attribute can be omitted if the service name internally is the
    same as in the service container.

You can get the same result without touching the configuration files (and use
any other dependency injection feature, like injecting parameters or specific
implementations). To do so, return
:class:`Symfony\\Contracts\\Service\\Attribute\\SubscribedService` objects in
``getSubscribedServices()`` and pass the needed dependency injection attributes
in their ``attributes`` argument (you can combine these objects with regular
string entries)::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    public static function getSubscribedServices(): array
    {
        return [
            // ...
            // inject a specific service for the given type
            new SubscribedService('logger', LoggerInterface::class, attributes: new Autowire(service: 'monolog.logger.event')),

            // inject a specific implementation of the given type
            new SubscribedService('payment.logger', LoggerInterface::class, attributes: new Target('paymentLogger')),

            // inject a container parameter
            new SubscribedService('env', 'string', attributes: new Autowire('%kernel.environment%')),
        ];
    }

The supported attributes are ``Autowire``, ``AutowireDecorated``,
``AutowireIterator``, ``AutowireLocator`` and ``Target``.

.. note::

    Returning ``SubscribedService`` objects requires version ``3.2`` or newer
    of ``symfony/service-contracts``.

.. _service-subscribers-service-subscriber-trait:

Service Subscriber Trait
------------------------

Service subscribers require some boilerplate code: implementing the interface,
listing the services in one method, injecting the locator in the constructor
and getting the services from it. The
:class:`Symfony\\Contracts\\Service\\ServiceMethodsSubscriberTrait` removes
most of that boilerplate: add the
:class:`Symfony\\Contracts\\Service\\Attribute\\SubscribedService` attribute to
any private method that defines a return type and the trait will build the list
of subscribed services using those return types. Each method returns the
service associated with it, so the class ends up with a type-hinted helper
method per dependency::

    // src/Service/MyService.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;
    use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceMethodsSubscriberTrait;

        public function doSomething(): void
        {
            // $this->router() ...
            // $this->logger() ...
        }

        #[SubscribedService]
        private function router(): RouterInterface
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }

This allows you to create helper traits like RouterAware, LoggerAware, etc.
and compose your services with them::

    // src/Service/LoggerAware.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    trait LoggerAware
    {
        #[SubscribedService]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__CLASS__.'::'.__FUNCTION__);
        }
    }

    // src/Service/RouterAware.php
    namespace App\Service;

    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    trait RouterAware
    {
        #[SubscribedService]
        private function router(): RouterInterface
        {
            return $this->container->get(__CLASS__.'::'.__FUNCTION__);
        }
    }

    // src/Service/MyService.php
    namespace App\Service;

    use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceMethodsSubscriberTrait, LoggerAware, RouterAware;

        public function doSomething(): void
        {
            // $this->router() ...
            // $this->logger() ...
        }
    }

.. warning::

    When creating these helper traits, the service id cannot be ``__METHOD__``
    as this will include the trait name, not the class name. Instead, use
    ``__CLASS__.'::'.__FUNCTION__`` as the service id.

Hooked Properties
~~~~~~~~~~~~~~~~~

.. versionadded:: 3.7

    Support for hooked properties in ``ServiceMethodsSubscriberTrait`` was
    introduced in ``symfony/service-contracts`` 3.7.

Instead of using methods, you can also use PHP `virtual/hooked properties`_
together with the ``SubscribedService`` attribute. The property must define a
``get`` hook that retrieves the service from the container::

    // src/Service/MyService.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;
    use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceMethodsSubscriberTrait;

        #[SubscribedService]
        public RouterInterface $router {
            get => $this->container->get(__METHOD__);
        }

        #[SubscribedService]
        public LoggerInterface $logger {
            get => $this->container->get(__METHOD__);
        }

        public function doSomething(): void
        {
            // $this->router ...
            // $this->logger ...
        }
    }

``SubscribedService`` Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``#[SubscribedService]`` attribute also accepts the ``attributes`` argument
to customize the injected services, as shown previously for the
``getSubscribedServices()`` method::

    // src/Service/MyService.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;
    use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceMethodsSubscriberTrait;

        public function doSomething(): void
        {
            // $this->environment() ...
            // $this->router() ...
            // $this->logger() ...
        }

        #[SubscribedService(attributes: new Autowire('%kernel.environment%'))]
        private function environment(): string
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService(attributes: new Autowire(service: 'router'))]
        private function router(): RouterInterface
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService(attributes: new Target('requestLogger'))]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }

Testing a Service Subscriber
----------------------------

To unit test a service subscriber, you can create a fake container::

    use Symfony\Contracts\Service\ServiceLocatorTrait;
    use Symfony\Contracts\Service\ServiceProviderInterface;

    // Create the fake services
    $foo = new stdClass();
    $bar = new stdClass();
    $bar->foo = $foo;

    // Create the fake container
    $container = new class([
        'foo' => fn () => $foo,
        'bar' => fn () => $bar,
    ]) implements ServiceProviderInterface {
        use ServiceLocatorTrait;
    };

    // Create the service subscriber
    $serviceSubscriber = new MyService($container);
    // ...

.. note::

    When defining the service locator like this, beware that the
    :method:`Symfony\\Contracts\\Service\\ServiceLocatorTrait::getProvidedServices`
    of your container will use the return type of the closures as the values of the
    returned array. If no return type is defined, the value will be ``?``. If you
    want the values to reflect the classes of your services, the return type has
    to be set on your closures.

Another alternative is to mock it using ``PHPUnit``::

    use Psr\Container\ContainerInterface;

    $container = $this->createMock(ContainerInterface::class);
    $container->expects(self::any())
        ->method('get')
        ->willReturnMap([
            ['foo', $this->createStub(Foo::class)],
            ['bar', $this->createStub(Bar::class)],
        ])
    ;

    $serviceSubscriber = new MyService($container);
    // ...

.. _`PSR-11 container`: https://www.php-fig.org/psr/psr-11/
.. _`virtual/hooked properties`: https://php.net/language.oop5.property-hooks
