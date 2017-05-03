.. index::
   single: Service Container
   single: DependencyInjection; Container

Service Container
=================

Your application is *full* of useful objects: one "Mailer" object might help you
deliver email messages while another object might help you save things to the database.
Almost *everything* that your app "does" is actually done by one of these objects.
And each time you install a new bundle, you get access to even more!

In Symfony, these useful objects are called **services** and each service lives inside
a very special object called the **service container**. If you have the service container,
then you can fetch a service by using that service's id::

    $logger = $container->get('logger');
    $entityManager = $container->get('doctrine.entity_manager');

The container is the *heart* of Symfony: it allows you to standardize and centralize
the way objects are constructed. It makes your life easier, is super fast, and emphasizes
an architecture that promotes reusable and decoupled code. It's also a big reason
that Symfony is so fast and extensible!

Finally, configuring and using the service container is easy. By the end
of this article, you'll be comfortable creating your own objects via the
container and customizing objects from any third-party bundle. You'll begin
writing code that is more reusable, testable and decoupled, simply because
the service container makes writing good code so easy.

Fetching and using Services
---------------------------

The moment you start a Symfony app, the container *already* contains many services.
These are like *tools*, waiting for you to take advantage of them. In your controller,
you have access to the container via ``$this->container``. Want to :doc:`log </logging>`
something? No problem::

    // src/AppBundle/Controller/ProductController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

``logger`` is a unique key for the ``Logger`` object. What other services are available?
Find out by running:

.. code-block:: terminal

     $ php app/console debug:container

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

Throughout the docs, you'll see how to use the many different services that live
in the container.

.. sidebar:: Container: Lazy-loaded for speed

    If the container holds so many useful objects (services), does that mean those
    objects are instantiated on *every* request? No! The container is lazy: it doesn't
    instantiate a service until (and unless) you ask for it. For example, if you
    never use the ``validator`` service during a request, the container will never
    instantiate it.

.. index::
   single: Service Container; Configuring services

.. _service-container-creating-service:

Creating/Configuring Services in the Container
----------------------------------------------

You can also leverage the container to organize your *own* code into services. For
example, suppose you want to show your users a random, happy message every time
they do something. If you put this code in your controller, it can't be re-used.
Instead, you decide to create a new class::

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

Congratulations! You've just created your first service class. Next, you can *teach*
the service container *how* to instantiate it:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.message_generator:
                class:     AppBundle\Service\MessageGenerator
                arguments: []

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.message_generator" class="AppBundle\Service\MessageGenerator">
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('app.message_generator', new Definition(
            MessageGenerator::class,
            array()
        ));

That's it! Your service - with the unique key ``app.message_generator`` - is now
available in the container. You can use it immediately inside your controller::

    public function newAction()
    {
        // ...

        // the container will instantiate a new MessageGenerator()
        $messageGenerator = $this->container->get('app.message_generator');

        // or use this shorter syntax
        // $messageGenerator = $this->get('app.message_generator');

        $message = $messageGenerator->getHappyMessage();
        $this->addFlash('success', $message);
        // ...
    }

When you ask for the ``app.message_generator`` service, the container constructs
a new ``MessageGenerator`` object and returns it. If you never ask for the
``app.message_generator`` service during a request, it's *never* constructed, saving
you memory and increasing the speed of your app. This also means that there's almost
no performance overhead for defining a lot of services.

As a bonus, the ``app.message_generator`` service is only created *once*: the same
instance is returned each time you ask for it.

Injecting Services/Config into a Service
----------------------------------------

What if you want to use the ``logger`` service from within ``MessageGenerator``?
Your service does *not* have a ``$this->container`` property: that's a special power
only controllers have.

Instead, you should create a ``__construct()`` method, add a ``$logger`` argument
and set it on a ``$logger`` property::

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

.. tip::

    The ``LoggerInterface`` type-hint in the ``__construct()`` method is optional,
    but a good idea. You can find the correct type-hint by reading the docs for the
    service or by using the ``php bin/console debug:container`` console command.

Next, tell the container the service has a constructor argument:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.message_generator:
                class:     AppBundle\Service\MessageGenerator
                arguments: ['@logger']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.message_generator" class="AppBundle\Service\MessageGenerator">
                    <argument type="service" id="logger" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.message_generator', new Definition(
            MessageGenerator::class,
            array(new Reference('logger'))
        ));

That's it! The container now knows to pass the ``logger`` service as an argument
when it instantiates the ``MessageGenerator``. This is called dependency injection.

The ``arguments`` key holds an array of all of the constructor arguments to the
service (just 1 so far). The ``@`` symbol before ``@logger`` is important: it tells
Symfony to pass the *service* named ``logger``.

But you can pass anything as arguments. For example, suppose you want to make your
class a bit more configurable::

    // src/AppBundle/Service/MessageGenerator.php
    // ...

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;
        private $loggingEnabled;

        public function __construct(LoggerInterface $logger, $loggingEnabled)
        {
            $this->logger = $logger;
            $this->loggingEnabled = $loggingEnabled;
        }

        public function getHappyMessage()
        {
            if ($this->loggingEnabled) {
                $this->logger->info('About to find a happy message!');
            }
            // ...
        }
    }

The class now has a *second* constructor argument. No problem, just update your
service config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.message_generator:
                class:     AppBundle\Service\MessageGenerator
                arguments: ['@logger', true]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.message_generator" class="AppBundle\Service\MessageGenerator">
                    <argument type="service" id="logger" />
                    <argument>true</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.message_generator', new Definition(
            MessageGenerator::class,
            array(new Reference('logger'), true)
        ));

You can even leverage :doc:`environments </configuration/environments>` to control
this new value in different situations.

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
            enable_generator_logging:  true

        services:
            app.message_generator:
                class:     AppBundle\Service\MessageGenerator
                arguments: ['@logger', '%enable_generator_logging%']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <parameters>
                    <parameter key="enable_generator_logging">true</parameter>
                </parameters>

                <service id="app.message_generator" class="AppBundle\Service\MessageGenerator">
                    <argument type="service" id="logger" />
                    <argument>%enable_generator_logging%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setParameter('enable_generator_logging', true);

        $container->setDefinition('app.message_generator', new Definition(
            MessageGenerator::class,
            array(new Reference('logger'), '%enable_generator_logging%')
        ));

Actually, once you define a parameter, it can be referenced via the ``%parameter_name%``
syntax in *any* other service configuration file - like ``config.yml``. Many parameters
are defined in a :ref:`parameters.yml file <config-parameters-yml>`.

You can also fetch parameters directly from the container::

    public function newAction()
    {
        // ...

        $isLoggingEnabled = $this->container
            ->getParameter('enable_generator_logging');
        // ...
    }

.. note::

    If you use a string that starts with ``@`` or ``%``, you need to escape it by
    adding another ``@`` or ``%``:

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            # This will be parsed as string '@securepass'
            mailer_password: '@@securepass'

            # Parsed as http://symfony.com/?foo=%s&amp;bar=%d
            url_pattern: 'http://symfony.com/?foo=%%s&amp;bar=%%d'

For more info about parameters, see :doc:`/service_container/parameters`.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /service_container/*

.. _`service-oriented architecture`: https://en.wikipedia.org/wiki/Service-oriented_architecture
