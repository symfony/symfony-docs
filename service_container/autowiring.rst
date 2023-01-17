.. index::
    single: DependencyInjection; Autowiring

Defining Services Dependencies Automatically (Autowiring)
=========================================================

Autowiring allows you to manage services in the container with minimal
configuration. It reads the type-hints on your constructor (or other methods)
and automatically passes the correct services to each method. Symfony's
autowiring is designed to be predictable: if it is not absolutely clear which
dependency should be passed, you will see an actionable exception.

.. tip::

    Thanks to Symfony's compiled container, there is no runtime overhead for using
    autowiring.

An Autowiring Example
---------------------

Imagine you are building an API to publish statuses on a Twitter feed, obfuscated
with `ROT13`_, a fun encoder that shifts all characters 13 letters forward in
the alphabet.

Start by creating a ROT13 transformer class::

    // src/Util/Rot13Transformer.php
    namespace App\Util;

    class Rot13Transformer
    {
        public function transform(string $value): string
        {
            return str_rot13($value);
        }
    }

And now a Twitter client using this transformer::

    // src/Service/TwitterClient.php
    namespace App\Service;

    use App\Util\Rot13Transformer;
    // ...

    class TwitterClient
    {
        private $transformer;

        public function __construct(Rot13Transformer $transformer)
        {
            $this->transformer = $transformer;
        }

        public function tweet(User $user, string $key, string $status): void
        {
            $transformedStatus = $this->transformer->transform($status);

            // ... connect to Twitter and send the encoded status
        }
    }

If you are using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
**both classes are automatically registered as services and configured to be autowired**.
This means you can use them immediately without *any* configuration.

However, to understand autowiring better, the following examples explicitly configure
both services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true
            # ...

            App\Service\TwitterClient:
                # redundant thanks to _defaults, but value is overridable on each service
                autowire: true

            App\Util\Rot13Transformer:
                autowire: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true"/>
                <!-- ... -->

                <!-- autowire is redundant thanks to defaults, but value is overridable on each service -->
                <service id="App\Service\TwitterClient" autowire="true"/>

                <service id="App\Util\Rot13Transformer" autowire="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
            ;

            $services->set(TwitterClient::class)
                // redundant thanks to defaults, but value is overridable on each service
                ->autowire();

            $services->set(Rot13Transformer::class)
                ->autowire();
        };


Now, you can use the ``TwitterClient`` service immediately in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Service\TwitterClient;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends AbstractController
    {
        /**
         * @Route("/tweet", methods={"POST"})
         */
        public function tweet(TwitterClient $twitterClient, Request $request): Response
        {
            // fetch $user, $key, $status from the POST'ed data

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

    // src/Service/TwitterClient.php
    namespace App\Service;

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
id. Autowiring is not magic: it looks for a service whose id matches the type-hint.
If you :ref:`load services automatically <service-container-services-load-example>`,
each service's id is its class name.

If there is *not* a service whose id exactly matches the type, a clear exception
will be thrown.

Autowiring is a great way to automate configuration, and Symfony tries to be as
*predictable* and as clear as possible.

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

            # the id is not a class, so it will not be used for autowiring
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
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.rot13.transformer" class="App\Util\Rot13Transformer" autowire="true"/>
                <service id="App\Util\Rot13Transformer" alias="app.rot13.transformer"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Util\Rot13Transformer;

        return function(ContainerConfigurator $containerConfigurator) {
            // ...

            // the id is not a class, so it will not be used for autowiring
            $services->set('app.rot13.transformer', Rot13Transformer::class)
                ->autowire();

            // but this fixes it!
            // the ``app.rot13.transformer`` service will be injected when
            // an ``App\Util\Rot13Transformer`` type-hint is detected
            $services->alias(Rot13Transformer::class, 'app.rot13.transformer');
        };


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
of concrete classes as it replaces your dependencies with other objects.

To follow this best practice, suppose you decide to create a ``TransformerInterface``::

    // src/Util/TransformerInterface.php
    namespace App\Util;

    interface TransformerInterface
    {
        public function transform(string $value): string;
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
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Util\Rot13Transformer"/>

                <service id="App\Util\TransformerInterface" alias="App\Util\Rot13Transformer"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Util\Rot13Transformer;
        use App\Util\TransformerInterface;

        return function(ContainerConfigurator $containerConfigurator) {
            // ...

            $services->set(Rot13Transformer::class);

            // the ``App\Util\Rot13Transformer`` service will be injected when
            // an ``App\Util\TransformerInterface`` type-hint is detected
            $services->alias(TransformerInterface::class, Rot13Transformer::class);
        };


Thanks to the ``App\Util\TransformerInterface`` alias, the autowiring subsystem
knows that the ``App\Util\Rot13Transformer`` service should be injected when
dealing with the ``TransformerInterface``.

.. tip::

    When using a `service definition prototype`_, if only one service is
    discovered that implements an interface, configuring the alias is not mandatory
    and Symfony will automatically create one.

.. tip::

    Autowiring is powerful enough to guess which service to inject even when using
    union and intersection types. This means you are able to type-hint argument with
    complex types like this::

        use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
        use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
        use Symfony\Component\Serializer\SerializerInterface;

        class DataFormatter
        {
            public function __construct((NormalizerInterface&DenormalizerInterface)|SerializerInterface $transformer)
            {
                // ...
            }

            // ...
        }

.. versionadded:: 5.4

    The support of union and intersection types was introduced in Symfony 5.4.

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

Suppose you create a second class - ``UppercaseTransformer`` that implements
``TransformerInterface``::

    // src/Util/UppercaseTransformer.php
    namespace App\Util;

    class UppercaseTransformer implements TransformerInterface
    {
        public function transform(string $value): string
        {
            return strtoupper($value);
        }
    }

If you register this as a service, you now have *two* services that implement the
``App\Util\TransformerInterface`` type. Autowiring subsystem can not decide
which one to use. Remember, autowiring is not magic; it looks for a service
whose id matches the type-hint. So you need to choose one by creating an alias
from the type to the correct service id (see :ref:`autowiring-interface-alias`).
Additionally, you can define several named autowiring aliases if you want to use
one implementation in some cases, and another implementation in some
other cases.

For instance, you may want to use the ``Rot13Transformer``
implementation by default when the ``TransformerInterface`` interface is
type hinted, but use the ``UppercaseTransformer`` implementation in some
specific cases. To do so, you can create a normal alias from the
``TransformerInterface`` interface to ``Rot13Transformer``, and then
create a *named autowiring alias* from a special string containing the
interface followed by a variable name matching the one you use when doing
the injection::

    // src/Service/MastodonClient.php
    namespace App\Service;

    use App\Util\TransformerInterface;

    class MastodonClient
    {
        private $transformer;

        public function __construct(TransformerInterface $shoutyTransformer)
        {
            $this->transformer = $shoutyTransformer;
        }

        public function toot(User $user, string $key, string $status): void
        {
            $transformedStatus = $this->transformer->transform($status);

            // ... connect to Mastodon and send the transformed status
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Util\Rot13Transformer: ~
            App\Util\UppercaseTransformer: ~

            # the ``App\Util\UppercaseTransformer`` service will be
            # injected when an ``App\Util\TransformerInterface``
            # type-hint for a ``$shoutyTransformer`` argument is detected.
            App\Util\TransformerInterface $shoutyTransformer: '@App\Util\UppercaseTransformer'

            # If the argument used for injection does not match, but the
            # type-hint still matches, the ``App\Util\Rot13Transformer``
            # service will be injected.
            App\Util\TransformerInterface: '@App\Util\Rot13Transformer'

            App\Service\TwitterClient:
                # the Rot13Transformer will be passed as the $transformer argument
                autowire: true

                # If you wanted to choose the non-default service and do not
                # want to use a named autowiring alias, wire it manually:
                # arguments:
                #     $transformer: '@App\Util\UppercaseTransformer'
                # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->
                <service id="App\Util\Rot13Transformer"/>
                <service id="App\Util\UppercaseTransformer"/>

                <service id="App\Util\TransformerInterface" alias="App\Util\Rot13Transformer"/>
                <service
                    id="App\Util\TransformerInterface $shoutyTransformer"
                    alias="App\Util\UppercaseTransformer"/>

                <service id="App\Service\TwitterClient" autowire="true">
                    <!-- <argument key="$transformer" type="service" id="App\Util\UppercaseTransformer"/> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MastodonClient;
        use App\Service\TwitterClient;
        use App\Util\Rot13Transformer;
        use App\Util\TransformerInterface;
        use App\Util\UppercaseTransformer;

        return function(ContainerConfigurator $containerConfigurator) {
            // ...

            $services->set(Rot13Transformer::class)->autowire();
            $services->set(UppercaseTransformer::class)->autowire();

            // the ``App\Util\UppercaseTransformer`` service will be
            // injected when an ``App\Util\TransformerInterface``
            // type-hint for a ``$shoutyTransformer`` argument is detected.
            $services->alias(TransformerInterface::class.' $shoutyTransformer', UppercaseTransformer::class);

            // If the argument used for injection does not match, but the
            // type-hint still matches, the ``App\Util\Rot13Transformer``
            // service will be injected.
            $services->alias(TransformerInterface::class, Rot13Transformer::class);

            $services->set(TwitterClient::class)
                // the Rot13Transformer will be passed as the $transformer argument
                ->autowire()

                // If you wanted to choose the non-default service and do not
                // want to use a named autowiring alias, wire it manually:
                //     ->arg('$transformer', service(UppercaseTransformer::class))
                // ...
            ;
        };

Thanks to the ``App\Util\TransformerInterface`` alias, any argument type-hinted
with this interface will be passed the ``App\Util\Rot13Transformer`` service.
If the argument is named ``$shoutyTransformer``,
``App\Util\UppercaseTransformer`` will be used instead.
But, you can also manually wire any *other* service by specifying the argument
under the arguments key.

Another possibility is to use the ``#[Target]`` attribute. By using this attribute
on the argument you want to autowire, you can define exactly which service to inject
by using its alias. Thanks to this, you are able to have multiple services implementing
the same interface and keep the argument name decorrelated of any implementation name
(like shown in the example above).

Let's say you defined the ``app.uppercase_transformer`` alias for the
``App\Util\UppercaseTransformer`` service. You would be able to use the ``#[Target]``
attribute like this::

    // src/Service/MastodonClient.php
    namespace App\Service;

    use App\Util\TransformerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Target;

    class MastodonClient
    {
        private $transformer;

        public function __construct(#[Target('app.uppercase_transformer')] TransformerInterface $transformer)
        {
            $this->transformer = $transformer;
        }
    }

.. versionadded:: 5.3

    The ``#[Target]`` attribute was introduced in Symfony 5.3.

Fixing Non-Autowireable Arguments
---------------------------------

Autowiring only works when your argument is an *object*. But if you have a scalar
argument (e.g. a string), this cannot be autowired: Symfony will throw a clear
exception.

To fix this, you can :ref:`manually wire the problematic argument <services-manually-wire-args>`.
You wire up the difficult arguments, Symfony takes care of the rest.

.. _autowiring-calls:

Autowiring other Methods (e.g. Setters and Public Typed Properties)
-------------------------------------------------------------------

When autowiring is enabled for a service, you can *also* configure the container
to call methods on your class when it is instantiated. For example, suppose you want
to inject the ``logger`` service, and decide to use setter-injection:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Util/Rot13Transformer.php
        namespace App\Util;

        class Rot13Transformer
        {
            private $logger;

            /**
             * @required
             */
            public function setLogger(LoggerInterface $logger): void
            {
                $this->logger = $logger;
            }

            public function transform($value): string
            {
                $this->logger->info('Transforming '.$value);
                // ...
            }
        }

    .. code-block:: php-attributes

        // src/Util/Rot13Transformer.php
        namespace App\Util;

        use Symfony\Contracts\Service\Attribute\Required;

        class Rot13Transformer
        {
            private $logger;

            #[Required]
            public function setLogger(LoggerInterface $logger): void
            {
                $this->logger = $logger;
            }

            public function transform($value): string
            {
                $this->logger->info('Transforming '.$value);
                // ...
            }
        }

Autowiring will automatically call *any* method with the ``#[Required]`` attribute
above it, autowiring each argument. If you need to manually wire some of the arguments
to a method, you can always explicitly :doc:`configure the method call </service_container/calls>`.

If your PHP version doesn't support attributes (they were introduced in PHP 8),
you can use the ``@required`` annotation instead.

.. versionadded:: 5.2

    The ``#[Required]`` attribute was introduced in Symfony 5.2.

Despite property injection having some :ref:`drawbacks <property-injection>`,
autowiring with ``#[Required]`` or ``@required`` can also be applied to public
typed properties:

.. configuration-block::

    .. code-block:: php-annotations

        namespace App\Util;

        class Rot13Transformer
        {
            /** @required */
            public LoggerInterface $logger;

            public function transform($value)
            {
                $this->logger->info('Transforming '.$value);
                // ...
            }
        }

    .. code-block:: php-attributes

        namespace App\Util;

        use Symfony\Contracts\Service\Attribute\Required;

        class Rot13Transformer
        {
            #[Required]
            public LoggerInterface $logger;

            public function transform($value)
            {
                $this->logger->info('Transforming '.$value);
                // ...
            }
        }

.. versionadded:: 5.1

    Public typed properties autowiring was introduced in Symfony 5.1.

Autowiring Controller Action Methods
------------------------------------

If you are using the Symfony Framework, you can also autowire arguments to your controller
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
Autowiring depends on the services that are available in the container and bundles have
no control over the service container of applications they are included in. You can use
autowiring when building reusable bundles within your company, as you have full control
over all code.

.. _ROT13: https://en.wikipedia.org/wiki/ROT13
.. _service definition prototype: https://symfony.com/blog/new-in-symfony-3-3-psr-4-based-service-discovery
