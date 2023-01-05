.. index::
   single: DependencyInjection; Injection types

Types of Injection
==================

Making a class's dependencies explicit and requiring that they be injected
into it is a good way of making a class more reusable, testable and decoupled
from others.

There are several ways that the dependencies can be injected. Each injection
point has advantages and disadvantages to consider, as well as different
ways of working with them when using the service container.

Constructor Injection
---------------------

The most common way to inject dependencies is via a class's constructor.
To do this you need to add an argument to the constructor signature to accept
the dependency::

    // src/Mail/NewsletterManager.php
    namespace App\Mail;

    // ...
    class NewsletterManager
    {
        private $mailer;

        public function __construct(MailerInterface $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

You can specify what service you would like to inject into this in the
service container configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mail\NewsletterManager:
                arguments: ['@mailer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Mail\NewsletterManager">
                    <argument type="service" id="mailer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterManager;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(NewsletterManager::class)
                ->args(service('mailer'));
        };

.. tip::

    Type hinting the injected object means that you can be sure that a suitable
    dependency has been injected. By type-hinting, you'll get a clear error
    immediately if an unsuitable dependency is injected. By type hinting
    using an interface rather than a class you can make the choice of dependency
    more flexible. And assuming you only use methods defined in the interface,
    you can gain that flexibility and still safely use the object.

There are several advantages to using constructor injection:

* If the dependency is a requirement and the class cannot work without it
  then injecting it via the constructor ensures it is present when the class
  is used as the class cannot be constructed without it.

* The constructor is only ever called once when the object is created, so
  you can be sure that the dependency will not change during the object's
  lifetime.

These advantages do mean that constructor injection is not suitable for
working with optional dependencies. It is also more difficult to use in
combination with class hierarchies: if a class uses constructor injection
then extending it and overriding the constructor becomes problematic.

Immutable-setter Injection
--------------------------

Another possible injection is to use a method which returns a separate instance
by cloning the original service, this approach allows you to make a service immutable::

    // src/Mail/NewsletterManager.php
    namespace App\Mail;

    // ...
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Contracts\Service\Attribute\Required;

    class NewsletterManager
    {
        private $mailer;

        /**
         * @return static
         */
        #[Required]
        public function withMailer(MailerInterface $mailer): self
        {
            $new = clone $this;
            $new->mailer = $mailer;

            return $new;
        }

        // ...
    }

In order to use this type of injection, don't forget to configure it:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
       services:
            # ...

            app.newsletter_manager:
                class: App\Mail\NewsletterManager
                calls:
                    - withMailer: !returns_clone ['@mailer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.newsletter_manager" class="App\Mail\NewsletterManager">
                    <call method="withMailer" returns-clone="true">
                        <argument type="service" id="mailer"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Mail\NewsletterManager;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->register('app.newsletter_manager', NewsletterManager::class)
            ->addMethodCall('withMailer', [new Reference('mailer')], true);

.. note::

    If you decide to use autowiring, this type of injection requires
    that you add a ``@return static`` docblock in order for the container
    to be capable of registering the method.

This approach is useful if you need to configure your service according to your needs,
so, here's the advantages of immutable-setters:

* Immutable setters works with optional dependencies, this way, if you don't need
  a dependency, the setter doesn't need to be called.

* Like the constructor injection, using immutable setters force the dependency to stay
  the same during the lifetime of a service.

* This type of injection works well with traits as the service can be composed,
  this way, adapting the service to your application requirements is easier.

* The setter can be called multiple times, this way, adding a dependency to a collection
  becomes easier and allows you to add a variable number of dependencies.

The disadvantages are:

* As the setter call is optional, a dependency can be null when calling
  methods of the service. You must check that the dependency is available
  before using it.

* Unless the service is declared lazy, it is incompatible with services
  that reference each other in what are called circular loops.

Setter Injection
----------------

Another possible injection point into a class is by adding a setter method
that accepts the dependency::

    // src/Mail/NewsletterManager.php
    namespace App\Mail;

    use Symfony\Contracts\Service\Attribute\Required;

    // ...
    class NewsletterManager
    {
        private $mailer;

        #[Required]
        public function setMailer(MailerInterface $mailer): void
        {
            $this->mailer = $mailer;
        }

        // ...
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            app.newsletter_manager:
                class: App\Mail\NewsletterManager
                calls:
                    - setMailer: ['@mailer']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.newsletter_manager" class="App\Mail\NewsletterManager">
                    <call method="setMailer">
                        <argument type="service" id="mailer"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterManager;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(NewsletterManager::class)
                ->call('setMailer', [service('mailer')]);
        };

This time the advantages are:

* Setter injection works well with optional dependencies. If you do not
  need the dependency, then do not call the setter.

* You can call the setter multiple times. This is particularly useful if
  the method adds the dependency to a collection. You can then have a variable
  number of dependencies.

* Like the immutable-setter one, this type of injection works well with
  traits and allows you to compose your service.

The disadvantages of setter injection are:

* The setter can be called more than once, also long after initialization,
  so you cannot be sure the dependency is not replaced during the lifetime
  of the object (except by explicitly writing the setter method to check if
  it has already been called).

* You cannot be sure the setter will be called and so you need to add checks
  that any required dependencies are injected.

.. _property-injection:

Property Injection
------------------

Another possibility is setting public fields of the class directly::

    // ...
    class NewsletterManager
    {
        public $mailer;

        // ...
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            app.newsletter_manager:
                class: App\Mail\NewsletterManager
                properties:
                    mailer: '@mailer'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.newsletter_manager" class="App\Mail\NewsletterManager">
                    <property name="mailer" type="service" id="mailer"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\NewsletterManager;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set('app.newsletter_manager', NewsletterManager::class)
                ->property('mailer', service('mailer'));
        };

There are mainly only disadvantages to using property injection, it is similar
to setter injection but with these additional important problems:

* You cannot control when the dependency is set at all, it can be changed
  at any point in the object's lifetime.

* You cannot use type hinting so you cannot be sure what dependency is injected
  except by writing into the class code to explicitly test the class instance
  before using it.

But, it is useful to know that this can be done with the service container,
especially if you are working with code that is out of your control, such
as in a third party library, which uses public properties for its dependencies.
