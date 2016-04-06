.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

.. versionadded:: 3.1
    The ``ArgumentResolver`` and value resolvers are added in Symfony 3.1.

In the book, you've learned that you can add the :class:`Symfony\\Component\\HttpFoundation\\Request`
as action argument and it will be injected into the method. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. The ``ArgumentResolver`` uses
several value resolvers which allow you to extend the functionality.


Functionality Shipped With The HttpKernel
-----------------------------------------

Symfony ships with four value resolvers in the HttpKernel:
  * The :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\ArgumentFromAttributeResolver`
    attempts to find a request attribute that matches the name of the argument.

  * The :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\RequestValueResolver`
    injects the current ``Request`` if type-hinted with ``Request``, or a sub-class thereof.

  * The :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\DefaultValueResolver`
    will set the default value of the argument if present and the argument is optional.

  * The :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\VariadicValueResolver`
    verifies in the request if your data is an array and will add all of them to the argument list.
    When the action is called, the last (variadic) argument will contain all the values of this array.

.. note::

    In older versions of Symfony this logic was all resolved within the ``ControllerResolver``. The
    old functionality is moved to the ``LegacyArgumentResolver``, which contains the previously
    used resolving logic.

Adding a New Value Resolver
---------------------------

Adding a new value resolver requires one class and one service defintion. In our next example, we
will be creating a shortcut to inject the ``User`` object from our security. Given you write the following
action::

    namespace AppBundle\Controller;

    use AppBundle\User;
    use Symfony\Component\HttpFoundation\Response;

    class UserController
    {
        public function indexAction(User $user)
        {
            return new Response('<html><body>Hello '.$user->getUsername().'!</body></html>');
        }
    }

Somehow you will have to get the ``User`` object and inject it into our action. This can be done
by implementing the :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`.
This interface specifies that you have to implement two methods::

    interface ArgumentValueResolverInterface
    {
        public function supports(Request $request, ArgumentMetadata $argument);
        public function resolve(Request $request, ArgumentMetadata $argument);
    }

  * The ``supports()`` method is used to check whether the resolver supports the given argument. It will
    only continue if it returns ``true``.

  * The ``resolve()`` method will be used to resolve the actual value just acknowledged by
    ``supports()``. Once a value is resolved you can ``yield`` the value to the ``ArgumentResolver``.

  * The ``Request`` object is the current ``Request`` which would also be injected into your
    action in the forementioned functionality.

  * The :class:``Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadata`` represents
    information retrieved from the method signature for the current argument it's trying to resolve.

.. note::

    The ``ArgumentMetadata`` is a simple data container created by the
    :class:``Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadataFactory``. This
    factory will work on every supported PHP version but might give different results. E.g. the
    ``isVariadic()`` will never return true on PHP 5.5 and only on PHP 7.0 and higher it will give
    you basic types when calling ``getType()``.

Now that you know what to do, you can implement this interface. In order to get the current ``User``,
you will have to get it from the ``TokenInterface`` which is in the ``TokenStorageInterface``::

    namespace AppBundle\ArgumentValueResolver;

    use AppBundle\User;
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
            return ($token = $this->tokenStorage->getToken()) && $token->getUser() instanceof User;
        }

        public function resolve(Request $request, ArgumentMetadata $argument)
        {
            yield $this->tokenStorage->getToken()->getUser();
        }
    }

This was pretty simple, now all you have to do is add the configuration for the service container. This
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
        <services>
            <service id="app.value_resolver.user" class="AppBundle\ArgumentValueResolver\UserValueResolver">
                <argument type="service" id="security.token_storage">
                <tag name="kernel.argument_resolver" priority="50" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $defintion = new Definition(
            'AppBundle\ArgumentValueResolver\UserValueResolver',
            array(new Reference('security.token_storage'))
        );
        $definition->addTag('kernel.argument_resolver', array('priority' => 50));
        $container->setDefinition('app.value_resolver.user', $definition);
