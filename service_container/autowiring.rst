.. index::
    single: DependencyInjection; Autowiring

Defining Services Dependencies Automatically (Autowiring)
=========================================================

Autowiring allows to register services in the container with minimal configuration.
It automatically resolves the service dependencies based on the constructor's
typehint which is useful in the field of `Rapid Application Development`_,
when designing prototypes in early stages of large projects. It makes it easy
to register a service graph and eases refactoring.

Imagine you're building an API to publish statuses on a Twitter feed, obfuscated
with `ROT13`_ (a special case of the Caesar cipher).

Start by creating a ROT13 transformer class::

    namespace AppBundle;

    class Rot13Transformer
    {
        public function transform($value)
        {
            return str_rot13($value);
        }
    }

And now a Twitter client using this transformer::

    namespace AppBundle;

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

The DependencyInjection component will be able to automatically register
the dependencies of this ``TwitterClient`` class when its service is marked as
autowired:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\TwitterClient:
                autowire: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\TwitterClient" autowire="true" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\TwitterClient;

        // ...

        $container->autowire(TwitterClient::class);


.. versionadded:: 3.3
    The method ``ContainerBuilder::autowire()`` was introduced in Symfony 3.3.
    Prior to version 3.3, you needed to use the ``Definition::setAutowired()``
    method.

The autowiring subsystem will detect the dependencies of the ``TwitterClient``
class by parsing its constructor. For instance it will find here an instance of
a ``Rot13Transformer`` as dependency.

The subsystem will first try to find a service whose id is the required type, so
here it'll search for a service named ``AppBundle\Rot13Transformer``.
It will be considered as the default implementation and will win over any other
services implementing the required type.

*In case this service does not exist*, the subsystem will detect the types of
all services and check if one - and only one - implements the required type, and
inject it if it's the case. If there are several services of the same type, an
exception will be thrown. You'll have to use an explicit service definition or
register a default implementation by creating a service or an alias whose id is
the required type (as seen above).
Note that this step is deprecated and will no longer be done in 4.0. The
subsystem will directly pass to the third check.

At last, if no service implements the required type, as it's the case here, the
subsystem is, as long as it's a concrete class, smart enough to automatically
register a private service for it.
Here it'll register a private service for the ``Rot13Transformer`` class and set
it as first argument of the ``twitter_client`` service.

As you can see, the autowiring feature drastically reduces the amount of configuration
required to define a service. No more arguments section! It also makes it easy
to change the dependencies of the ``TwitterClient`` class: just add or remove typehinted
arguments in the constructor and you are done. There is no need anymore to search
and edit related service definitions.

Here is a typical controller using the ``TwitterClient``::

    namespace AppBundle\Controller;

    use AppBundle\TwitterClient;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

    class DefaultController extends Controller
    {
        /**
         * @Route("/tweet")
         * @Method("POST")
         */
        public function tweetAction(Request $request, TwitterClient $twitterClient)
        {
            $user = $request->request->get('user');
            $key = $request->request->get('key');
            $status = $request->request->get('status');

            if (!$user || !$key || !$status) {
                throw new BadRequestHttpException();
            }

            $twitterClient->tweet($user, $key, $status);

            // or using the container
            // $this->container->get(TwitterClient::class)->tweet($user, $key, $status);

            return new Response('OK');
        }
    }

You can give the API a try using ``curl``:

.. code-block:: bash

    $ curl -d "user=kevin&key=ABCD&status=Hello" http://localhost:8000/tweet

It should return ``OK``.

Working with Interfaces
-----------------------

You might also find yourself using abstractions instead of implementations (especially
in grown applications) as it allows to easily replace some dependencies without
modifying the class depending of them.

To follow this best practice, constructor arguments must be typehinted with interfaces
and not concrete classes. It allows to replace easily the current implementation
if necessary. It also allows to use other transformers. You can create a
``TransformerInterface`` containing just one method (``transform()``)::

    namespace AppBundle;

    interface TransformerInterface
    {
        public function transform($value);
    }

Then edit ``Rot13Transformer`` to make it implementing the new interface::

    // ...
    class Rot13Transformer implements TransformerInterface
    {
        // ...
    }

And update ``TwitterClient`` to depend of this new interface::

    class TwitterClient
    {
        public function __construct(TransformerInterface $transformer)
        {
             // ...
        }

        // ...
    }

Finally the service definition must be updated because, obviously, the autowiring
subsystem isn't able to find itself the interface implementation to register.
You have to indicate which service must be injected for your interface when
using autowiring:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Rot13Transformer: ~

            # the ``AppBundle\Rot13Transformer`` service will be injected when
            # a ``AppBundle\TransformerInterface`` type-hint is detected
            AppBundle\TransformerInterface: '@AppBundle\Rot13Transformer'

            AppBundle\TwitterClient:
                autowire: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Rot13Transformer" />

                <service id="AppBundle\TransformerInterface" alias="AppBundle\Rot13Transformer" />

                <service id="AppBundle\TwitterClient" autowire="true" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Rot13Transformer;
        use AppBundle\TransformerInterface;
        use AppBundle\TwitterClient;

        // ...
        $container->register(Rot13Transformer::class);
        $container->setAlias(TransformerInterface::class, Rot13Transformer::class);

        $container->autowire(TwitterClient::class);

Thanks to the ``AppBundle\TransformerInterface`` alias, the autowiring subsystem
knows that the ``AppBundle\Rot13Transformer`` service must be injected when
dealing with the ``TransformerInterface`` and injects it automatically. Even
when using interfaces (and you should), building the service graph and
refactoring the project is easier than with standard definitions.

.. _service-autowiring-alias:

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

To deal with multiple implementations of the same type, the manipulation is the
same as when dealing with an interface. You have to register a service whose id
is your type: this will indicate to the autowiring subsystem which service to
use by default for this type.
So if you have several services implementing the same type, you can decide which
one the subsystem should use. Let's introduce a new implementation of the
``TransformerInterface`` returning the result of the ROT13 transformation
uppercased::

    namespace AppBundle;

    class UppercaseTransformer implements TransformerInterface
    {
        private $transformer;

        public function __construct(TransformerInterface $transformer)
        {
            $this->transformer = $transformer;
        }

        public function transform($value)
        {
            return strtoupper($this->transformer->transform($value));
        }
    }

This class is intended to decorate any transformer and return its value uppercased.

The controller can now be refactored to add a new endpoint using this uppercase
transformer::

    namespace AppBundle\Controller;

    use AppBundle\TwitterClient;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

    class DefaultController extends Controller
    {
        /**
         * @Route("/tweet")
         * @Method("POST")
         */
        public function tweetAction(Request $request, TwitterClient $twitterClient)
        {
            // Here the client is automatically injected because it's the default
            // implementation

            return $this->tweet($request, $twitterClient);
        }

        /**
         * @Route("/tweet-uppercase")
         * @Method("POST")
         */
        public function tweetUppercaseAction(Request $request)
        {
            // not the default implementation
            $twitterClient = $this->get('uppercase_twitter_client');

            return $this->tweet($request, $twitterClient);
        }

        private function tweet(Request $request, TwitterClient $twitterClient)
        {
            $user = $request->request->get('user');
            $key = $request->request->get('key');
            $status = $request->request->get('status');

            if (!$user || !$key || !$status) {
                throw new BadRequestHttpException();
            }

            $twitterClient->tweet($user, $key, $status);

            return new Response('OK');
        }
    }

The last step is to update service definitions to register this new implementation
and a Twitter client using it:

.. configuration-block::

    .. code-block:: yaml

        services:
            AppBundle\Rot13Transformer: ~

            # the ``AppBundle\Rot13Transformer`` service will *always* be used
            # when ``AppBundle\TransformerInterface`` is detected by the
            # autowiring subsystem
            AppBundle\TransformerInterface: '@AppBundle\Rot13Transformer'

            AppBundle\TwitterClient:
                autowire: true

            AppBundle\UppercaseTransformer:
                autowire: true

            uppercase_twitter_client:
                class:     AppBundle\TwitterClient
                arguments: ['@AppBundle\UppercaseTransformer']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Rot13Transformer" />

                <service id="AppBundle\TransformerInterface" alias="AppBundle\Rot13Transformer" />

                <service id="AppBundle\TwitterClient" autowire="true" />

                <service id="AppBundle\UppercaseTransformer" autowire="true" />

                <service id="uppercase_twitter_client" class="AppBundle\TwitterClient">
                    <argument type="service" id="AppBundle\UppercaseTransformer" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Rot13Transformer;
        use AppBundle\TransformerInterface;
        use AppBundle\TwitterClient;
        use AppBundle\UppercaseTransformer;
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->register(Rot13Transformer::class);
        $container->setAlias(TransformerInterface::class, Rot13Transformer::class);

        $container->autowire(TwitterClient::class);
        $container->autowire(UppercaseTransformer::class);
        $container->register('uppercase_twitter_client', TwitterClient::class)
            ->addArgument(new Reference(UppercaseTransformer::class));

This deserves some explanations. You now have two services implementing the
``TransformerInterface``. As said earlier, the autowiring subsystem cannot guess
which one to use which leads to errors like this:

.. code-block:: text

      [Symfony\Component\DependencyInjection\Exception\RuntimeException]
      Unable to autowire argument of type "AppBundle\TransformerInterface" for the service "AppBundle\TwitterClient".

Fortunately, the FQCN alias (the ``AppBundle\TransformerInterface`` alias) is
here to specify which implementation to use by default.

.. versionadded:: 3.3
    Using FQCN aliases to fix autowiring ambiguities is allowed since Symfony
    3.3. Prior to version 3.3, you needed to use the ``autowiring_types`` key.

Thanks to this alias, the ``AppBundle\Rot13Transformer`` service is
automatically injected as an argument of the ``AppBundle\UppercaseTransformer``
and ``AppBundle\TwitterClient`` services. For the ``uppercase_twitter_client``,
a standard service definition is used to inject the specific
``AppBundle\UppercaseTransformer`` service.

As for other RAD features such as the FrameworkBundle controller or annotations,
keep in mind to not use autowiring in public bundles nor in large projects with
complex maintenance needs.

.. _Rapid Application Development: https://en.wikipedia.org/wiki/Rapid_application_development
.. _ROT13: https://en.wikipedia.org/wiki/ROT13
