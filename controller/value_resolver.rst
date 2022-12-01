.. index::
   single: Controller; Argument Value Resolvers

Extending Action Argument Resolving
===================================

In the :doc:`controller guide </controller>`, you've learned that you can get the
:class:`Symfony\\Component\\HttpFoundation\\Request` object via an argument in
your controller. This argument has to be type-hinted by the ``Request`` class
in order to be recognized. This is done via the
:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver`. By
creating and registering custom value resolvers, you can extend this
functionality.

.. _functionality-shipped-with-the-httpkernel:

Built-In Value Resolvers
------------------------

Symfony ships with the following value resolvers in the
:doc:`HttpKernel component </components/http_kernel>`:

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver`
    Attempts to resolve a backed enum case from a route path parameter that matches the name of the argument.
    Leads to a 404 Not Found response if the value isn't a valid backing value for the enum type.

    For example, if your backed enum is::

        namespace App\Model;

        enum Suit: string
        {
            case Hearts = 'H';
            case Diamonds = 'D';
            case Clubs = 'C';
            case Spades = 'S';
        }

    And your controller contains the following::

        class CardController
        {
            #[Route('/cards/{suit}')]
            public function list(Suit $suit): Response
            {
                // ...
            }

            // ...
        }

    When requesting the ``/cards/H`` URL, the ``$suit`` variable will store the
    ``Suit::Hearts`` case.

    Furthermore, you can limit route parameter's allowed values to
    only one (or more) with ``EnumRequirement``::

        use Symfony\Component\Routing\Requirement\EnumRequirement;

        // ...

        class CardController
        {
            #[Route('/cards/{suit}', requirements: [
                // this allows all values defined in the Enum
                'suit' => new EnumRequirement(Suit::class),
                // this restricts the possible values to the Enum values listed here
                'suit' => new EnumRequirement([Suit::Diamonds, Suit::Spades]),
            ])]
            public function list(Suit $suit): Response
            {
                // ...
            }

            // ...
        }

    The example above allows requesting only ``/cards/D`` and ``/cards/S``
    URLs and leads to 404 Not Found response in two other cases.

    .. versionadded:: 6.1

        The ``BackedEnumValueResolver`` and ``EnumRequirement`` were introduced in Symfony 6.1.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver`
    Attempts to find a request attribute that matches the name of the argument.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver`
    Attempts to find a request attribute that matches the name of the argument
    and injects a ``DateTimeInterface`` object if type-hinted with a class
    extending ``DateTimeInterface``.

    By default any input that can be parsed as a date string by PHP is accepted.
    You can restrict how the input can be formatted with the
    :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapDateTime` attribute.

    .. versionadded:: 6.1

        The ``DateTimeValueResolver`` was introduced in Symfony 6.1.

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

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\UidValueResolver`
    Attempts to convert any UID values from a route path parameter into UID objects.
    Leads to a 404 Not Found response if the value isn't a valid UID.

    For example, the following will convert the token parameter into a ``UuidV4`` object::

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;
        use Symfony\Component\Uid\UuidV4;

        class DefaultController
        {
            #[Route('/share/{token}')]
            public function share(UuidV4 $token): Response
            {
                // ...
            }
        }

    .. versionadded:: 6.1

        The ``UidValueResolver`` was introduced in Symfony 6.1.

:class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver`
    Verifies if the request data is an array and will add all of them to the
    argument list. When the action is called, the last (variadic) argument will
    contain all the values of this array.

In addition, some components and official bundles provide other value resolvers:

:class:`Symfony\\Component\\Security\\Http\\Controller\\UserValueResolver`
    Injects the object that represents the current logged in user if type-hinted
    with ``UserInterface``. You can also type-hint your own ``User`` class but you
    must then add the ``#[CurrentUser]`` attribute to the argument. Default value
    can be set to ``null`` in case  the controller can be accessed by anonymous
    users. It requires installing the :doc:`SecurityBundle </security>`.

    If the argument is not nullable and there is no logged in user or the logged in
    user has a user class not matching the type-hinted class, an ``AccessDeniedException``
    is thrown by the resolver to prevent access to the controller.

PSR-7 Objects Resolver:
    Injects a Symfony HttpFoundation ``Request`` object created from a PSR-7 object
    of type :class:`Psr\\Http\\Message\\ServerRequestInterface`,
    :class:`Psr\\Http\\Message\\RequestInterface` or :class:`Psr\\Http\\Message\\MessageInterface`.
    It requires installing :doc:`the PSR-7 Bridge </components/psr7>` component.

Adding a Custom Value Resolver
------------------------------

In the next example, you'll create a value resolver to inject an ID value
object whenever a controller argument has a type implementing
``IdentifierInterface`` (e.g. ``BookingId``)::

    // src/Controller/BookingController.php
    namespace App\Controller;

    use App\Reservation\BookingId;
    use Symfony\Component\HttpFoundation\Response;

    class BookingController
    {
        public function index(BookingId $id): Response
        {
            // ... do something with $id
        }
    }

.. versionadded:: 6.2

    The ``ValueResolverInterface`` was introduced in Symfony 6.2. Prior to
    6.2, you had to use the
    :class:`Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface`,
    which defines different methods.

Adding a new value resolver requires creating a class that implements
:class:`Symfony\\Component\\HttpKernel\\Controller\\ValueResolverInterface`
and defining a service for it.

This interface contains a ``resolve()`` method, which is called for each
argument of the controller. It receives the current ``Request`` object and an
:class:`Symfony\\Component\\HttpKernel\\ControllerMetadata\\ArgumentMetadata`
instance, which contains all information from the method signature.

The ``resolve()`` method should return either an empty array (if it cannot resolve
this argument) or an array with the resolved value(s). Usually arguments are
resolved as a single value, but variadic arguments require resolving multiple
values. That's why you must always return an array, even for single values:

.. code-block:: php

    // src/ValueResolver/IdentifierValueResolver.php
    namespace App\ValueResolver;

    use App\IdentifierInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
    use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

    class BookingIdValueResolver implements ValueResolverInterface
    {
        public function resolve(Request $request, ArgumentMetadata $argument): array
        {
            // get the argument type (e.g. BookingId)
            $argumentType = $argument->getType();
            if (
                !$argumentType
                || !is_subclass_of($argumentType, IdentifierInterface::class, true)
            ) {
                return [];
            }

            // get the value from the request, based on the argument name
            $value = $request->attributes->get($argument->getName());
            if (!is_string($value)) {
                return [];
            }

            // create and return the value object
            return [$argumentType::fromString($value)];
        }
    }

This method first checks whether it can resolve the value:

* The argument must be type-hinted with a class implementing a custom ``IdentifierInterface``;
* The argument name (e.g. ``$id``) must match the name of a request
  attribute (e.g. using a ``/booking/{id}`` route placeholder).

When those requirements are met, the method creates a new instance of the
custom value object and returns it as the value for this argument.

That's it! Now all you have to do is add the configuration for the service
container. This can be done by tagging the service with ``controller.argument_value_resolver``
and adding a priority:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                # ... be sure autowiring is enabled
                autowire: true
            # ...

            App\ArgumentResolver\BookingIdValueResolver:
                tags:
                    - { name: controller.argument_value_resolver, priority: 150 }

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

                <service id="App\ArgumentResolver\BookingIdValueResolver">
                    <tag name="controller.argument_value_resolver" priority="150"/>
                </service>
            </services>

        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\ArgumentResolver\BookingIdValueResolver;

        return static function (ContainerConfigurator $container) {
            $services = $configurator->services();

            $services->set(BookingIdValueResolver::class)
                ->tag('controller.argument_value_resolver', ['priority' => 150])
            ;
        };

While adding a priority is optional, it's recommended to add one to make sure
the expected value is injected. The built-in ``RequestAttributeValueResolver``,
which fetches attributes from the ``Request``, has a priority of ``100``. If your
resolver also fetches ``Request`` attributes, set a priority of ``100`` or more.
Otherwise, set a priority lower than ``100`` to make sure the argument resolver
is not triggered when the ``Request`` attribute is present.

To ensure your resolvers are added in the right position you can run the following
command to see which argument resolvers are present and in which order they run:

.. code-block:: terminal

    $ php bin/console debug:container debug.argument_resolver.inner --show-arguments
