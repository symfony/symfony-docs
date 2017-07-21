.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

.. versionadded:: 3.1
    The ``ArgumentResolver`` and value resolvers were introduced in Symfony 3.1.

In the :doc:`controller guide </controller>`, you've learned that you can get the
:class:`Symfony\\Component\\HttpFoundation\\Request` object via an argument in
your controller. This argument has to be type-hinted by the ``Request`` class
in order to be recognized. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. By
creating and registering custom argument value resolvers, you can extend this
functionality.

.. tip::

    A custom ``ParamConverter`` can also create the object for a type-hinted class. 
    This functionality is similar to an ``ArgumentResolver``. However 
    ``ParamConverter`` are executed first and usually use request parameters to 
    create the object.

    See :ref:`ParamConverter <https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html#creating-a-converter>`
    for more information.

Functionality Shipped with the HttpKernel
-----------------------------------------

Symfony ships with four value resolvers in the HttpKernel component:

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver`
    Attempts to find a request attribute that matches the name of the argument.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver`
    Injects the current ``Request`` if type-hinted with ``Request`` or a class
    extending ``Request``.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver`
    Will set the default value of the argument if present and the argument
    is optional.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver`
    Verifies if the request data is an array and will add all of them to the
    argument list. When the action is called, the last (variadic) argument will
    contain all the values of this array.

.. note::

    Prior to Symfony 3.1, this logic was resolved within the ``ControllerResolver``.
    The old functionality is rewritten to the aforementioned value resolvers.

Adding a Custom Value Resolver
------------------------------

Adding a new value resolver requires creating one class and one service
definition. In the next example, you'll create a value resolver to inject the
``User`` object from the security system. Given you write the following
controller::

    namespace AppBundle\Controller;

    use AppBundle\Entity\User;
    use Symfony\Component\HttpFoundation\Response;

    class UserController
    {
        public function indexAction(User $user)
        {
            return new Response('Hello '.$user->getUsername().'!');
        }
    }

Somehow you will have to get the ``User`` object and inject it into the controller.
This can be done by implementing the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`.
This interface specifies that you have to implement two methods:

``supports()``
    This method is used to check whether the value resolver supports the
    given argument. ``resolve()`` will only be executed when this returns ``true``.
``resolve()``
    This method will resolve the actual value for the argument. Once the value
    is resolved, you must `yield`_ the value to the ``ArgumentResolver``.

Both methods get the ``Request`` object, which is the current request, and an
:class:`Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadata`
instance. This object contains all information retrieved from the method signature
for the current argument.

Now that you know what to do, you can implement this interface. To get the
current ``User``, you need the current security token. This token can be
retrieved from the token storage::

    // src/AppBundle/ArgumentResolver/UserValueResolver.php
    namespace AppBundle\ArgumentResolver;

    use AppBundle\Entity\User;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
    use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

    class UserValueResolver implements ArgumentValueResolverInterface
    {
        private $tokenStorage;

        public function __construct(TokenStorageInterface $tokenStorage)
        {
            $this->tokenStorage = $tokenStorage;
        }

        public function supports(Request $request, ArgumentMetadata $argument)
        {
            if (User::class !== $argument->getType()) {
                return false;
            }

            $token = $this->tokenStorage->getToken();

            if (!$token instanceof TokenInterface) {
                return false;
            }

            return $token->getUser() instanceof User;
        }

        public function resolve(Request $request, ArgumentMetadata $argument)
        {
            yield $this->tokenStorage->getToken()->getUser();
        }
    }

In order to get the actual ``User`` object in your argument, the given value
must fulfill the following requirements:

* An argument must be type-hinted as ``User`` in your action method signature;
* A security token must be present;
* The value must be an instance of the ``User``.

When all those requirements are met and ``true`` is returned, the
``ArgumentResolver`` calls ``resolve()`` with the same values as it called
``supports()``.

That's it! Now all you have to do is add the configuration for the service
container. This can be done by tagging the service with ``controller.argument_value_resolver``
and adding a priority.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.value_resolver.user:
                class: AppBundle\ArgumentResolver\UserValueResolver
                arguments:
                    - '@security.token_storage'
                tags:
                    - { name: controller.argument_value_resolver, priority: 50 }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="'http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.value_resolver.user"
                    class="AppBundle\ArgumentResolver\UserValueResolver"
                >
                    <argument type="service" id="security.token_storage">
                    <tag name="controller.argument_value_resolver" priority="50" />
                </service>
            </services>

        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(
            'AppBundle\ArgumentResolver\UserValueResolver',
            array(new Reference('security.token_storage'))
        );
        $definition->addTag('controller.argument_value_resolver', array('priority' => 50));
        $container->setDefinition('app.value_resolver.user', $definition);

While adding a priority is optional, it's recommended to add one to make sure
the expected value is injected. The ``RequestAttributeValueResolver`` has a
priority of 100. As this one is responsible for fetching attributes from the
``Request``, it's recommended to trigger your custom value resolver with a
lower priority. This makes sure the argument resolvers are not triggered when
the attribute is present. For instance, when passing the user along a
subrequests.

.. tip::

    As you can see in the ``UserValueResolver::supports()`` method, the user
    may not be available (e.g. when the controller is not behind a firewall).
    In these cases, the resolver will not be executed. If no argument value
    is resolved, an exception will be thrown.

    To prevent this, you can add a default value in the controller (e.g. ``User
    $user = null``). The ``DefaultValueResolver`` is executed as the last
    resolver and will use the default value if no value was already resolved.

.. _`yield`: http://php.net/manual/en/language.generators.syntax.php
