.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

In the :doc:`controller guide </controller>`, you've learned that you can get the
:class:`Symfony\\Component\\HttpFoundation\\Request` object via an argument in
your controller. This argument has to be type-hinted by the ``Request`` class
in order to be recognized. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. By
creating and registering custom argument value resolvers, you can extend this
functionality.

.. _functionality-shipped-with-the-httpkernel:

Built-In Value Resolvers
------------------------

Symfony ships with the following value resolvers in the
:doc:`HttpKernel component </components/http_kernel>`:

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver`
    Attempts to find a request attribute that matches the name of the argument.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver`
    Injects the current ``Request`` if type-hinted with ``Request`` or a class
    extending ``Request``.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver`
    Injects a service if type-hinted with a valid service class or interface. This
    works like :doc:`autowiring </service_container/autowiring>`.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver`
    Injects the configured session class implementing ``SessionInterface`` if
    type-hinted with ``SessionInterface`` or a class implementing
    ``SessionInterface``.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver`
    Will set the default value of the argument if present and the argument
    is optional.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver`
    Verifies if the request data is an array and will add all of them to the
    argument list. When the action is called, the last (variadic) argument will
    contain all the values of this array.

In addition, some components and official bundles provide other value resolvers:

:class:`Symfony\\Component\\Security\\Http\\Controller\\UserValueResolver`
    Injects the object that represents the current logged in user if type-hinted
    with ``UserInterface``. Default value can be set to ``null`` in case
    the controller can be accessed by anonymous users. It requires installing
    the :doc:`Security component </components/security>`.

``Psr7ServerRequestResolver``
    Injects a `PSR-7`_ compliant version of the current request if type-hinted
    with ``RequestInterface``, ``MessageInterface`` or ``ServerRequestInterface``.
    It requires installing the `SensioFrameworkExtraBundle`_.

Adding a Custom Value Resolver
------------------------------

In the next example, you'll create a value resolver to inject the object that
represents the current user whenever a controller method type-hints an argument
with the ``User`` class::

    // src/Controller/UserController.php
    namespace App\Controller;

    use App\Entity\User;
    use Symfony\Component\HttpFoundation\Response;

    class UserController
    {
        public function index(User $user)
        {
            return new Response('Hello '.$user->getUserIdentifier().'!');
        }
    }

Beware that this feature is already provided by the `@ParamConverter`_
annotation from the SensioFrameworkExtraBundle. If you have that bundle
installed in your project, add this config to disable the auto-conversion of
type-hinted method arguments:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sensio_framework_extra.yaml
        sensio_framework_extra:
            request:
                converters: true
                auto_convert: false

    .. code-block:: xml

        <!-- config/packages/sensio_framework_extra.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:sensio-framework-extra="http://symfony.com/schema/dic/symfony_extra"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony_extra
                https://symfony.com/schema/dic/symfony_extra/symfony_extra-1.0.xsd">

            <sensio-framework-extra:config>
                <request converters="true" auto-convert="false"/>
            </sensio-framework-extra:config>
        </container>

    .. code-block:: php

        // config/packages/sensio_framework_extra.php
        $container->loadFromExtension('sensio_framework_extra', [
            'request' => [
                'converters' => true,
                'auto_convert' => false,
            ],
        ]);

Adding a new value resolver requires creating a class that implements
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`
and defining a service for it. The interface defines two methods:

``supports()``
    This method is used to check whether the value resolver supports the
    given argument. ``resolve()`` will only be called when this returns ``true``.
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

    // src/ArgumentResolver/UserValueResolver.php
    namespace App\ArgumentResolver;

    use App\Entity\User;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
    use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
    use Symfony\Component\Security\Core\Security;

    class UserValueResolver implements ArgumentValueResolverInterface
    {
        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        public function supports(Request $request, ArgumentMetadata $argument)
        {
            if (User::class !== $argument->getType()) {
                return false;
            }

            return $this->security->getUser() instanceof User;
        }

        public function resolve(Request $request, ArgumentMetadata $argument)
        {
            yield $this->security->getUser();
        }
    }

In order to get the actual ``User`` object in your argument, the given value
must fulfill the following requirements:

* An argument must be type-hinted as ``User`` in your action method signature;
* The value must be an instance of the ``User`` class.

When all those requirements are met and ``true`` is returned, the
``ArgumentResolver`` calls ``resolve()`` with the same values as it called
``supports()``.

That's it! Now all you have to do is add the configuration for the service
container. This can be done by tagging the service with ``controller.argument_value_resolver``
and adding a priority.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                # ... be sure autowiring is enabled
                autowire: true
            # ...

            App\ArgumentResolver\UserValueResolver:
                tags:
                    - { name: controller.argument_value_resolver, priority: 50 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... be sure autowiring is enabled -->
                <defaults autowire="true"/>
                <!-- ... -->

                <service id="App\ArgumentResolver\UserValueResolver">
                    <tag name="controller.argument_value_resolver" priority="50"/>
                </service>
            </services>

        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\ArgumentResolver\UserValueResolver;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(UserValueResolver::class)
                ->tag('controller.argument_value_resolver', ['priority' => 50])
            ;
        };

While adding a priority is optional, it's recommended to add one to make sure
the expected value is injected. The built-in ``RequestAttributeValueResolver``,
which fetches attributes from the ``Request``, has a priority of ``100``. If your
resolver also fetches ``Request`` attributes, set a priority of ``100`` or more.
Otherwise, set a priority lower than ``100`` to make sure the argument resolver
is not triggered when the ``Request`` attribute is present (for example, when
passing the user along sub-requests).

.. tip::

    As you can see in the ``UserValueResolver::supports()`` method, the user
    may not be available (e.g. when the controller is not behind a firewall).
    In these cases, the resolver will not be executed. If no argument value
    is resolved, an exception will be thrown.

    To prevent this, you can add a default value in the controller (e.g. ``User
    $user = null``). The ``DefaultValueResolver`` is executed as the last
    resolver and will use the default value if no value was already resolved.

.. _`@ParamConverter`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`yield`: https://www.php.net/manual/en/language.generators.syntax.php
.. _`PSR-7`: https://www.php-fig.org/psr/psr-7/
.. _`SensioFrameworkExtraBundle`: https://github.com/sensiolabs/SensioFrameworkExtraBundle
