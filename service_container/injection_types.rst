Types of Dependency Injection
=============================

Making a class's dependencies explicit and requiring that they are injected
into it is a good way to make a class more reusable, testable and decoupled
from the rest of the code.

There are several ways that the dependencies can be injected. Each injection
point has advantages and disadvantages to consider, as well as different
ways of working with them when using the service container.

Constructor Injection
---------------------

**The most common way** to inject dependencies is via a class's constructor.
To do this you need to add an argument to the constructor signature to accept
the dependency::

    // src/Mail/NewsletterSender.php
    namespace App\Mail;

    use Symfony\Component\Mailer\MailerInterface;

    class NewsletterSender
    {
        public function __construct(
            private MailerInterface $mailer,
        ) {
        }

        // ...
    }

When using the :ref:`default service configuration <service-container-services-load-example>`,
the container injects the dependency automatically thanks to
:doc:`autowiring </service_container/autowiring>`. If you don't use autowiring,
you can specify which service to inject in the service configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\NewsletterSender:
                arguments: ['@mailer']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'arguments' => [
                        service('mailer'),
                    ],
                ],
            ],
        ]);

.. tip::

    Type hinting the injected object means that you can be sure that a suitable
    dependency has been injected. By type-hinting, you'll get a clear error
    immediately if an unsuitable dependency is injected. By type hinting
    using an interface rather than a class you can make the choice of dependency
    more flexible. And assuming you only use methods defined in the interface,
    you can gain that flexibility and still safely use the object.

There are several **advantages** to using constructor injection:

* If the dependency is a requirement and the class cannot work without it
  then injecting it via the constructor ensures it is present, since the class
  cannot be constructed without it.
* The constructor is only called once when the object is created, so you can be
  sure that the dependency will not change during the object's lifetime.

The main **disadvantage** of constructor injection is that it's more difficult
to use in combination with class hierarchies: if a class uses constructor
injection then extending it and overriding the constructor becomes problematic.

.. _injection-types-setter:

Setter Injection
----------------

Another possible injection point into a class is a **setter method** that accepts
the dependency. If you use autowiring, add the ``#[Required]`` attribute to the
setter and the container calls it automatically when instantiating the service::

    // src/Mail/NewsletterSender.php
    namespace App\Mail;

    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Contracts\Service\Attribute\Required;

    class NewsletterSender
    {
        private MailerInterface $mailer;

        #[Required]
        public function setMailer(MailerInterface $mailer): void
        {
            $this->mailer = $mailer;
        }

        // ...
    }

You can also configure the method call explicitly using the ``calls`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\NewsletterSender:
                calls:
                    - setMailer: ['@mailer']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'calls' => [
                        'setMailer' => [service('mailer')],
                    ],
                ],
            ],
        ]);

This time the **advantages** are:

* You can call the setter multiple times. This is particularly useful if
  the method adds the dependency to a collection. You can then have a variable
  number of dependencies.
* This type of injection works well with traits and allows you to compose
  your service.

The **disadvantages** of setter injection are:

* The setter can be called more than once, also long after initialization,
  so you cannot be sure the dependency is not replaced during the lifetime
  of the object (except by explicitly writing the setter method to check if
  it has already been called).
* You cannot be sure the setter will be called and so you need to add checks
  that any required dependencies are injected.

Immutable Setter Injection
~~~~~~~~~~~~~~~~~~~~~~~~~~

Another possible injection is to use a method which **returns a separate instance**
by cloning the original service. This approach allows you to make a service
immutable::

    // src/Mail/NewsletterSender.php
    namespace App\Mail;

    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Contracts\Service\Attribute\Required;

    class NewsletterSender
    {
        private MailerInterface $mailer;

        #[Required]
        public function withMailer(MailerInterface $mailer): static
        {
            $new = clone $this;
            $new->mailer = $mailer;

            return $new;
        }

        // ...
    }

When using autowiring, methods with the ``#[Required]`` attribute and a ``static``
return type (or a ``@return static`` docblock) are called automatically as
immutable setters: the container uses the value returned by the method
(``$service = $service->withMailer($mailer);``).

.. tip::

    If you don't want a method with a ``static`` return type and a ``#[Required]``
    attribute to behave as a wither, you can add a ``@return $this`` annotation
    to disable the *returns clone* feature.

You can also configure this method call explicitly with the special
``returns_clone`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\NewsletterSender:
                calls:
                    - withMailer: !returns_clone ['@mailer']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'calls' => [
                        ['withMailer', [service('mailer')], true],
                    ],
                ],
            ],
        ]);

The **advantages** of immutable setters are:

* Like the constructor injection, using immutable setters forces the dependency
  to stay the same during the lifetime of a service.
* This type of injection works well with traits as the service can be composed.
  This way, adapting the service to your application requirements is easier.
* The setter can be called multiple times. This way, adding a dependency to a
  collection becomes easier and allows you to add a variable number of
  dependencies.

The **disadvantages** are:

* As the setter call is optional, a dependency can be null when calling
  methods of the service. You must check that the dependency is available
  before using it.
* Unless the service is declared lazy, it is incompatible with services
  that reference each other in what are called circular loops.

.. _property-injection:

Property Injection
------------------

Another possibility is **setting public fields** of the class directly::

    // src/Mail/NewsletterSender.php
    namespace App\Mail;

    use Symfony\Component\Mailer\MailerInterface;

    class NewsletterSender
    {
        public MailerInterface $mailer;

        // ...
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\NewsletterSender:
                properties:
                    mailer: '@mailer'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'properties' => [
                        'mailer' => service('mailer'),
                    ],
                ],
            ],
        ]);

There are mainly only **disadvantages** to using property injection. It is similar
to setter injection but with this additional important problem:

* You cannot control when the dependency is set at all, it can be changed
  at any point in the object's lifetime.

But, it is useful to know that this can be done with the service container,
especially if you are working with code that is out of your control, such
as in a third party library, which uses public properties for its dependencies.

.. _optional-dependencies:

Making Dependencies Optional
----------------------------

Sometimes, one of your services may have an optional dependency, meaning
that the dependency is not required for your service to work properly. You can
configure the container to not throw an error in this case.

If you use autowiring, declare the argument as nullable with a ``null`` default
value. The container injects the service if it exists and ``null`` otherwise::

    // src/Mail/NewsletterSender.php
    namespace App\Mail;

    use Psr\Log\LoggerInterface;

    class NewsletterSender
    {
        public function __construct(
            private ?LoggerInterface $logger = null,
        ) {
        }

        // ...
    }

When configuring services explicitly, you can choose between two strategies
for missing dependencies: ``null`` and ``ignore``.

Setting Missing Dependencies to ``null``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the ``null`` strategy to explicitly set the argument to ``null``
if the service does not exist:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mail\NewsletterSender:
                # the 'null' strategy is not supported by the YAML format, but this
                # is not a real limitation: when used in an argument, the '@?' syntax
                # of the 'ignore' strategy (explained in the next section) also
                # sets the argument to null
                arguments: ['@?logger']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'arguments' => [
                        service('logger')->nullOnInvalid(),
                    ],
                ],
            ],
        ]);

Ignoring Missing Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The behavior of ignoring missing dependencies is the same as the "null" behavior
except when used within a method call, in which case the method call itself
will be removed.

In the following example the container will inject a service using a method
call if the service exists and remove the method call if it does not:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mail\NewsletterSender:
                calls:
                    # the '@?' syntax tells the container that the dependency is optional
                    - setLogger: ['@?logger']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                NewsletterSender::class => [
                    'calls' => [
                        'setLogger' => [service('logger')->ignoreOnInvalid()],
                    ],
                ],
            ],
        ]);

.. note::

    If the argument to the method call is a collection of arguments and any of
    them is missing, those elements are removed but the method call is still
    made with the remaining elements of the collection.

.. tip::

    When using the container in :ref:`standalone PHP applications <service-container-standalone>`,
    the ``get()`` method of the container also accepts these strategies as
    constants of ``ContainerInterface`` (e.g. ``ContainerInterface::NULL_ON_INVALID_REFERENCE``).
