.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

.. versionadded:: 3.1
    The ``ArgumentResolver`` and value resolvers were introduced in Symfony 3.1.

In the book, you've learned that you can get the :class:`Symfony\\Component\\HttpFoundation\\Request`
object via an argument in your controller. This argument has to be typehinted
by the ``Request`` class in order to be recognized. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. By
creating and registering custom argument value resolvers, you can extend
this functionality.

Functionality Shipped with the HttpKernel
-----------------------------------------

Symfony ships with four value resolvers in the HttpKernel component:

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\ArgumentFromAttributeResolver`
    Attempts to find a request attribute that matches the name of the argument.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\RequestValueResolver`
    Injects the current ``Request`` if type-hinted with ``Request``, or a
    sub-class thereof.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\DefaultValueResolver`
    Will set the default value of the argument if present and the argument
    is optional.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolver\\VariadicValueResolver`
    Verifies in the request if your data is an array and will add all of
    them to the argument list. When the action is called, the last (variadic)
    argument will contain all the values of this array.

.. note::

    Prior to Symfony 3.1, this logic was resolved within the ``ControllerResolver``.
    The old functionality is rewritten to the aforementioned value resolvers.

Adding a Custom Value Resolver
------------------------------

Adding a new value resolver requires one class and one service defintion.
In the next example, you'll create a value resolver to inject the ``User``
object from the security system. Given you write the following action::

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

Somehow you will have to get the ``User`` object and inject it into the controller.
This can be done by implementing the :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`.
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

When all those requirements are met and true is returned, the ``ArgumentResolver``
calls ``resolve()`` with the same values as it called ``supports()``.

That's it! Now all you have to do is add the configuration for the service
container. This can be done by tagging the service with ``controller.argument_resolver``
and adding a priority.

.. note::

    While adding a priority is optional, it's recommended to add one to
    make sure the expected value is injected. The ``ArgumentFromAttributeResolver``
    has a priority of 100. As this one is responsible for fetching attributes
    from the ``Request``, it's also recommended to trigger your custom value
    resolver with a lower priority. This makes sure the argument resolvers
    are not triggered in (e.g.) subrequests if you pass your user along:
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
                    - { name: controller.argument_value_resolver, priority: 50 }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="'http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.value_resolver.user" class="AppBundle\ArgumentValueResolver\UserValueResolver">
                    <argument type="service" id="security.token_storage">
                    <tag name="controller.argument_value_resolver" priority="50" />
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
        $definition->addTag('controller.argument_value_resolver', array('priority' => 50));
        $container->setDefinition('app.value_resolver.user', $definition);

Creating an Optional User Resolver
----------------------------------

When you want your user to be optional, e.g. when your page is behind a
firewall that also allows anonymous authentication, you might not always
have a security user. To get this to work, you only have to change your
method signature to `UserInterface $user = null`.

When you take the ``UserValueResolver`` from the previous example, you can
see there is no logic in case of failure to comply to the requirements. Default
values are defined in the signature and are available in the ``ArgumentMetadata``.
When a default value is available and there are no resolvers that support
the given value, the ``DefaultValueResolver`` is triggered. This Resolver
takes the default value of your argument and yields it to the argument list::

    namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
    use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

    final class DefaultValueResolver implements ArgumentValueResolverInterface
    {
        public function supports(Request $request, ArgumentMetadata $argument)
        {
            return $argument->hasDefaultValue();
        }

        public function resolve(Request $request, ArgumentMetadata $argument)
        {
            yield $argument->getDefaultValue();
        }
    }

.. _`yield`: http://php.net/manual/en/language.generators.syntax.php
