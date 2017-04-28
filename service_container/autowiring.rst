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

    namespace Acme;

    class Rot13Transformer
    {
        public function transform($value)
        {
            return str_rot13($value);
        }
    }

And now a Twitter client using this transformer::

    namespace Acme;

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
the dependencies of this ``TwitterClient`` class when the ``twitter_client``
service is marked as autowired:

.. configuration-block::

    .. code-block:: yaml

        services:
            twitter_client:
                class:    Acme\TwitterClient
                autowire: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="twitter_client" class="Acme\TwitterClient" autowire="true" />
            </services>
        </container>

    .. code-block:: php

        use Acme\TwitterClient;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition = new Definition(TwitterClient::class);
        $definition->setAutowired(true);

        $container->setDefinition('twitter_client', $definition);

The autowiring subsystem will detect the dependencies of the ``TwitterClient``
class by parsing its constructor. For instance it will find here an instance of
a ``Rot13Transformer`` as dependency. If an existing service definition (and only
one – see below) is of the required type, this service will be injected. If it's
not the case (like in this example), the subsystem is smart enough to automatically
register a private service for the ``Rot13Transformer`` class and set it as first
argument of the ``twitter_client`` service. Again, it can work only if there is one
class of the given type. If there are several classes of the same type, you must
use an explicit service definition or register a default implementation.

As you can see, the autowiring feature drastically reduces the amount of configuration
required to define a service. No more arguments section! It also makes it easy
to change the dependencies of the ``TwitterClient`` class: just add or remove typehinted
arguments in the constructor and you are done. There is no need anymore to search
and edit related service definitions.

Here is a typical controller using the ``twitter_client`` service::

    namespace Acme\Controller;

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
        public function tweetAction(Request $request)
        {
            $user = $request->request->get('user');
            $key = $request->request->get('key');
            $status = $request->request->get('status');

            if (!$user || !$key || !$status) {
                throw new BadRequestHttpException();
            }

            $this->get('twitter_client')->tweet($user, $key, $status);

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

    namespace Acme;

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
subsystem isn't able to find itself the interface implementation to register:

.. configuration-block::

    .. code-block:: yaml

        services:
            rot13_transformer:
                class: Acme\Rot13Transformer

            twitter_client:
                class:    Acme\TwitterClient
                autowire: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="rot13_transformer" class="Acme\Rot13Transformer" />

                <service id="twitter_client" class="Acme\TwitterClient" autowire="true" />
            </services>
        </container>

    .. code-block:: php

        use Acme\TwitterClient;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->register('rot13_transformer', 'Acme\Rot13Transformer');

        $clientDefinition = new Definition(TwitterClient::class);
        $clientDefinition->setAutowired(true);
        $container->setDefinition('twitter_client', $clientDefinition);

The autowiring subsystem detects that the ``rot13_transformer`` service implements
the ``TransformerInterface`` and injects it automatically. Even when using
interfaces (and you should), building the service graph and refactoring the project
is easier than with standard definitions.

.. _service-autowiring-alias:

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

Last but not least, the autowiring feature allows to specify the default implementation
of a given type. Let's introduce a new implementation of the ``TransformerInterface``
returning the result of the ROT13 transformation uppercased::

    namespace Acme;

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

    namespace Acme\Controller;

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
        public function tweetAction(Request $request)
        {
            return $this->tweet($request, 'twitter_client');
        }

        /**
         * @Route("/tweet-uppercase")
         * @Method("POST")
         */
        public function tweetUppercaseAction(Request $request)
        {
            return $this->tweet($request, 'uppercase_twitter_client');
        }

        private function tweet(Request $request, $service)
        {
            $user = $request->request->get('user');
            $key = $request->request->get('key');
            $status = $request->request->get('status');

            if (!$user || !$key || !$status) {
                throw new BadRequestHttpException();
            }

            $this->get($service)->tweet($user, $key, $status);

            return new Response('OK');
        }
    }

The last step is to update service definitions to register this new implementation
and a Twitter client using it:

.. configuration-block::

    .. code-block:: yaml

        services:
            rot13_transformer:
                class: Acme\Rot13Transformer

            Acme\TransformerInterface: '@rot13_transformer'

            twitter_client:
                class:    Acme\TwitterClient
                autowire: true

            uppercase_transformer:
                class:    Acme\UppercaseTransformer
                autowire: true

            uppercase_twitter_client:
                class:     Acme\TwitterClient
                arguments: ['@uppercase_transformer']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="rot13_transformer" class="Acme\Rot13Transformer" />

                <service id="Acme\TransformerInterface" alias="rot13_transformer" />

                <service id="twitter_client" class="Acme\TwitterClient" autowire="true" />

                <service id="uppercase_transformer" class="Acme\UppercaseTransformer"
                    autowire="true"
                />

                <service id="uppercase_twitter_client" class="Acme\TwitterClient">
                    <argument type="service" id="uppercase_transformer" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Acme\Rot13Transformer;
        use Acme\TransformerInterface;
        use Acme\TwitterClient;
        use Acme\UppercaseTransformer;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->register('rot13_transformer', Rot13Transformer::class);
        $container->setAlias(TransformerInterface::class, 'rot13_transformer')

        $clientDefinition = new Definition(TwitterClient::class);
        $clientDefinition->setAutowired(true);
        $container->setDefinition('twitter_client', $clientDefinition);

        $uppercaseDefinition = new Definition(UppercaseTransformer::class);
        $uppercaseDefinition->setAutowired(true);
        $container->setDefinition('uppercase_transformer', $uppercaseDefinition);

        $uppercaseClientDefinition = new Definition(TwitterClient::class, array(
            new Reference('uppercase_transformer'),
        ));
        $container->setDefinition('uppercase_twitter_client', $uppercaseClientDefinition);

This deserves some explanations. You now have two services implementing the
``TransformerInterface``. The autowiring subsystem cannot guess which one
to use which leads to errors like this:

.. code-block:: text

      [Symfony\Component\DependencyInjection\Exception\RuntimeException]
      Unable to autowire argument of type "Acme\TransformerInterface" for the service "twitter_client".

Fortunately, the FQCN alias is here to specify which implementation
to use by default.

.. versionadded:: 3.3
    Using FQCN aliases to fix autowiring ambiguities is allowed since Symfony
    3.3. Prior to version 3.3, you needed to use the ``autowiring_types`` key.

Thanks to this alias, the ``rot13_transformer`` service is automatically injected
as an argument of the ``uppercase_transformer`` and ``twitter_client`` services. For
the ``uppercase_twitter_client``, a standard service definition is used to
inject the specific ``uppercase_transformer`` service.

As for other RAD features such as the FrameworkBundle controller or annotations,
keep in mind to not use autowiring in public bundles nor in large projects with
complex maintenance needs.

.. _Rapid Application Development: https://en.wikipedia.org/wiki/Rapid_application_development
.. _ROT13: https://en.wikipedia.org/wiki/ROT13
