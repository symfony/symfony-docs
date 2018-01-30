.. index::
    single: DependencyInjection; Autowiring

Defining Services Dependencies Automatically (Autowiring)
=========================================================

Autowiring allows you to manage services in the container with minimal
configuration. It reads the type-hints on your constructor (or other methods)
and automatically passes the correct services to each method. Symfony's
autowiring is designed to be predictable: if it is not absolutely clear which
dependency should be passed, you'll see an actionable exception.

.. tip::

    Thanks to Symfony's compiled container, there is no runtime overhead for using
    autowiring.

An Autowiring Example
---------------------

Imagine you're building an API to publish statuses on a Twitter feed, obfuscated
with `ROT13`_... a fun encoder that shifts all characters 13 letters forward in
the alphabet.

Start by creating a ROT13 transformer class::

    namespace App\Util;

    class Rot13Transformer
    {
        public function transform($value)
        {
            return str_rot13($value);
        }
    }

And now a Twitter client using this transformer::

    namespace App\Service;

    use App\Util\Rot13Transformer;

    class TwitterClient
    {
        private $transformer;

        public function __construct(Rot13Transformer $transformer)
        {
            $this->transformer = $transformer;
        }

        public function tweet($user, $key, $status)
        {
            $transformedStatus = $this->transformer->transform($status);

            // ... connect to Twitter and send the encoded status
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
**both classes are automatically registered as services and configured to be autowired**.
This means you can use them immediately without *any* configuration.

However, to understand autowiring better, the following examples explicitly configure
both services. Also, to keep things simple, configure ``TwitterClient`` to be a
:ref:`public <container-public>` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true
                public: false
            # ...

            App\Service\TwitterClient:
                # redundant thanks to _defaults, but value is overridable on each service
                autowire: true
                # not required, will help in our example
                public: true

            App\Util\Rot13Transformer:
                autowire: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false" />
                <!-- ... -->

                <service id="App\Service\TwitterClient" autowire="true" public="true" />

                <service id="App\Util\Rot13Transformer" autowire="true" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Service\TwitterClient;
        use App\Util\Rot13Transformer;

        // ...

        // the autowire method is new in Symfony 3.3
        // in earlier versions, use register() and then call setAutowired(true)
        $container->autowire(TwitterClient::class)
            ->setPublic(true);

        $container->autowire(Rot13Transformer::class)
            ->setPublic(false);

Now, you can use the ``TwitterClient`` service immediately in a controller::

    namespace App\Controller;

    use App\Service\TwitterClient;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends Controller
    {
        /**
         * @Route("/tweet")
         */
        public function tweet()
        {
            // fetch $user, $key, $status from the POST'ed data

            $twitterClient = $this->container->get(TwitterClient::class);
            $twitterClient->tweet($user, $key, $status);

            // ...
        }
    }

This works automatically! The container knows to pass the ``Rot13Transformer`` service
as the first argument when creating the ``TwitterClient`` service.

.. _autowiring-logic-explained:

Autowiring Logic Explained
--------------------------

Autowiring works by reading the ``Rot13Transformer`` *type-hint* in ``TwitterClient``::

    // ...
    use App\Util\Rot13Transformer;

    class TwitterClient
    {
        // ...

        public function __construct(Rot13Transformer $transformer)
        {
            $this->transformer = $transformer;
        }
    }

The autowiring system **looks for a service whose id exactly matches the type-hint**:
so ``App\Util\Rot13Transformer``. In this case, that exists! When you configured
the ``Rot13Transformer`` service, you used its fully-qualified class name as its
id. Autowiring isn't magic: it simply looks for a service whose id matches the type-hint.
If you :ref:`load services automatically <service-container-services-load-example>`,
each service's id is its class name.

If there is *not* a service whose id exactly matches the type, a clear exception
will be thrown.

Autowiring is a great way to automate configuration, and Symfony tries to be as
*predictable* and clear as possible.

.. _service-autowiring-alias:

Using Aliases to Enable Autowiring
----------------------------------

The main way to configure autowiring is to create a service whose id exactly matches
its class. In the previous example, the service's id is ``App\Util\Rot13Transformer``,
which allows us to autowire this type automatically.

This can also be accomplished using an :ref:`alias <services-alias>`. Suppose that
for some reason, the id of the service was instead ``app.rot13.transformer``. In
this case, any arguments type-hinted with the class name (``App\Util\Rot13Transformer``)
can no longer be autowired.

No problem! To fix this, you can *create* a service whose id matches the class by
adding a service alias:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # the id is not a class, so it won't be used for autowiring
            app.rot13.transformer:
                class: App\Util\Rot13Transformer
                # ...

            # but this fixes it!
            # the ``app.rot13.transformer`` service will be injected when
            # an ``App\Util\Rot13Transformer`` type-hint is detected
            App\Util\Rot13Transformer: '@app.rot13.transformer'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.rot13.transformer" class="App\Util\Rot13Transformer" autowire="true" />
                <service id="App\Util\Rot13Transformer" alias="app.rot13.transformer" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Util\Rot13Transformer;

        // ...

        $container->autowire('app.rot13.transformer', Rot13Transformer::class)
            ->setPublic(false);
        $container->setAlias(Rot13Transformer::class, 'app.rot13.transformer');

This creates a service "alias", whose id is ``App\Util\Rot13Transformer``.
Thanks to this, autowiring sees this and uses it whenever the ``Rot13Transformer``
class is type-hinted.

.. tip::

    Aliases are used by the core bundles to allow services to be autowired. For
    example, MonologBundle creates a service whose id is ``logger``. But it also
    adds an alias: ``Psr\Log\LoggerInterface`` that points to the ``logger`` service.
    This is why arguments type-hinted with ``Psr\Log\LoggerInterface`` can be autowired.

.. _autowiring-interface-alias:

Working with Interfaces
-----------------------

You might also find yourself type-hinting abstractions (e.g. interfaces) instead
of concrete classes as it makes it easy to replace your dependencies with other
objects.

To follow this best practice, suppose you decide to create a ``TransformerInterface``::

    namespace App\Util;

    interface TransformerInterface
    {
        public function transform($value);
    }

Then, you update ``Rot13Transformer`` to implement it::

    // ...
    class Rot13Transformer implements TransformerInterface
    {
        // ...
    }

Now that you have an interface, you should use this as your type-hint::

    class TwitterClient
    {
        public function __construct(TransformerInterface $transformer)
        {
             // ...
        }

        // ...
    }

But now, the type-hint (``App\Util\TransformerInterface``) no longer matches
the id of the service (``App\Util\Rot13Transformer``). This means that the
argument can no longer be autowired.

To fix that, add an :ref:`alias <service-autowiring-alias>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Util\Rot13Transformer: ~

            # the ``App\Util\Rot13Transformer`` service will be injected when
            # an ``App\Util\TransformerInterface`` type-hint is detected
            App\Util\TransformerInterface: '@App\Util\Rot13Transformer'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Util\Rot13Transformer" />

                <service id="App\Util\TransformerInterface" alias="App\Util\Rot13Transformer" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Util\Rot13Transformer;
        use App\Util\TransformerInterface;

        // ...
        $container->autowire(Rot13Transformer::class);
        $container->setAlias(TransformerInterface::class, Rot13Transformer::class);

Thanks to the ``App\Util\TransformerInterface`` alias, the autowiring subsystem
knows that the ``App\Util\Rot13Transformer`` service should be injected when
dealing with the ``TransformerInterface``.

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

Suppose you create a second class - ``UppercaseTransformer`` that implements
``TransformerInterface``::

    namespace App\Util;

    class UppercaseTransformer implements TransformerInterface
    {
        public function transform($value)
        {
            return strtoupper($value);
        }
    }

If you register this as a service, you now have *two* services that implement the
``App\Util\TransformerInterface`` type. Symfony doesn't know which one should
be used for autowiring, so you need to choose one by creating an alias from the type
to the correct service id (see :ref:`autowiring-interface-alias`).

If you want ``Rot13Transformer`` to be the service that's used for autowiring, create
that alias:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Util\Rot13Transformer: ~
            App\Util\UppercaseTransformer: ~

            # the ``App\Util\Rot13Transformer`` service will be injected when
            # a ``App\Util\TransformerInterface`` type-hint is detected
            App\Util\TransformerInterface: '@App\Util\Rot13Transformer'

            App\Service\TwitterClient:
                # the Rot13Transformer will be passed as the $transformer argument
                autowire: true

                # If you wanted to choose the non-default service, wire it manually
                # arguments:
                #     $transformer: '@App\Util\UppercaseTransformer'
                # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Util\Rot13Transformer" />
                <service id="App\Util\UppercaseTransformer" />

                <service id="App\Util\TransformerInterface" alias="App\Util\Rot13Transformer" />

                <service id="App\Service\TwitterClient" autowire="true">
                    <!-- <argument key="$transformer" type="service" id="App\Util\UppercaseTransformer" /> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Util\Rot13Transformer;
        use App\Util\UppercaseTransformer;
        use App\Util\TransformerInterface;
        use App\Service\TwitterClient;

        // ...
        $container->autowire(Rot13Transformer::class);
        $container->autowire(UppercaseTransformer::class);
        $container->setAlias(TransformerInterface::class, Rot13Transformer::class);
        $container->autowire(TwitterClient::class)
            //->setArgument('$transformer', new Reference(UppercaseTransformer::class))
        ;

Thanks to the ``App\Util\TransformerInterface`` alias, any argument type-hinted
with this interface will be passed the ``App\Util\Rot13Transformer`` service.
But, you can also manually wire the *other* service by specifying the argument
under the arguments key.

Fixing Non-Autowireable Arguments
---------------------------------

Autowiring only works when your argument is an *object*. But if you have a scalar
argument (e.g. a string), this cannot be autowired: Symfony will throw a clear
exception.

To fix this, you can :ref:`manually wire the problematic argument <services-manually-wire-args>`.
You wire up the difficult arguments, Symfony takes care of the rest.

.. _autowiring-calls:

Autowiring other Methods (e.g. Setters)
---------------------------------------

When autowiring is enabled for a service, you can *also* configure the container
to call methods on your class when it's instantiated. For example, suppose you want
to inject the ``logger`` service, and decide to use setter-injection::

    namespace App\Util;

    class Rot13Transformer
    {
        private $logger;

        /**
         * @required
         */
        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function transform($value)
        {
            $this->logger->info('Transforming '.$value);
            // ...
        }
    }

Autowiring will automatically call *any* method with the ``@required`` annotation
above it, autowiring each argument. If you need to manually wire some of the arguments
to a method, you can always explicitly :doc:`configure the method call </service_container/calls>`.

Autowiring Controller Action Methods
------------------------------------

If you're using the Symfony Framework, you can also autowire arguments to your controller
action methods. This is a special case for autowiring, which exists for convenience.
See :ref:`controller-accessing-services` for more details.

Performance Consequences
------------------------

Thanks to Symfony's compiled container, there is *no* performance penalty for using
autowiring. However, there is a small performance penalty in the ``dev`` environment,
as the container may be rebuilt more often as you modify classes. If rebuilding
your container is slow (possible on very large projects), you may not be able to
use autowiring.

Public and Reusable Bundles
---------------------------

Public bundles should explicitly configure their services and not rely on autowiring.

.. _Rapid Application Development: https://en.wikipedia.org/wiki/Rapid_application_development
.. _ROT13: https://en.wikipedia.org/wiki/ROT13
