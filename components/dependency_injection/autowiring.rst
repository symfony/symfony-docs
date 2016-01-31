.. index::
    single: DependencyInjection; Autowiring

Defining Services Dependencies Automatically
============================================

Autowiring allows to register services in the container with minimal configuration.
It is useful in the field of `Rapid Application Development`_, when designing prototypes
in early stages of large projects. It makes it easy to register a service graph
and eases refactoring.

Imagine you're building an API to publish statuses on a Twitter feed, obfuscated
with `ROT13`.. (a special case of the Caesar cipher).

Start by creating a ROT13 transformer class::

    // src/AppBundle/Rot13Transformer.php
    namespace AppBundle;

    class Rot13Transformer
    {
        public function transform($value)
        {
            return str_rot13($value);
        }
    }

And now a Twitter client using this transformer::

    // src/AppBundle/TwitterClient.php
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

The Dependency Injection Component will be able to automatically register the dependencies
of this ``TwitterClient`` class by marking the ``twitter_client`` service as autowired:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            twitter_client:
                class:    'AppBundle\TwitterClient'
                autowire: true

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="twitter_client" class="AppBundle\TwitterClient" autowire="true" />
            </services>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition = new Definition('AppBundle\TwitterClient');
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

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

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

You can give a try to the API with ``curl``::

    curl -d "user=kevin&key=ABCD&status=Hello" http://localhost:8000/tweet

It should return ``OK``.

Working with Interfaces
-----------------------

You might also find yourself using abstractions instead of implementations (especially
in grown applications) as it allows to easily replace some dependencies without
modifying the class depending of them.

To follow this best practice, constructor arguments must be typehinted with interfaces
and not concrete classes. It allows to replace easily the current implementation
if necessary. It also allows to use other transformers.

Let's introduce a ``TransformerInterface``::

    // src/AppBundle/TransformerInterface.php
    namespace AppBundle;

    interface TransformerInterface
    {
        public function transform($value);
    }

Then edit ``Rot13Transformer`` to make it implementing the new interface::

    // ...

    class Rot13Transformer implements TransformerInterface

    // ...


And update ``TwitterClient`` to depend of this new interface::

    class TwitterClient
    {
        // ...

        public function __construct(TransformerInterface $transformer)
        {
             // ...
        }

        // ...
    }

Finally the service definition must be updated because, obviously, the autowiring
subsystem isn't able to find itself the interface implementation to register::

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            rot13_transformer:
                class: 'AppBundle\Rot13Transformer'

            twitter_client:
                class:    'AppBundle\TwitterClient'
                autowire: true

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="rot13_transformer" class="AppBundle\Rot13Transformer" />
                <service id="twitter_client" class="AppBundle\TwitterClient" autowire="true" />
            </services>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition1 = new Definition('AppBundle\Rot13Transformer');
        $container->setDefinition('rot13_transformer', $definition1);

        $definition2 = new Definition('AppBundle\TwitterClient');
        $definition2->setAutowired(true);
        $container->setDefinition('twitter_client', $definition2);

The autowiring subsystem detects that the ``rot13_transformer`` service implements
the ``TransformerInterface`` and injects it automatically. Even when using
interfaces (and you should), building the service graph and refactoring the project
is easier than with standard definitions.

Dealing with Multiple Implementations of the Same Type
------------------------------------------------------

Last but not least, the autowiring feature allows to specify the default implementation
of a given type. Let's introduce a new implementation of the ``TransformerInterface``
returning the result of the ROT13 transformation uppercased::

    // src/AppBundle/UppercaseRot13Transformer.php
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

This class is intended to decorate the any transformer and return its value uppercased.

We can now refactor the controller to add another endpoint leveraging this new
transformer::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

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
and a Twitter client using it::

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            rot13_transformer:
                class:            'AppBundle\Rot13Transformer'
                autowiring_types: 'AppBundle\TransformerInterface'

            twitter_client:
                class:    'AppBundle\TwitterClient'
                autowire: true

            uppercase_rot13_transformer:
                class:    'AppBundle\UppercaseRot13Transformer'
                autowire: true

            uppercase_twitter_client:
                class:     'AppBundle\TwitterClient'
                arguments: [ '@uppercase_rot13_transformer' ]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="rot13_transformer" class="AppBundle\Rot13Transformer">
                    <autowiring-type>AppBundle\TransformerInterface</autowiring-type>
                </service>
                <service id="twitter_client" class="AppBundle\TwitterClient" autowire="true" />
                <service id="uppercase_rot13_transformer" class="AppBundle\UppercaseRot13Transformer" autowire="true" />
                <service id="uppercase_twitter_client" class="AppBundle\TwitterClient">
                    <argument type="service" id="uppercase_rot13_transformer" />
                </service>
            </services>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition1 = new Definition('AppBundle\Rot13Transformer');
        $definition1->setAutowiringTypes(array('AppBundle\TransformerInterface'));
        $container->setDefinition('rot13_transformer', $definition1);

        $definition2 = new Definition('AppBundle\TwitterClient');
        $definition2->setAutowired(true);
        $container->setDefinition('twitter_client', $definition2);

        $definition3 = new Definition('AppBundle\UppercaseRot13Transformer');
        $definition3->setAutowired(true);
        $container->setDefinition('uppercase_rot13_transformer', $definition3);

        $definition4 = new Definition('AppBundle\TwitterClient');
        $definition4->addArgument(new Reference('uppercase_rot13_transformer'));
        $container->setDefinition('uppercase_twitter_client', $definition4);

It deserves some explanations. We now have 2 services implementing the ``TransformerInterface``.
The autowiring subsystem cannot guess which one to use, this leads to errors
like::

      [Symfony\Component\DependencyInjection\Exception\RuntimeException]
      Unable to autowire argument of type "AppBundle\TransformerInterface" for the service "twitter_client".

Fortunately, the ``autowiring_types`` key is here to specify which implementation
to use by default. This key can take a list of types if necessary (using a YAML
array).

Thanks to this setting, the ``rot13_transformer`` service is automatically injected
as an argument of the ``uppercase_rot13_transformer`` and ``twitter_client`` services. For
the ``uppercase_twitter_client``, we use a standard service definition to inject
the specific ``uppercase_rot13_transformer`` service.

As for other RAD features such as the FrameworkBundle controller or annotations,
keep in mind to not use autowiring in public bundles nor in large projects with
complex maintenance needs.

.. _Rapid Application Development: https://en.wikipedia.org/wiki/Rapid_application_development
.. _ROT13: https://en.wikipedia.org/wiki/ROT13
