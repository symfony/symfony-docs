.. index::
   single: Service Container
   single: DependencyInjection; Container

Service Container
=================

Your application is *full* of useful objects: one "Mailer" object might help you
send email messages while another object might help you save things to the database.
Almost *everything* that your app "does" is actually done by one of these objects.
And each time you install a new bundle, you get access to even more!

In Symfony, these useful objects are called **services** and each service lives inside
a very special object called the **service container**. If you have the service container,
then you can fetch a service by using that service's id::

    $logger = $container->get('logger');
    $entityManager = $container->get('doctrine.entity_manager');

The container allows you to centralize the way objects are constructed. It makes
your life easier, promotes a strong architecture and is super fast!

Fetching and using Services
---------------------------

The moment you start a Symfony app, your container *already* contains many services.
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

Congratulations! You've just created your first service class. Next, you can *teach*
the service container *how* to instantiate it:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # default configuration for services in *this* file
            _defaults:
                autowire: true
                autoconfigure: true

            # load services from whatever directories you want (you can update this!)
            AppBundle\:
                resource: '../../src/AppBundle/{Service,EventDispatcher,Twig,Form}'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

That's it! Thanks to the ``AppBundle\`` line and ``resource`` key below it, a service
will be registered for each class in the ``src/AppBundle/Service`` directory (and
the other directories listed).

Each service's "key" is its class name. You can use it immediately inside your controller::

    use AppBundle\Service\MessageGenerator;

    public function newAction()
    {
        // ...

        // the container will instantiate a new MessageGenerator()
        $messageGenerator = $this->container->get(MessageGenerator::class);

        // or use this shorter synax
        // $messageGenerator = $this->get(MessageGenerator::class);

        $message = $messageGenerator->getHappyMessage();
        $this->addFlash('success', $message);
        // ...
    }

When you ask for the ``MessageGenerator::class`` service, the container constructs
a new ``MessageGenerator`` object and returns it. But if you never ask for the service,
it's *never* constructed: saving memory and speed.

As a bonus, the ``MessageGenerator::class`` service is only created *once*: the same
instance is returned each time you ask for it.

.. caution::

    Service ids are case-insensitive (e.g. ``AppBundle\Service\MessageGenerator``
    and ``appbundle\service\messagegenerator`` refer to the same service). But this
    was deprecated in Symfony 3.3. Starting in 4.0, service ids will be case sensitive.

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

That's it! The container will *automatically* know to pass the ``logger`` service
when instantiating the ``MessageGenerator``. How does it know to do this?
:doc:`Autowiring </service_container/autowiring>`. The key is the ``LoggerInterface``
type-hint in your ``__construct()`` method and the ``autowire: true`` config in
``services.yml``. When you type-hint an argument, the container will automatically
find the matching service. If it can't or there is any ambiguity, you'll see a clear
exception suggesting how to fix it.

Be sure to read more about :doc:`autowiring </service_container/autowiring>`.

.. tip::

    How should you know to use ``LoggerInterface`` for the type-hint? The best way
    is by reading the docs for whatever feature you're using. You can also use the
    ``php bin/console debug:container`` console command to get a hint
    to the class name for a service.

Handling Multiple Services
--------------------------

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

This uses the ``MessageGenerator`` *and* the ``Swift_Mailer`` service. To register
this as a new service in the container, simply tell your configuration to load from
the new ``Updates`` sub-directory:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            # registers all classes in Services & Updates directories
            AppBundle\:
                resource: '../../src/AppBundle/{Service,Updates,EventDispatcher,Twig,Form}'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

Now, you can use the service immediately::

    use AppBundle\Updates\SiteUpdateManager;

    public function newAction()
    {
        // ...

        $siteUpdateManager = $this->container->get(SiteUpdateManager::class);

        $message = $siteUpdateManager->notifyOfSiteUpdate();
        $this->addFlash('success', $message);
        // ...
    }

Thanks to autowiring and your type-hints in ``__construct()``, the container creates
the ``SiteUpdateManager`` object and passes it the correct arguments. In most cases,
this works perfectly.

Manually Wiring Arguments
-------------------------

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
                resource: '../../src/AppBundle/{Service,Updates}'

            # explicitly configure the service
            AppBundle\Updates\SiteUpdateManager:
                arguments:
                    $adminEmail: 'manager@example.com'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

Thanks to this, the container will pass ``manager@example.com`` as the third argument
to ``__construct`` when creating the ``SiteUpdateManager`` service. The other arguments
will still be autowired.

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

        TODO

    .. code-block:: php

        TODO

Actually, once you define a parameter, it can be referenced via the ``%parameter_name%``
syntax in *any* other service configuration file - like ``config.yml``. Many parameters
are defined in a :ref:`parameters.yml file <config-parameters-yml>`.

You can also fetch parameters directly from the container::

    public function newAction()
    {
        // ...

        $adminEmail = $this->container
            ->getParameter('admin_email');
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
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

This tells the container that the ``$logger`` argument to ``_construct`` should use
service whose id is ``monolog.logger.request``.

.. tip::

    The ``@`` symbol is important: that's what tells the container you want to pass
    the *service* whose id is ``monolog.logger.request``, and not just the *string*
    ``monolog.logger.request``.

.. _services-autoconfigure:

The autoconfigure Option
------------------------

Above, we've set ``autoconfigure: true`` in the ``_defaults`` section so that it
applies to all services defined in that file. With this setting, the container will
automatically apply certain configuration to your services, based on your service's
*class*. The is mostly used to *auto-tag* your services.

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
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

But, with ``autoconfigure: true``, you don't need the tag. In fact, all you need
is this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            # load your service from the Twig directory
            AppBundle\:
                resource: '../../src/AppBundle/{Service,EventDispatcher,Twig,Form}'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        TODO

    .. code-block:: php

        // app/config/services.php
        TODO

That's it! The container will find your class in the ``Twig/`` directory and register
it as a service. Then ``autoconfigure`` will add the ``twig.extension`` tag *for*
you, because your class implements ``Twig_ExtensionInterface``. And thanks to ``autowire``,
you can even add ``__construct()`` arguments without any configuration.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /service_container/*

.. _`service-oriented architecture`: https://en.wikipedia.org/wiki/Service-oriented_architecture
