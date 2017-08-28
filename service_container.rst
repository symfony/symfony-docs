.. index::
   single: Service Container
   single: DependencyInjection; Container

Service Container
=================

Your application is *full* of useful objects: a "Mailer" object might help you
send emails while another object might help you save things to the database.
Almost *everything* that your app "does" is actually done by one of these objects.
And each time you install a new bundle, you get access to even more!

In Symfony, these useful objects are called **services** and each service lives inside
a very special object called the **service container**. If you have the service container,
then you can fetch a service by using that service's id::

    $logger = $container->get('logger');
    $entityManager = $container->get('doctrine.orm.entity_manager');

The container allows you to centralize the way objects are constructed. It makes
your life easier, promotes a strong architecture and is super fast!

Fetching and using Services
---------------------------

The moment you start a Symfony app, your container *already* contains many services.
These are like *tools*: waiting for you to take advantage of them. In your controller,
you can "ask" for a service from the container by type-hinting an argument with the
service's class or interface name. Want to :doc:`log </logging>` something? No problem::

    // src/AppBundle/Controller/ProductController.php
    // ...

    use Psr\Log\LoggerInterface;

    /**
     * @Route("/products")
     */
    public function listAction(LoggerInterface $logger)
    {
        $logger->info('Look! I just used a service');

        // ...
    }

.. versionadded:: 3.3
    The ability to type-hint a service in order to receive it was added in Symfony 3.3.
    See the :ref:`controller chapter <controller-service-arguments-tag>` for more
    details.

.. _container-debug-container:

What other services are available? Find out by running:

.. code-block:: terminal

     $ php bin/console debug:container

This is just a *small* sample of the output:

=============================== =======================================================================
Service ID                      Class name
=============================== =======================================================================
doctrine                        ``Doctrine\Bundle\DoctrineBundle\Registry``
filesystem                      ``Symfony\Component\Filesystem\Filesystem``
form.factory                    ``Symfony\Component\Form\FormFactory``
logger                          ``Symfony\Bridge\Monolog\Logger``
request_stack                   ``Symfony\Component\HttpFoundation\RequestStack``
router                          ``Symfony\Bundle\FrameworkBundle\Routing\Router``
security.authorization_checker  ``Symfony\Component\Security\Core\Authorization\AuthorizationChecker``
security.password_encoder       ``Symfony\Component\Security\Core\Encoder\UserPasswordEncoder``
session                         ``Symfony\Component\HttpFoundation\Session\Session``
translator                      ``Symfony\Component\Translation\DataCollectorTranslator``
twig                            ``Twig_Environment``
validator                       ``Symfony\Component\Validator\Validator\ValidatorInterface``
=============================== =======================================================================

You can also use the unique "Service ID" to access a service directly::

    // src/AppBundle/Controller/ProductController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Routing\Annotation\Route;

    class ProductController extends Controller
    {
        /**
         * @Route("/products")
         */
        public function listAction()
        {
            $logger = $this->container->get('logger');
            $logger->info('Look! I just used a service');

            // ...
        }
    }

:ref:`Fetching a service directly from the container <controller-access-services-directly>`
like this only works if you extend the ``Controller`` class.

Throughout the docs, you'll see how to use the many different services that live
in the container.

.. sidebar:: Container: Lazy-loaded for speed

    Wait! Are all the services (objects) instantiated on *every* request? No! The
    container is lazy: it doesn't instantiate a service until (and unless) you ask
    for it. For example, if you never use the ``validator`` service during a request,
    the container will never instantiate it.

.. index::
   single: Service Container; Configuring services

.. _service-container-creating-service:

Creating/Configuring Services in the Container
----------------------------------------------

.. tip::

    The recommended way of configuring services changed in Symfony 3.3. For a deep
    explanation, see :doc:`/service_container/3.3-di-changes`.

You can also organize your *own* code into services. For example, suppose you need
to show your users a random, happy message. If you put this code in your controller,
it can't be re-used. Instead, you decide to create a new class::

    // src/AppBundle/Service/MessageGenerator.php
    namespace AppBundle\Service;

    class MessageGenerator
    {
        public function getHappyMessage()
        {
            $messages = [
                'You did it! You updated the system! Amazing!',
                'That was one of the coolest updates I\'ve seen all day!',
                'Great work! Keep going!',
            ];

            $index = array_rand($messages);

            return $messages[$index];
        }
    }

Congratulations! You've just created your first service class! You can use it immediately
inside your controller::

    use AppBundle\Service\MessageGenerator;

    public function newAction(MessageGenerator $messageGenerator)
    {
        // thanks to the type-hint, the container will instantiate a
        // new MessageGenerator and pass it to you!
        // ...

        $message = $messageGenerator->getHappyMessage();
        $this->addFlash('success', $message);
        // ...
    }

When you ask for the ``MessageGenerator`` service, the container constructs a new
``MessageGenerator`` object and returns it (see sidebar below). But if you never ask
for the service, it's *never* constructed: saving memory and speed. As a bonus, the
``MessageGenerator`` service is only created *once*: the same instance is returned
each time you ask for it.

.. _service-container-services-load-example:

.. sidebar:: Automatic Service Loading in services.yml

    The documentation assumes you're using
    `Symfony Standard Edition (version 3.3) services.yml`_ configuration. The most
    important part is this:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/services.yml
            services:
                # default configuration for services in *this* file
                _defaults:
                    autowire: true
                    autoconfigure: true
                    public: false

                # makes classes in src/AppBundle available to be used as services
                AppBundle\:
                    resource: '../../src/AppBundle/*'
                    # you can exclude directories or files
                    # but if a service is unused, it's removed anyway
                    exclude: '../../src/AppBundle/{Entity,Repository}'

        .. code-block:: xml

            <!-- app/config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- Default configuration for services in *this* file -->
                    <defaults autowire="true" autoconfigure="true" public="false" />

                    <!-- Load services from whatever directories you want (you can update this!) -->
                    <prototype namespace="AppBundle\" resource="../../src/AppBundle/*" exclude="../../src/AppBundle/{Entity,Repository}" />
                </services>
            </container>

        .. code-block:: php

            // app/config/services.php
            use Symfony\Component\DependencyInjection\Definition;

            // To use as default template
            $definition = new Definition();

            $definition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(false)
            ;

            // $this is a reference to the current loader
            $this->registerClasses($definition, 'AppBundle\\', '../../src/AppBundle/*', '../../src/AppBundle/{Entity,Repository}');

    Thanks to this configuration, you can automatically use any classes from the
    ``src/AppBundle`` directory as a service, without needing to manually configure
    it. Later, you'll learn more about this later in :ref:`service-psr4-loader`.

    If you'd prefer to manually wire your service, that's totally possible: see
    :ref:`services-explicitly-configure-wire-services`.

    .. versionadded:: 3.3
        The ``_defaults`` key *and* ability to load services from a directory were added
        in Symfony 3.3.

You can also fetch a service directly from the container via its "id", which will
be its class name in this case::

    use AppBundle\Service\MessageGenerator;

    // accessing services like this only works if you extend Controller
    class ProductController extends Controller
    {
        public function newAction()
        {
            // only works if your service is public
            $messageGenerator = $this->get(MessageGenerator::class);

            $message = $messageGenerator->getHappyMessage();
            $this->addFlash('success', $message);
            // ...
        }
    }

However, this only works if you make your service :ref:`public <container-public>`.

.. caution::

    Service ids are case-insensitive (e.g. ``AppBundle\Service\MessageGenerator``
    and ``appbundle\service\messagegenerator`` refer to the same service). But this
    was deprecated in Symfony 3.3. Starting in 4.0, service ids will be case sensitive.

.. _services-constructor-injection:

Injecting Services/Config into a Service
----------------------------------------

What if you need to access the ``logger`` service from within ``MessageGenerator``?
Your service does *not* have access to the container directly, so you can't fetch
it via ``$this->container->get()``.

No problem! Instead, create a ``__construct()`` method with a ``$logger`` argument
that has the ``LoggerInterface`` type-hint. Set this on a new ``$logger`` property
and use it later::

    // src/AppBundle/Service/MessageGenerator.php
    // ...

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function getHappyMessage()
        {
            $this->logger->info('About to find a happy message!');
            // ...
        }
    }

That's it! The container will *automatically* know to pass the ``logger`` service
when instantiating the ``MessageGenerator``. How does it know to do this?
:ref:`Autowiring <services-autowire>`. The key is the ``LoggerInterface``
type-hint in your ``__construct()`` method and the ``autowire: true`` config in
``services.yml``. When you type-hint an argument, the container will automatically
find the matching service. If it can't, you'll see a clear exception with a helpful
suggestion.

.. _services-debug-container-types:

How should you know to use ``LoggerInterface`` for the type-hint? You can either
read the docs for whatever feature you're using, or get a list of autowireable
type-hints by running:

.. code-block:: terminal

    $ php bin/console debug:container --types

This is just a small subset of the output:

=============================================================== =====================================
Service ID                                                      Class name
=============================================================== =====================================
``Psr\Cache\CacheItemPoolInterface``                            alias for "cache.app.recorder"
``Psr\Log\LoggerInterface``                                     alias for "monolog.logger"
``Symfony\Component\EventDispatcher\EventDispatcherInterface``  alias for "debug.event_dispatcher"
``Symfony\Component\HttpFoundation\RequestStack``               alias for "request_stack"
``Symfony\Component\HttpFoundation\Session\SessionInterface``   alias for "session"
``Symfony\Component\Routing\RouterInterface``                   alias for "router.default"
=============================================================== =====================================

Handling Multiple Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you also want to email a site administrator each time a site update is
made. To do that, you create a new class::

    // src/AppBundle/Updates/SiteUpdateManager.php
    namespace AppBundle\Updates;

    use AppBundle\Service\MessageGenerator;

    class SiteUpdateManager
    {
        private $messageGenerator;
        private $mailer;

        public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer)
        {
            $this->messageGenerator = $messageGenerator;
            $this->mailer = $mailer;
        }

        public function notifyOfSiteUpdate()
        {
            $happyMessage = $this->messageGenerator->getHappyMessage();

            $message = \Swift_Message::newInstance()
                ->setSubject('Site update just happened!')
                ->setFrom('admin@example.com')
                ->setTo('manager@example.com')
                ->addPart(
                    'Someone just updated the site. We told them: '.$happyMessage
                );
            $this->mailer->send($message);

            return $message;
        }
    }

This uses the ``MessageGenerator`` *and* the ``Swift_Mailer`` service. As long as
you're :ref:`loading all services from src/AppBundle <service-container-services-load-example>`,
you can use the service immediately::

    use AppBundle\Updates\SiteUpdateManager;

    public function newAction(SiteUpdateManager $siteUpdateManager)
    {
        // ...

        $message = $siteUpdateManager->notifyOfSiteUpdate();
        $this->addFlash('success', $message);
        // ...
    }

Thanks to autowiring and your type-hints in ``__construct()``, the container creates
the ``SiteUpdateManager`` object and passes it the correct argument. In most cases,
this works perfectly.

.. _services-manually-wire-args:

Manually Wiring Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~

But there are a few cases when an argument to a service cannot be autowired. For
example, suppose you want to make the admin email configurable:

.. code-block:: diff

    // src/AppBundle/Updates/SiteUpdateManager.php
    // ...

    class SiteUpdateManager
    {
        // ...
    +    private $adminEmail;

    -    public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer)
    +    public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer, $adminEmail)
        {
            // ...
    +        $this->adminEmail = $adminEmail;
        }

        public function notifyOfSiteUpdate()
        {
            // ...

            $message = \Swift_Message::newInstance()
                // ...
    -            ->setTo('manager@example.com')
    +            ->setTo($this->adminEmail)
                // ...
            ;
            // ...
        }
    }

If you make this change and refresh, you'll see an error:

    Cannot autowire service "AppBundle\Updates\SiteUpdateManager": argument "$adminEmail"
    of method "__construct()" must have a type-hint or be given a value explicitly.

That makes sense! There is no way that the container knows what value you want to
pass here. No problem! In your configuration, you can explicitly set this argument:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            # same as before
            AppBundle\:
                resource: '../../src/AppBundle/*'
                exclude: '../../src/AppBundle/{Entity,Repository}'

            # explicitly configure the service
            AppBundle\Updates\SiteUpdateManager:
                arguments:
                    $adminEmail: 'manager@example.com'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- Same as before -->
                <prototype namespace="AppBundle\" resource="../../src/AppBundle/*" exclude="../../src/AppBundle/{Entity,Repository}" />

                <!-- Explicitly configure the service -->
                <service id="AppBundle\Updates\SiteUpdateManager">
                    <argument key="$adminEmail">manager@example.com</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Updates\SiteUpdateManager;
        use Symfony\Component\DependencyInjection\Definition;

        // Same as before
        $definition = new Definition();

        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;

        $this->registerClasses($definition, 'AppBundle\\', '../../src/AppBundle/*', '../../src/AppBundle/{Entity,Repository}');

        // Explicitly configure the service
        $container->getDefinition(SiteUpdateManager::class)
            ->setArgument('$adminEmail', 'manager@example.com');

.. versionadded:: 3.3
    The ability to configure an argument by its name (``$adminEmail``) was added
    in Symfony 3.3. Previously, you could configure it only by its index (``2`` in
    this case) or by using empty quotes for the other arguments.

Thanks to this, the container will pass ``manager@example.com`` as the third argument
to ``__construct`` when creating the ``SiteUpdateManager`` service. The other arguments
will still be autowired.

But, isn't this fragile? Fortunately, no! If you rename the ``$adminEmail`` argument
to something else - e.g. ``$mainEmail`` - you will get a clear exception when you
reload the next page (even if that page doesn't use this service).

.. _service-container-parameters:

Service Parameters
------------------

In addition to holding service objects, the container also holds configuration,
called ``parameters``. To create a parameter, add it under the ``parameters`` key
and reference it with the ``%parameter_name%`` syntax:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        parameters:
            admin_email: manager@example.com

        services:
            # ...

            AppBundle\Updates\SiteUpdateManager:
                arguments:
                    $adminEmail: '%admin_email%'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="admin_email">manager@example.com</parameter>
            </parameters>

            <services>
                <!-- ... -->

                <service id="AppBundle\Updates\SiteUpdateManager">
                    <argument key="$adminEmail">%admin_email%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Updates\SiteUpdateManager;
        $container->setParameter('admin_email', 'manager@example.com');

        $container->autowire(SiteUpdateManager::class)
            // ...
            ->setArgument('$adminEmail', '%admin_email%');

Actually, once you define a parameter, it can be referenced via the ``%parameter_name%``
syntax in *any* other service configuration file - like ``config.yml``. Many parameters
are defined in a :ref:`parameters.yml file <config-parameters-yml>`.

You can also fetch parameters directly from the container::

    public function newAction()
    {
        // ...

        // this ONLY works if you extend Controller
        $adminEmail = $this->container->getParameter('admin_email');

        // or a shorter way!
        // $adminEmail = $this->getParameter('admin_email');
    }

For more info about parameters, see :doc:`/service_container/parameters`.

.. _services-wire-specific-service:

Choose a Specific Service
-------------------------

The ``MessageGenerator`` service created earlier requires a ``LoggerInterface`` argument::

    // src/AppBundle/Service/MessageGenerator.php
    // ...

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }
        // ...
    }

However, there are *multiple* services in the container that implement ``LoggerInterface``,
such as ``logger``, ``monolog.logger.request``, ``monolog.logger.php``, etc. How
does the container know which one to use?

In these situations, the container is usually configured to automatically choose
one of the services - ``logger`` in this case (read more about why in :ref:`service-autowiring-alias`).
But, you can control this and pass in a different logger:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ... same code as before

            # explicitly configure the service
            AppBundle\Service\MessageGenerator:
                arguments:
                    $logger: '@monolog.logger.request'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="AppBundle\Service\MessageGenerator">
                    <argument key="$logger" type="service" id="monolog.logger.request" />
                </service>
            </services>
        </container>


    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->autowire(MessageGenerator::class)
            ->setAutoconfigured(true)
            ->setPublic(false)
            ->setArgument('$logger', new Reference('monolog.logger.request'));

This tells the container that the ``$logger`` argument to ``__construct`` should use
service whose id is ``monolog.logger.request``.

.. tip::

    The ``@`` symbol is important: that's what tells the container you want to pass
    the *service* whose id is ``monolog.logger.request``, and not just the *string*
    ``monolog.logger.request``.

.. _services-autowire:

The autowire Option
-------------------

Above, the ``services.yml`` file has ``autowire: true`` in the ``_defaults`` section
so that it applies to all services defined in that file. With this setting, you're
able to type-hint arguments in the ``__construct()`` method of your services and
the container will automatically pass you the correct arguments. This entire entry
has been written around autowiring.

For more details about autowiring, check out :doc:`/service_container/autowiring`.

.. _services-autoconfigure:

The autoconfigure Option
------------------------

.. versionadded:: 3.3
    The ``autoconfigure`` option was added in Symfony 3.3.

Above, the ``services.yml`` file has ``autoconfigure: true`` in the ``_defaults``
section so that it applies to all services defined in that file. With this setting,
the container will automatically apply certain configuration to your services, based
on your service's *class*. This is mostly used to *auto-tag* your services.

For example, to create a Twig Extension, you need to create a class, register it
as a service, and :doc:`tag </service_container/tags>` it with ``twig.extension``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            AppBundle\Twig\MyTwigExtension:
                tags: [twig.extension]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="AppBundle\Twig\MyTwigExtension">
                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Twig\MyTwigExtension;

        $container->autowire(MyTwigExtension::class)
            ->addTag('twig.extension');

But, with ``autoconfigure: true``, you don't need the tag. In fact, if you're using
the :ref:`Symfony Standard Edition services.yml config <service-container-services-load-example>`,
you don't need to do *anything*: the service will be automatically loaded. Then,
``autoconfigure`` will add the ``twig.extension`` tag *for* you, because your class
implements ``Twig_ExtensionInterface``. And thanks to ``autowire``, you can even add
constructor arguments without any configuration.

Of course, you can still :ref:`manually configure the service <services-manually-wire-args>`
if you need to.

.. _container-public:

Public Versus Private Services
------------------------------

Thanks to the ``_defaults`` section in ``services.yml``, every service defined in
this file is ``public: false`` by default:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # default configuration for services in *this* file
            _defaults:
                # ...
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- Default configuration for services in *this* file -->
                <defaults autowire="true" autoconfigure="true" public="false" />
            </services>
        </container>

What does this mean? When a service is **not** public, you cannot access it directly
from the container::

    use AppBundle\Service\MessageGenerator;

    public function newAction(MessageGenerator $messageGenerator)
    {
        // type-hinting it as an argument DOES work

        // but accessing it directly from the container does NOT Work
        $this->container->get(MessageGenerator::class);
    }

Usually, this is ok: there are better ways to access a service. But, if you *do*
need to make your service public, just override this setting:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ... same code as before

            # explicitly configure the service
            AppBundle\Service\MessageGenerator:
                public: true

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="AppBundle\Service\MessageGenerator" public="true"></service>
            </services>
        </container>

.. _service-psr4-loader:

Importing Many Services at once with resource
---------------------------------------------

You've already seen that you can import many services at once by using the ``resource``
key. For example, the default Symfony configuration contains this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            # the namespace prefix for classes (must end in \)
            AppBundle\:
                # accepts a glob pattern
                resource: '../../src/AppBundle/*'
                # exclude some paths
                exclude: '../../src/AppBundle/{Entity,Repository}'

            # these were imported above, but we want to add some extra config
            AppBundle\Controller\:
                resource: '../../src/AppBundle/Controller'
                # apply some configuration to these services
                public: true
                tags: ['controller.service_arguments']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <prototype namespace="AppBundle\" resource="../../src/AppBundle/*" exclude="../../src/AppBundle/{Entity,Repository}" />

                <prototype namespace="AppBundle\Controller\" resource="../../src/AppBundle/Controller" public="true">
                    <tag name="controller.service_arguments" />
                </prototype>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        // To use as default template
        $definition = new Definition();

        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;

        $this->registerClasses($definition, 'AppBundle\\', '../../src/AppBundle/*', '../../src/AppBundle/{Entity,Repository}');

        // Changes default config
        $definition
            ->setPublic(true)
            ->addTag('controller.service_arguments')
        ;

        // $this is a reference to the current loader
        $this->registerClasses($definition, 'AppBundle\\Controller\\', '../../src/AppBundle/Controller/*');

This can be used to quickly make many classes available as services and apply some
default configuration. The ``id`` of each service is its fully-qualified class name.
You can override any service that's imported by using its id (class name) below
(e.g. see :ref:`services-manually-wire-args`). If you override a service, none of
the options (e.g. ``public``) are inherited from the import (but the overridden
service *does* still inherit from ``_defaults``).

You can also ``exclude`` certain paths. This is optional, but will slightly increase
performance in the ``dev`` environment: excluded paths are not tracked and so modifying
them will not cause the container to be rebuilt.

.. note::

    Wait, does this mean that *every* class in ``src/AppBundle`` is registered as
    a service? Even model or entity classes? Actually, no. As long as you have
    ``public: false`` under your ``_defaults`` key (or you can add it under the
    specific import), all the imported services are *private*. Thanks to this, all
    classes in ``src/AppBundle`` that are *not* explicitly used as services are
    automatically removed from the final container. In reality, the import simply
    means that all classes are "available to be *used* as services" without needing
    to be manually configured.

.. sidebar:: The ``namespace`` Option

    .. versionadded:: 3.4
        The ``namespace`` option was added in Symfony 3.4.

    When using the ``resource`` option in YAML, the namespace prefix can only be used once
    per file when defining it as the ``id``. In order to have multiple definitions in the
    same file with the same namespace prefix, you can use the ``namespace`` option. When
    this option is used, the ``id`` can be anything as long as it is unique. For example,
    you can define your services like this:

    .. code-block:: yaml

        # app/config/services.yml
        services:
            command_handlers:
                namespace: App\Domain\
                resource: ../../src/Domain/*/CommandHandler
                tags: [command_handler]

            event_subscribers:
                namespace: App\Domain\
                resource: ../../src/Domain/*/EventSubscriber
                tags: [event_subscriber]

.. _services-explicitly-configure-wire-services:

Explicitly Configuring Services and Arguments
---------------------------------------------

Prior to Symfony 3.3, all services and (typically) arguments were explicitly configured:
it was not possible to :ref:`load services automatically <service-container-services-load-example>`
and :ref:`autowiring <services-autowire>` was much less common.

Both of these features are optional. And even if you use them, there may be some
cases where you want to manually wire a service. For example, suppose that you want
to register *2* services for the ``SiteUpdateManager`` class - each with a different
admin email. In this case, each needs to have a unique service id:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            # this is the service's id
            site_update_manager.superadmin:
                class: AppBundle\Updates\SiteUpdateManager
                # you CAN still use autowiring: we just want to show what it looks like without
                autowire: false
                # manually wire all arguments
                arguments:
                    - '@AppBundle\Service\MessageGenerator'
                    - '@mailer'
                    - 'superadmin@example.com'

            site_update_manager.normal_users:
                class: AppBundle\Updates\SiteUpdateManager
                autowire: false
                arguments:
                    - '@AppBundle\Service\MessageGenerator'
                    - '@mailer'
                    - 'contact@example.com'

            # Create an alias, so that - by default - if you type-hint SiteUpdateManager,
            # the site_update_manager.superadmin will be used
            AppBundle\Updates\SiteUpdateManager: '@site_update_manager.superadmin'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="site_update_manager.superadmin" class="AppBundle\Updates\SiteUpdateManager" autowire="false">
                    <argument type="service" id="AppBundle\Service\MessageGenerator" />
                    <argument type="service" id="mailer" />
                    <argument>superadmin@example.com</argument>
                </service>

                <service id="site_update_manager.normal_users" class="AppBundle\Updates\SiteUpdateManager" autowire="false">
                    <argument type="service" id="AppBundle\Service\MessageGenerator" />
                    <argument type="service" id="mailer" />
                    <argument>contact@example.com</argument>
                </service>

                <alias id="AppBundle\Updates\SiteUpdateManager" service="site_update_manager.superadmin">
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Updates\SiteUpdateManager;
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('site_update_manager.superadmin', SiteUpdateManager::class)
            ->setAutowire(false)
            ->setArguments(array(
                new Reference(MessageGenerator::class),
                new Reference('mailer'),
                'superadmin@example.com'
            ));

        $container->register('site_update_manager.normal_users', SiteUpdateManager::class)
            ->setAutowire(false)
            ->setArguments(array(
                new Reference(MessageGenerator::class),
                new Reference('mailer'),
                'contact@example.com'
            ));

        $container->setAlias(SiteUpdateManager::class, 'site_update_manager.superadmin')

In this case, *two* services are registered: ``site_update_manager.superadmin``
and ``site_update_manager.normal_users``. Thanks to the alias, if you type-hint
``SiteUpdateManager`` the first (``site_update_manager.superadmin``) will be passed.
If you want to pass the second, you'll need to :ref:`manually wire the service <services-wire-specific-service>`.

.. caution::

    If you do *not* create the alias and are :ref:`loading all services from src/AppBundle <service-container-services-load-example>`,
    then *three* services have been created (the automatic service + your two services)
    and the automatically loaded service will be passed - by default - when you type-hint
    ``SiteUpdateManager``. That's why creating the alias is a good idea.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /service_container/*

.. _`service-oriented architecture`: https://en.wikipedia.org/wiki/Service-oriented_architecture
.. _`Symfony Standard Edition (version 3.3) services.yml`: https://github.com/symfony/symfony-standard/blob/3.3/app/config/services.yml
