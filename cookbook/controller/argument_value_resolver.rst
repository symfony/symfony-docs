.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

.. versionadded:: 3.1
    The ``ArgumentResolver`` and value resolvers are added in Symfony 3.1.

In the book, you've learned that you can get the :class:`Symfony\\Component\\HttpFoundation\\Request`
object by adding a ``Request`` argument to your controller. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. By creating and registering custom
argument value resolvers, you can extend this functionality.


Functionality Shipped With The HttpKernel
-----------------------------------------

Symfony ships with four value resolvers in the HttpKernel:

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\ArgumentFromAttributeResolver`
    Attempts to find a request attribute that matches the name of the argument.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\RequestValueResolver`
    Injects the current ``Request`` if type-hinted with ``Request``, or a sub-class thereof.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\DefaultValueResolver`
    Will set the default value of the argument if present and the argument is optional.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\VariadicValueResolver`
    Verifies in the request if your data is an array and will add all of them to the argument list.
    When the action is called, the last (variadic) argument will contain all the values of this array.

.. note::

    Prior to Symfony 3.1, this logic was resolved within the ``ControllerResolver``. The old
    functionality is moved to the ``LegacyArgumentResolver``, which contains the previously
    used resolving logic.

Adding a Custom Value Resolver
------------------------------

Adding a new value resolver requires one class and one service defintion. In the next example,
you'll create a value resolver to inject the ``User`` object from the security system.. Given
you write the following action::

    namespace AppBundle\Controller;

    use AppBundle\Entity\User;
    use Symfony\Component\HttpFoundation\Response;

    class UserController
    {
        public function indexAction(User $user)
        {
            return new Response('<html><body>Hello '.$user->getUsername().'!</body></html>');
        }
    }

Somehow you will have to get the ``User`` object and inject it into the controller. This can be done
by implementing the :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`.
This interface specifies that you have to implement two methods::

    interface ArgumentValueResolverInterface
    {
        public function supports(Request $request, ArgumentMetadata $argument);
        public function resolve(Request $request, ArgumentMetadata $argument);
    }

``supports()``
    This method is used to check whether the value resolver supports the
    given argument. ``resolve()`` will only be executed when this returns ``true``.
``resolve()``
    This method will resolve the actual value for the argument. Once the value
    is resolved, you should `yield`_ the value to the ``ArgumentResolver``.

Both methods get the ``Request`` object, which is the current request, and an
:class:`Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadata`.
This object contains all informations retrieved from the method signature for the
current argument.

.. note::

    The ``ArgumentMetadata`` is a simple data container created by the
    :class:`Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadataFactory`. This
    factory will work on every supported PHP version but might give different results. E.g. the
    ``isVariadic()`` will never return true on PHP 5.5 and only on PHP 7.0 and higher it will give
    you basic types when calling ``getType()``.

Now that you know what to do, you can implement this interface. To get the current ``User``, you need
the current security token. This token can be retrieved from the token storage.::

    namespace AppBundle\ArgumentValueResolver;

    use AppBundle\Entity\User;
    use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
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

            $token = $this->tokenStorage->getToken()
            return $token->getUser() instanceof User;
        }

        public function resolve(Request $request, ArgumentMetadata $argument)
        {
            yield $this->tokenStorage->getToken()->getUser();
        }
    }

That's it! Now all you have to do is add the configuration for the service container. This
can be done by tagging the service with ``kernel.argument_resolver`` and adding a priority.

.. note::

    While adding a priority is optional, it's recommended to add one to make sure the expected
    value is injected. The ``ArgumentFromAttributeResolver`` has a priority of 100. As this
    one is responsible for fetching attributes from the ``Request``, it's also recommended to
    trigger your custom value resolver with a lower priority. This makes sure the argument
    resolvers are not triggered in (e.g.) subrequests if you pass your user along:
    ``{{ render(controller('AppBundle:User:index', {'user', app.user})) }}``.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.value_resolver.user:
                class: AppBundle\ArgumentValueResolver\UserValueResolver
                arguments:
                    - '@security.token_storage'
                tags:
                    - { name: kernel.argument_resolver, priority: 50 }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="'http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.value_resolver.user" class="AppBundle\ArgumentValueResolver\UserValueResolver">
                    <argument type="service" id="security.token_storage">
                    <tag name="kernel.argument_resolver" priority="50" />
                </service>
            </services>

        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $defintion = new Definition(
            'AppBundle\ArgumentValueResolver\UserValueResolver',
            array(new Reference('security.token_storage'))
        );
        $definition->addTag('kernel.argument_resolver', array('priority' => 50));
        $container->setDefinition('app.value_resolver.user', $definition);

.. _`yield`: http://php.net/manual/en/language.generators.syntax.php
