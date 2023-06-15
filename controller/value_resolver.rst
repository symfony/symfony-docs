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

    .. tip::

        The ``DateTimeInterface`` object is generated with the :doc:`Clock component </components/clock>`.
        This. gives your full control over the date and time values the controller
        receives when testing your application and using the
        :class:`Symfony\\Component\\Clock\\MockClock` implementation.

    .. versionadded:: 6.1

        The ``DateTimeValueResolver`` and the ``MapDateTime`` attribute were
        introduced in Symfony 6.1.

    .. versionadded:: 6.3

        The use of the :doc:`Clock component </components/clock>` to generate the
        ``DateTimeInterface`` object was introduced in Symfony 6.3.

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

In addition, some components, bridges and official bundles provide other value resolvers:

:class:`Symfony\\Component\\Security\\Http\\Controller\\UserValueResolver`
    Injects the object that represents the current logged in user if type-hinted
    with ``UserInterface``. You can also type-hint your own ``User`` class but you
    must then add the ``#[CurrentUser]`` attribute to the argument. Default value
    can be set to ``null`` in case  the controller can be accessed by anonymous
    users. It requires installing the :doc:`SecurityBundle </security>`.

    If the argument is not nullable and there is no logged in user or the logged in
    user has a user class not matching the type-hinted class, an ``AccessDeniedException``
    is thrown by the resolver to prevent access to the controller.

:class:`Symfony\\Component\\Security\\Http\\Controller\\SecurityTokenValueResolver`
    Injects the object that represents the current logged in token if type-hinted
    with ``TokenInterface`` or a class extending it.

    If the argument is not nullable and there is no logged in token, an ``HttpException``
    with status code 401 is thrown by the resolver to prevent access to the controller.

    .. versionadded:: 6.3

        The ``SecurityTokenValueResolver`` was introduced in Symfony 6.3.

:class:`Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver`
    Automatically query for an entity and pass it as an argument to your controller.

    For example, the following will query the ``Product`` entity which has ``{id}`` as primary key::

        // src/Controller/DefaultController.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class DefaultController
        {
            #[Route('/product/{id}')]
            public function share(Product $product): Response
            {
                // ...
            }
        }

    To learn more about the use of the ``EntityValueResolver``, see the dedicated
    section :ref:`Automatically Fetching Objects <doctrine-entity-value-resolver>`.

    .. versionadded:: 6.2

        The ``EntityValueResolver`` was introduced in Symfony 6.2.

PSR-7 Objects Resolver:
    Injects a Symfony HttpFoundation ``Request`` object created from a PSR-7 object
    of type :class:`Psr\\Http\\Message\\ServerRequestInterface`,
    :class:`Psr\\Http\\Message\\RequestInterface` or :class:`Psr\\Http\\Message\\MessageInterface`.
    It requires installing :doc:`the PSR-7 Bridge </components/psr7>` component.

Managing Value Resolvers
------------------------

For each argument, every resolver tagged with ``controller.argument_value_resolver``
will be called until one provides a value. The order in which they are called depends
on their priority. For example, the ``SessionValueResolver`` will be called before the
``DefaultValueResolver`` because its priority is higher. This allows to write e.g.
``SessionInterface $session = null`` to get the session if there is one, or ``null``
if there is none.

In that specific case, you don't need any resolver running before
``SessionValueResolver``, so skipping them would not only improve performance,
but also prevent one of them providing a value before ``SessionValueResolver``
has a chance to.

The :class:`Symfony\\Component\\HttpKernel\\Attribute\\ValueResolver` attribute
lets you do this by "targeting" the resolver you want::

    // src/Controller/SessionController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    use Symfony\Component\HttpKernel\Attribute\ValueResolver;
    use Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver;
    use Symfony\Component\Routing\Annotation\Route;

    class SessionController
    {
        #[Route('/')]
        public function __invoke(
            #[ValueResolver(SessionValueResolver::class)]
            SessionInterface $session = null
        ): Response
        {
            // ...
        }
    }

.. versionadded:: 6.3

    The ``ValueResolver`` attribute was introduced in Symfony 6.3.

In the example above, the ``SessionValueResolver`` will be called first because
it is targeted. The ``DefaultValueResolver`` will be called next if no value has
been provided; that's why you can assign ``null`` as ``$session``'s default value.

You can target a resolver by passing its name as ``ValueResolver``'s first argument.
For convenience, built-in resolvers' name are their FQCN.

A targeted resolver can also be disabled by passing ``ValueResolver``'s ``$disabled``
argument to ``true``; this is how :ref:`MapEntity allows to disable the
EntityValueResolver for a specific controller <doctrine-entity-value-resolver>`.
Yes, ``MapEntity`` extends ``ValueResolver``!

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
values. That's why you must always return an array, even for single values::

    // src/ValueResolver/IdentifierValueResolver.php
    namespace App\ValueResolver;

    use App\IdentifierInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
    use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

    class BookingIdValueResolver implements ValueResolverInterface
    {
        public function resolve(Request $request, ArgumentMetadata $argument): iterable
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
container. This can be done by adding one of the following tags to your value resolver.

``controller.argument_value_resolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This tag is automatically added to every service implementing ``ValueResolverInterface``,
but you can set it yourself to change its ``priority`` or ``name`` attributes.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                # ... be sure autowiring is enabled
                autowire: true
            # ...

            App\ValueResolver\BookingIdValueResolver:
                tags:
                    - controller.argument_value_resolver:
                        name: booking_id
                        priority: 150

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

                <service id="App\ValueResolver\BookingIdValueResolver">
                    <tag name="booking_id" priority="150">controller.argument_value_resolver</tag>
                </service>
            </services>

        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\ValueResolver\BookingIdValueResolver;

        return static function (ContainerConfigurator $containerConfigurator): void {
            $services = $containerConfigurator->services();

            $services->set(BookingIdValueResolver::class)
                ->tag('controller.argument_value_resolver', ['name' => 'booking_id', 'priority' => 150])
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

You can also configure the name passed to the ``ValueResolver`` attribute to target
your resolver. Otherwise it will default to the service's id.

``controller.targeted_value_resolver``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Set this tag if you want your resolver to be called only if it is targeted by a
``ValueResolver`` attribute. Like ``controller.argument_value_resolver``, you
can customize the name by which your resolver can be targeted.

As an alternative, you can add the
:class:`Symfony\\Component\\HttpKernel\\Attribute\\AsTargetedValueResolver` attribute
to your resolver and pass your custom name as its first argument::

    // src/ValueResolver/IdentifierValueResolver.php
    namespace App\ValueResolver;

    use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
    use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

    #[AsTargetedValueResolver('booking_id')]
    class BookingIdValueResolver implements ValueResolverInterface
    {
        // ...
    }

You can then pass this name as ``ValueResolver``'s first argument to target your resolver::

    // src/Controller/BookingController.php
    namespace App\Controller;

    use App\Reservation\BookingId;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\ValueResolver;

    class BookingController
    {
        public function index(#[ValueResolver('booking_id')] BookingId $id): Response
        {
            // ... do something with $id
        }
    }

.. versionadded:: 6.3

    The ``controller.targeted_value_resolver`` tag and ``AsTargetedValueResolver``
    attribute were introduced in Symfony 6.3.
