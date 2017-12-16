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
use the logger in a controller, add a new argument type-hinted with ``LoggerInterface``:

    use Psr\Log\LoggerInterface;
    // ...

    public function index($name, LoggerInterface $logger)
    {
        $logger->info("Saying hello to $name!");

        // ...
    }

That's it! The new log message be written to ``var/log/dev.log``. Of course, this
can be configured by updating one of the config files added by the recipe.

Services & Autowiring
---------------------

But wait! Something *very* cool just happened. Symfony read the ``LoggerInterface``
type-hint and automatically figured out that it should pass us the Logger object!
This is called *autowiring*.

Every bit of work that's done in a Symfony app is done by an *object*: the Logger
object logs things and the Twig object renders templates. These objects are called
*services* and they are the *tools* that make you dangerous.

To make life awesome, you can ask Symfony to pass you a service by using a type-hint.
What other possible classes or interfaces could you use? Find out by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

=============================================================== =====================================
Class/Interface Type                                            Alias Service ID
=============================================================== =====================================
``Psr\Cache\CacheItemPoolInterface``                            alias for "cache.app.recorder"
``Psr\Log\LoggerInterface``                                     alias for "monolog.logger"
``Symfony\Component\EventDispatcher\EventDispatcherInterface``  alias for "debug.event_dispatcher"
``Symfony\Component\HttpFoundation\RequestStack``               alias for "request_stack"
``Symfony\Component\HttpFoundation\Session\SessionInterface``   alias for "session"
``Symfony\Component\Routing\RouterInterface``                   alias for "router.default"
=============================================================== =====================================

This is just a short summary of the full list! And as you add more packages, this
list of tools will grow!

Creating Services
-----------------

To keep your code organized, you can even create your own services! Suppose you
want to generate a random greeting (e.g. "Hello", "Yo", etc). Instead of putting
this code directly in your controller, create a new class:

    // src/GreetingGenerator.php
    namespace App;

    class GreetingGenerator
    {
        public function getRandomGreeting()
        {
            $greetings = ['Hey', 'Yo', 'Aloha'];
            $greeting = $greetings[array_rand($greetings)];

            return $greeting;
        }
    }

Great! You can use this immediately in your controller::

    use App\GreetingGenerator;
    // ...

    public function index($name, LoggerInterface $logger, GreetingGenerator $generator)
    {
        $greeting = $generator->getRandomGreeting();

        $logger->info("Saying $greeting to $name!");

        // ...
    }

That's it! Symfony will instantiate the ``GreetingGenerator`` automatically and
pass it as an argument. But, could we *also* move the logger logic to ``GreetingGenerator``?
Yes! You can use autowiring inside a service to access *other* services. The only
difference is that it's done in the constructor:

.. code-block:: diff

    + use Psr\Log\LoggerInterface;

    class GreetingGenerator
    {
    +     private $logger;
    + 
    +     public function __construct(LoggerInterface $logger)
    +     {
    +         $this->logger = $logger;
    +     }

        public function getRandomGreeting()
        {
            // ...

     +        $this->logger->info('Using the greeting: '.$greeting);

             return $greeting;
        }
    }

Yes! This works too: no configuration, no time wasted.

Event Subscriber
----------------



.. _`Monolog`: https://github.com/Seldaek/monolog
