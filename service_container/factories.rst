Using a Factory to Create Services
==================================

A *factory* is a class or a callable whose main purpose is to create and return
other objects. The `factory design pattern`_ is useful when the creation of an
object requires some special logic that shouldn't live in the object itself,
such as selecting the concrete class to instantiate based on some condition or
running some code after the instantiation.

Symfony's :doc:`service container </service_container>` supports this pattern:
instead of instantiating the class of a service directly, the container can
call a method on your factory to create the object.

Static Factories
----------------

Suppose you have a factory that configures and returns a new ``NewsletterSender``
object by calling the static ``createNewsletterSender()`` method::

    // src/Email/NewsletterSenderStaticFactory.php
    namespace App\Email;

    // ...

    class NewsletterSenderStaticFactory
    {
        public static function createNewsletterSender(): NewsletterSender
        {
            $newsletterSender = new NewsletterSender();

            // ...

            return $newsletterSender;
        }
    }

To make the ``NewsletterSender`` object available as a service, use the
``factory`` option to define which method of which class must be called to
create its object:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterSender:
                # the first argument is the class and the second argument is the static method
                factory: ['App\Email\NewsletterSenderStaticFactory', 'createNewsletterSender']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSender;
        use App\Email\NewsletterSenderStaticFactory;

        return App::config([
            'services' => [
                // ...
                NewsletterSender::class => [
                    // the first argument is the class and the second argument is the static method
                    'factory' => [NewsletterSenderStaticFactory::class, 'createNewsletterSender'],
                ],
            ],
        ]);

If the factory method needs arguments, define them with the ``arguments``
option, as explained later in :ref:`factories-passing-arguments-factory-method`.

.. tip::

    When configuring your services with PHP, you can use `first-class callable syntax`_
    to define the factory::

        NewsletterSender::class => [
            'factory' => NewsletterSenderStaticFactory::createNewsletterSender(...),
        ],

    This syntax also works with global functions::

        'date' => [
            'class' => \DateTime::class,
            'factory' => \date_create(...),
        ],

.. note::

    When using a factory to create services, the value chosen for class
    has no effect on the resulting service. The actual class name
    only depends on the object that is returned by the factory. However,
    the configured class name may be used by compiler passes and therefore
    should be set to a sensible value.

Using the Class as Factory Itself
---------------------------------

When the static factory method belongs to the same class as the created instance,
you don't need to repeat the class name. Suppose the ``NewsletterSender`` class
has a ``create()`` method that must be called to create the object::

    // src/Email/NewsletterSender.php
    namespace App\Email;

    // ...

    class NewsletterSender
    {
        public static function create(): self
        {
            $newsletterSender = new self();

            // ...

            return $newsletterSender;
        }
    }

Use the ``constructor`` option to tell the container which static method of the
class creates the object:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Email/NewsletterSender.php
        namespace App\Email;

        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

        #[Autoconfigure(constructor: 'create')]
        class NewsletterSender
        {
            public static function create(): self
            {
                $newsletterSender = new self();

                // ...

                return $newsletterSender;
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterSender:
                constructor: 'create'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'constructor' => 'create',
                ],
            ],
        ]);

The same result can be achieved with the ``factory`` option by passing ``null``
as the factory class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterSender:
                factory: [null, 'create']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'factory' => [null, 'create'],
                ],
            ],
        ]);

If the factory method needs arguments, define them with the ``arguments``
option, as explained later in :ref:`factories-passing-arguments-factory-method`.

Non-Static Factories
--------------------

If your factory is using a regular method instead of a static one to configure
and create the service, instantiate the factory itself as a service too.
Configuration of the service container then looks like this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # first, create a service for the factory
            App\Email\NewsletterSenderFactory: ~

            # second, use the factory service as the first argument of the 'factory'
            # option and the factory method as the second argument
            App\Email\NewsletterSender:
                factory: ['@App\Email\NewsletterSenderFactory', 'createNewsletterSender']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSender;
        use App\Email\NewsletterSenderFactory;

        return App::config([
            'services' => [
                // first, create a service for the factory
                NewsletterSenderFactory::class => null,

                // second, use the factory service as the first argument of the 'factory'
                // option and the factory method as the second argument
                NewsletterSender::class => [
                    'factory' => [service(NewsletterSenderFactory::class), 'createNewsletterSender'],
                ],
            ],
        ]);

.. _factories-invokable:

Invokable Factories
-------------------

Suppose you now change your factory method to ``__invoke()`` so that your
factory service can be used as a callback::

    // src/Email/InvokableNewsletterSenderFactory.php
    namespace App\Email;

    // ...
    class InvokableNewsletterSenderFactory
    {
        public function __invoke(): NewsletterSender
        {
            $newsletterSender = new NewsletterSender();

            // ...

            return $newsletterSender;
        }
    }

Services can be created and configured via invokable factories by omitting the
method name:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterSender:
                factory: '@App\Email\InvokableNewsletterSenderFactory'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\InvokableNewsletterSenderFactory;
        use App\Email\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'factory' => service(InvokableNewsletterSenderFactory::class),
                ],
            ],
        ]);

.. _factories-passing-arguments-factory-method:

Passing Arguments to the Factory Method
---------------------------------------

.. tip::

    Arguments to your factory method are :ref:`autowired <services-autowire>` if
    that's enabled for your service.

If you need to pass arguments to the factory method you can use the ``arguments``
option. For example, suppose the ``createNewsletterSender()`` method in the
previous examples takes the ``twig`` service as an argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Email\NewsletterSender:
                factory:   ['@App\Email\NewsletterSenderFactory', createNewsletterSender]
                arguments: ['@twig']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSender;
        use App\Email\NewsletterSenderFactory;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'factory' => [service(NewsletterSenderFactory::class), 'createNewsletterSender'],
                    'arguments' => [service('twig')],
                ],
            ],
        ]);

Expression-Based Factories
--------------------------

Instead of a PHP class, the factory can be an :ref:`expression <services-expressions>`.
This allows you to select the created object at runtime:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Email\NewsletterSenderInterface:
                # use the "traceable_newsletter" service when debug is enabled, "newsletter" otherwise
                # "@=" indicates that this is an expression
                factory: '@=parameter("kernel.debug") ? service("traceable_newsletter") : service("newsletter")'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Email\NewsletterSenderInterface;

        return App::config([
            'services' => [
                NewsletterSenderInterface::class => [
                    // use the "traceable_newsletter" service when debug is enabled, "newsletter" otherwise
                    'factory' => expr("parameter('kernel.debug') ? service('traceable_newsletter') : service('newsletter')"),
                ],
            ],
        ]);

Factory expressions also support the ``arg()`` function, which returns an
argument of the definition itself (e.g.
``'@=arg(0).createNewsletterSender() ?: service("default_newsletter_sender")'``).

.. _`factory design pattern`: https://en.wikipedia.org/wiki/Factory_(object-oriented_programming)
.. _`first-class callable syntax`: https://www.php.net/manual/en/functions.first_class_callable_syntax.php
