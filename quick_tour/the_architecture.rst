The Architecture
================

You are my hero! Who would have thought that you would still be here after the first
two parts? Your efforts will be well-rewarded soon. The first two parts didn't look
too deeply at the architecture of the framework. Because it makes Symfony stand apart
from the framework crowd, let's dive into the architecture now.

Add Logging
-----------

A new Symfony app is micro: it's basically just a routing & controller system. But
thanks to Flex, installing more features is simple.

Want a logging system? No problem:

.. code-block:: terminal

    $ composer require logger

This installs and configures (via a recipe) the powerful `Monolog`_ library. To
use the logger in a controller, add a new argument type-hinted with ``LoggerInterface``::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends AbstractController
    {
        #[Route('/hello/{name}', methods: ['GET'])]
        public function index(string $name, LoggerInterface $logger): Response
        {
            $logger->info("Saying hello to $name!");

            // ...
        }
    }

That's it! The new log message will be written to ``var/log/dev.log``. The log
file path or even a different method of logging can be configured by updating
one of the config files added by the recipe.

Services & Autowiring
---------------------

But wait! Something *very* cool just happened. Symfony read the ``LoggerInterface``
type-hint and automatically figured out that it should pass us the Logger object!
This is called *autowiring*.

Every bit of work that's done in a Symfony app is done by an *object*: the Logger
object logs things and the Twig object renders templates. These objects are called
*services* and they are *tools* that help you build rich features.

To make life awesome, you can ask Symfony to pass you a service by using a type-hint.
What other possible classes or interfaces could you use? Find out by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

      # this is just a *small* sample of the output...

      Describes a logger instance.
      Psr\Log\LoggerInterface (monolog.logger)

      Request stack that controls the lifecycle of requests.
      Symfony\Component\HttpFoundation\RequestStack (request_stack)

      RouterInterface is the interface that all Router classes must implement.
      Symfony\Component\Routing\RouterInterface (router.default)

      [...]

This is just a short summary of the full list! And as you add more packages, this
list of tools will grow!

Creating Services
-----------------

To keep your code organized, you can even create your own services! Suppose you
want to generate a random greeting (e.g. "Hello", "Yo", etc). Instead of putting
this code directly in your controller, create a new class::

    // src/GreetingGenerator.php
    namespace App;

    class GreetingGenerator
    {
        public function getRandomGreeting(): string
        {
            $greetings = ['Hey', 'Yo', 'Aloha'];
            $greeting = $greetings[array_rand($greetings)];

            return $greeting;
        }
    }

Great! You can use it immediately in your controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\GreetingGenerator;
    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends AbstractController
    {
        #[Route('/hello/{name}', methods: ['GET'])]
        public function index(string $name, LoggerInterface $logger, GreetingGenerator $generator): Response
        {
            $greeting = $generator->getRandomGreeting();

            $logger->info("Saying $greeting to $name!");

            // ...
        }
    }

That's it! Symfony will instantiate the ``GreetingGenerator`` automatically and
pass it as an argument. But, could we *also* move the logger logic to ``GreetingGenerator``?
Yes! You can use autowiring inside a service to access *other* services. The only
difference is that it's done in the constructor:

.. code-block:: diff

      <?php
      // src/GreetingGenerator.php
    + use Psr\Log\LoggerInterface;

      class GreetingGenerator
      {
    +     public function __construct(
    +         private LoggerInterface $logger,
    +     ) {
    +     }

          public function getRandomGreeting(): string
          {
              // ...

    +        $this->logger->info('Using the greeting: '.$greeting);

               return $greeting;
          }
      }

Yes! This works too: no configuration, no time wasted. Keep coding!

Twig Extension & Autoconfiguration
----------------------------------

Thanks to Symfony's service handling, you can *extend* Symfony in many ways, like
by creating an event subscriber or a security voter for complex authorization
rules. Let's add a new filter to Twig called ``greet``. How? Create a class
that extends ``AbstractExtension``::

    // src/Twig/GreetExtension.php
    namespace App\Twig;

    use App\GreetingGenerator;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class GreetExtension extends AbstractExtension
    {
        public function __construct(
            private GreetingGenerator $greetingGenerator,
        ) {
        }

        public function getFilters()
        {
            return [
                new TwigFilter('greet', [$this, 'greetUser']),
            ];
        }

        public function greetUser(string $name): string
        {
            $greeting =  $this->greetingGenerator->getRandomGreeting();

            return "$greeting $name!";
        }
    }

After creating just *one* file, you can use this immediately:

.. code-block:: html+twig

    {# templates/default/index.html.twig #}
    {# Will print something like "Hey Symfony!" #}
    <h1>{{ name|greet }}</h1>

How does this work? Symfony notices that your class extends ``AbstractExtension``
and so *automatically* registers it as a Twig extension. This is called autoconfiguration,
and it works for *many* many things. Create a class and then extend a base class
(or implement an interface). Symfony takes care of the rest.

Blazing Speed: The Cached Container
-----------------------------------

After seeing how much Symfony handles automatically, you might be wondering: "Doesn't
this hurt performance?" Actually, no! Symfony is blazing fast.

How is that possible? The service system is managed by a very important object called
the "container". Most frameworks have a container, but Symfony's is unique because
it's *cached*. When you loaded your first page, all of the service information was
compiled and saved. This means that the autowiring and autoconfiguration features
add *no* overhead! It also means that you get *great* errors: Symfony inspects and
validates *everything* when the container is built.

Now you might be wondering what happens when you update a file and the cache needs
to rebuild? I like your thinking! It's smart enough to rebuild on the next page
load. But that's really the topic of the next section.

Development Versus Production: Environments
-------------------------------------------

One of a framework's main jobs is to make debugging easy! And our app is *full* of
great tools for this: the web debug toolbar displays at the bottom of the page, errors
are big, beautiful & explicit, and any configuration cache is automatically rebuilt
whenever needed.

But what about when you deploy to production? We will need to hide those tools and
optimize for speed!

This is solved by Symfony's *environment* system. Symfony applications begin with
three environments: ``dev``, ``prod``, and ``test``. You can define options for
specific environments in the configuration files from the ``config/`` directory
using the special ``when@`` keyword:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/routing.yaml
        framework:
            router:
                utf8: true

        when@prod:
            framework:
                router:
                    strict_requirements: null

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:router utf8="true"/>
            </framework:config>

            <when env="prod">
                <framework:config>
                    <framework:router strict-requirements="null"/>
                </framework:config>
            </when>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework, ContainerConfigurator $container) {
            $framework->router()
                ->utf8(true)
            ;

            if ('prod' === $container->env()) {
                $framework->router()
                    ->strictRequirements(null)
                ;
            }
        };

This is a *powerful* idea: by changing one piece of configuration (the environment),
your app is transformed from a debugging-friendly experience to one that's optimized
for speed.

Oh, how do you change the environment? Change the ``APP_ENV`` environment variable
from ``dev`` to ``prod``:

.. code-block:: diff

      # .env
    - APP_ENV=dev
    + APP_ENV=prod

But I want to talk more about environment variables next. Change the value back
to ``dev``: debugging tools are great when you're working locally.

Environment Variables
---------------------

Every app contains configuration that's different on each server - like database
connection information or passwords. How should these be stored? In files? Or another way?

Symfony follows the industry best practice by storing server-based configuration
as *environment* variables. This means that Symfony works *perfectly* with
Platform as a Service (PaaS) deployment systems as well as Docker.

But setting environment variables while developing can be a pain. That's why your
app automatically loads a ``.env`` file. The keys in this file then become environment
variables and are read by your app:

.. code-block:: bash

    # .env
    ###> symfony/framework-bundle ###
    APP_ENV=dev
    APP_SECRET=cc86c7ca937636d5ddf1b754beb22a10
    ###< symfony/framework-bundle ###

At first, the file doesn't contain much. But as your app grows, you'll add more
configuration as you need it. But, actually, it gets much more interesting! Suppose
your app needs a database ORM. Let's install the Doctrine ORM:

.. code-block:: terminal

    $ composer require doctrine

Thanks to a new recipe installed by Flex, look at the ``.env`` file again:

.. code-block:: diff

      ###> symfony/framework-bundle ###
      APP_ENV=dev
      APP_SECRET=cc86c7ca937636d5ddf1b754beb22a10
      ###< symfony/framework-bundle ###

    + ###> doctrine/doctrine-bundle ###
    + # ...
    + DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
    + ###< doctrine/doctrine-bundle ###

The new ``DATABASE_URL`` environment variable was added *automatically* and is already
referenced by the new ``doctrine.yaml`` configuration file. By combining environment
variables and Flex, you're using industry best practices without any extra effort.

Keep Going!
-----------

Call me crazy, but after reading this part, you should be comfortable with the most
*important* parts of Symfony. Everything in Symfony is designed to get out of your
way so you can keep coding and adding features, all with the speed and quality you
demand.

That's all for the quick tour. From authentication, to forms, to caching, there is
so much more to discover. Ready to dig into these topics now? Look no further - go
to the official :doc:`/index` and pick any guide you want.

.. _`Monolog`: https://github.com/Seldaek/monolog
