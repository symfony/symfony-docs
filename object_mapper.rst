Object Mapper
=============

.. versionadded:: 7.3

    The ObjectMapper component was introduced in Symfony 7.3 as an
    :doc:`experimental feature </contributing/code/experimental>`.

This component transforms one object into another, simplifying tasks such as
converting DTOs (Data Transfer Objects) into entities or vice versa. It can also
be helpful when decoupling API input/output from internal models, particularly
when working with legacy code or implementing hexagonal architectures.

Installation
------------

Run this command to install the component before using it:

.. code-block:: terminal

    $ composer require symfony/object-mapper

Usage
-----

The object mapper service will be :doc:`autowired </service_container/autowiring>`
automatically in controllers or services when type-hinting for
:class:`Symfony\\Component\\ObjectMapper\\ObjectMapperInterface`::

    // src/Controller/UserController.php
    namespace App\Controller;

    use App\Dto\UserInput;
    use App\Entity\User;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\ObjectMapper\ObjectMapperInterface;

    class UserController extends AbstractController
    {
        public function updateUser(UserInput $userInput, ObjectMapperInterface $objectMapper): Response
        {
            $user = new User();
            // Map properties from UserInput to User
            $objectMapper->map($userInput, $user);

            // ... persist $user and return response
            return new Response('User updated!');
        }
    }

Basic Mapping
-------------

The core functionality is provided by the ``map()`` method. It accepts a
source object and maps its properties to a target. The target can either be
a class name (to create a new instance) or an existing object (to update it).

Mapping to a New Object
~~~~~~~~~~~~~~~~~~~~~~~

Provide the target class name as the second argument::

    use App\Dto\ProductInput;
    use App\Entity\Product;
    use Symfony\Component\ObjectMapper\ObjectMapper;

    $productInput = new ProductInput();
    $productInput->name = 'Wireless Mouse';
    $productInput->sku = 'WM-1024';

    $mapper = new ObjectMapper();
    // creates a new Product instance and maps properties from $productInput
    $product = $mapper->map($productInput, Product::class);

    // $product is now an instance of Product
    // with $product->name = 'Wireless Mouse' and $product->sku = 'WM-1024'

Mapping to an Existing Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Provide an existing object instance as the second argument to update it::

    use App\Dto\ProductUpdateInput;
    use App\Entity\Product;
    use Symfony\Component\ObjectMapper\ObjectMapper;

    $product = $productRepository->find(1);

    $updateInput = new ProductUpdateInput();
    $updateInput->price = 99.99;

    $mapper = new ObjectMapper();
    // updates the existing $product instance
    $mapper->map($updateInput, $product);

    // $product->price is now 99.99

Mapping from ``stdClass``
~~~~~~~~~~~~~~~~~~~~~~~~~

The source object can also be an instance of ``stdClass``. This can be
useful when working with decoded JSON data or loosely typed input::

    use App\Entity\Product;
    use Symfony\Component\ObjectMapper\ObjectMapper;

    $productData = new \stdClass();
    $productData->name = 'Keyboard';
    $productData->sku = 'KB-001';

    $mapper = new ObjectMapper();
    $product = $mapper->map($productData, Product::class);

    // $product is an instance of Product with properties mapped from $productData

Configuring Mapping with Attributes
-----------------------------------

ObjectMapper uses PHP attributes to configure how properties are mapped.
The primary attribute is :class:`Symfony\\Component\\ObjectMapper\\Attribute\\Map`.

Defining the Default Target Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Apply ``#[Map]`` to the source class to define its default mapping target::

    // src/Dto/ProductInput.php
    namespace App\Dto;

    use App\Entity\Product;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: Product::class)]
    class ProductInput
    {
        public string $name = '';
        public string $sku = '';
    }

    // now you can call map() without the second argument if ProductInput is the source:
    $mapper = new ObjectMapper();
    $product = $mapper->map($productInput); // Maps to Product automatically

Configuring Property Mapping
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can apply the ``#[Map]`` attribute to properties to customize their mapping behavior:

* ``target``: Specifies the name of the property in the target object;
* ``source``: Specifies the name of the property in the source object (useful
  when mapping is defined on the target, see below);
* ``if``: Defines a condition for mapping the property;
* ``transform``: Applies a transformation to the value before mapping.

This is how it looks in practice::

    // src/Dto/OrderInput.php
    namespace App\Dto;

    use App\Entity\Order;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: Order::class)]
    class OrderInput
    {
        // map 'customerEmail' from source to 'email' in target
        #[Map(target: 'email')]
        public string $customerEmail = '';

        // do not map this property at all
        #[Map(if: false)]
        public string $internalNotes = '';

        // only map 'discountCode' if it's a non-empty string
        // (uses PHP's strlen() function as a condition)
        #[Map(if: 'strlen')]
        public ?string $discountCode = null;
    }

By default, if a property exists in the source but not in the target, it is
ignored. If a property exists in both and no ``#[Map]`` is defined, the mapper
assumes a direct mapping when names match.

Conditional Mapping with Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For complex conditions, you can use a dedicated service implementing
:class:`Symfony\\Component\\ObjectMapper\\ConditionCallableInterface`::

    // src/ObjectMapper/IsShippableCondition.php
    namespace App\ObjectMapper;

    use App\Dto\OrderInput;
    use App\Entity\Order; // Target type hint
    use Symfony\Component\ObjectMapper\ConditionCallableInterface;

    /**
     * @implements ConditionCallableInterface<OrderInput, Order>
     */
    final class IsShippableCondition implements ConditionCallableInterface
    {
        public function __invoke(mixed $value, object $source, ?object $target): bool
        {
            // example: Only map shipping address if order total is above 50
            return $source->total > 50;
        }
    }

Then, pass the service name (its class name by default) to the ``if`` parameter::

    // src/Dto/OrderInput.php
    namespace App\Dto;

    use App\Entity\Order;
    use App\ObjectMapper\IsShippableCondition;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: Order::class)]
    class OrderInput
    {
        public float $total = 0.0;

        #[Map(if: IsShippableCondition::class)]
        public ?string $shippingAddress = null;
    }

For this to work, ``IsShippableCondition`` must be registered as a service.

.. _object_mapper-conditional-property-target:

Conditional Property Mapping based on Target
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a source class maps to multiple targets, you may want to include or exclude
certain properties depending on which target is being used. Use the
:class:`Symfony\\Component\\ObjectMapper\\Condition\\TargetClass` condition within
the ``if`` parameter of a property's ``#[Map]`` attribute to achieve this.

This pattern is useful for building multiple representations (e.g., public vs. admin)
from a given source object, and can be used as an alternative to
:ref:`serialization groups <serializer-groups-attribute>`::

    // src/Entity/User.php
    namespace App\Entity;

    use App\Dto\AdminUserProfile;
    use App\Dto\PublicUserProfile;
    use Symfony\Component\ObjectMapper\Attribute\Map;
    use Symfony\Component\ObjectMapper\Condition\TargetClass;

    // this User entity can be mapped to two different DTOs
    #[Map(target: PublicUserProfile::class)]
    #[Map(target: AdminUserProfile::class)]
    class User
    {
        // map 'lastLoginIp' to 'ipAddress' ONLY when the target is AdminUserProfile
        #[Map(target: 'ipAddress', if: new TargetClass(AdminUserProfile::class))]
        public ?string $lastLoginIp = '192.168.1.100';

        // map 'registrationDate' to 'memberSince' for both targets
        #[Map(target: 'memberSince')]
        public \DateTimeImmutable $registrationDate;

        public function __construct() {
            $this->registrationDate = new \DateTimeImmutable();
        }
    }

    // src/Dto/PublicUserProfile.php
    namespace App\Dto;
    class PublicUserProfile
    {
        public \DateTimeImmutable $memberSince;
        // no $ipAddress property here
    }

    // src/Dto/AdminUserProfile.php
    namespace App\Dto;
    class AdminUserProfile
    {
        public \DateTimeImmutable $memberSince;
        public ?string $ipAddress = null; // mapped from lastLoginIp
    }

    // usage:
    $user = new User();
    $mapper = new ObjectMapper();

    $publicProfile = $mapper->map($user, PublicUserProfile::class);
    // no IP address available

    $adminProfile = $mapper->map($user, AdminUserProfile::class);
    // $adminProfile->ipAddress = '192.168.1.100'

Transforming Values
-------------------

Use the ``transform`` option within ``#[Map]`` to change a value before it is
assigned to the target. This can be a callable (e.g., a built-in PHP function,
static method, or anonymous function) or a service implementing
:class:`Symfony\\Component\\ObjectMapper\\TransformCallableInterface`.

Using Callables
~~~~~~~~~~~~~~~

Consider the following static utility method::

    // src/Util/PriceFormatter.php
    namespace App\Util;

    class PriceFormatter
    {
        public static function format(float $value, object $source): string
        {
            return number_format($value, 2, '.', '');
        }
    }

You can use that method to format a property when mapping it::

    // src/Dto/ProductInput.php
    namespace App\Dto;

    use App\Entity\Product;
    use App\Util\PriceFormatter;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: Product::class)]
    class ProductInput
    {
        // use a static method from another class for formatting
        #[Map(target: 'displayPrice', transform: [PriceFormatter::class, 'format'])]
        public float $price = 0.0;

        // can also use built-in PHP functions
        #[Map(transform: 'intval')]
        public string $stockLevel = '100';
    }

Using Transformer Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

Similar to conditions, complex transformations can be encapsulated in services
implementing :class:`Symfony\\Component\\ObjectMapper\\TransformCallableInterface`::

    // src/ObjectMapper/FullNameTransformer.php
    namespace App\ObjectMapper;

    use App\Dto\UserInput;
    use App\Entity\User;
    use Symfony\Component\ObjectMapper\TransformCallableInterface;

    /**
     * @implements TransformCallableInterface<UserInput, User>
     */
    final class FullNameTransformer implements TransformCallableInterface
    {
        public function __invoke(mixed $value, object $source, ?object $target): mixed
        {
            return trim($source->firstName . ' ' . $source->lastName);
        }
    }

Then, use this service to format the mapped property::

    // src/Dto/UserInput.php
    namespace App\Dto;

    use App\Entity\User;
    use App\ObjectMapper\FullNameTransformer;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: User::class)]
    class UserInput
    {
        // this property's value will be generated by the transformer
        #[Map(target: 'fullName', transform: new FullNameTransformer())]
        public string $firstName = '';

        public string $lastName = '';
    }

Class-Level Transformation
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can define a transformation at the class level using the ``transform``
parameter on the ``#[Map]`` attribute. This callable runs *after* the target
object is created (if the target is a class name, ``newInstanceWithoutConstructor``
is used), but *before* any properties are mapped. It must return a correctly
initialized instance of the target class (replacing the one created by the mapper
if needed)::

    // src/Dto/LegacyUserData.php
    namespace App\Dto;

    use App\Entity\User;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    // use a static factory method on the target User class for instantiation
    #[Map(target: User::class, transform: [User::class, 'createFromLegacy'])]
    class LegacyUserData
    {
        public int $userId = 0;
        public string $name = '';
    }

And the related target object must define the ``createFromLegacy()`` method::

    // src/Entity/User.php
    namespace App\Entity;
    class User
    {
        public string $name = '';
        private int $legacyId = 0;

        // uses a private constructor to avoid direct instantiation
        private function __construct() {}

        public static function createFromLegacy(mixed $value, object $source): self
        {
            // $value is the initially created (empty) User object
            // $source is the LegacyUserData object
            $user = new self();
            $user->legacyId = $source->userId;

            // property mapping will happen *after* this method returns $user
            return $user;
        }
    }

Mapping Multiple Targets
------------------------

A source class can be configured to map to multiple different target classes.
Apply the ``#[Map]`` attribute multiple times at the class level, typically
using the ``if`` condition to determine which target is appropriate based on the
source object's state or other logic::

    // src/Dto/EventInput.php
    namespace App\Dto;

    use App\Entity\OnlineEvent;
    use App\Entity\PhysicalEvent;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: OnlineEvent::class, if: [self::class, 'isOnline'])]
    #[Map(target: PhysicalEvent::class, if: [self::class, 'isPhysical'])]
    class EventInput
    {
        public string $type = 'online'; // e.g., 'online' or 'physical'
        public string $title = '';

        /**
         * In class-level conditions, $value is null.
         */
        public static function isOnline(?mixed $value, object $source): bool
        {
            return 'online' === $source->type;
        }

        public static function isPhysical(?mixed $value, object $source): bool
        {
            return 'physical' === $source->type;
        }
    }

    // consider that the src/Entity/OnlineEvent.php and PhysicalEvent.php
    // files exist and define the needed classes

    // usage:
    $eventInput = new EventInput();
    $eventInput->type = 'physical';
    $mapper = new ObjectMapper();
    $event = $mapper->map($eventInput); // automatically maps to PhysicalEvent

Mapping Based on Target Properties (Source Mapping)
---------------------------------------------------

Sometimes, it's more convenient to define how a target object should retrieve
its values from a source, especially when working with external data formats.
This is done using the ``source`` parameter in the ``#[Map]`` attribute on the
target class's properties.

Note that if both the ``source`` and the ``target`` classes define the ``#[Map]``
attribute, the ``source`` takes precedence.

Consider the following class that stores the data obtained from an external API
that uses snake_case property names::

    // src/Api/Payload.php
    namespace App\Api;

    class Payload
    {
        public string $product_name = '';
        public float $price_amount = 0.0;
    }

In your application, classes use camelCase for property names, so you can map
them as follows::

    // src/Entity/Product.php
    namespace App\Entity;

    use App\Api\Payload;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    // define that Product can be mapped from Payload
    #[Map(source: Payload::class)]
    class Product
    {
        // define where 'name' should get its value from in the Payload source
        #[Map(source: 'product_name')]
        public string $name = '';

        // define where 'price' should get its value from
        #[Map(source: 'price_amount')]
        public float $price = 0.0;
    }

Using it in practice::

    $payload = new Payload();
    $payload->product_name = 'Super Widget';
    $payload->price_amount = 123.45;

    $mapper = new ObjectMapper();
    // map from the payload to the Product class
    $product = $mapper->map($payload, Product::class);

    // $product->name = 'Super Widget'
    // $product->price = 123.45

When using source-based mapping, the ``ObjectMapper`` will automatically use the
target's ``#[Map(source: ...)]`` attributes if no mapping is defined on the
source class.

Handling Recursion
------------------

The ObjectMapper automatically detects and handles recursive relationships between
objects (e.g., a ``User`` has a ``manager`` which is another ``User``, who might
manage the first user). When it encounters previously mapped objects in the graph,
it reuses the corresponding target instances to prevent infinite loops::

    // src/Entity/User.php
    namespace App\Entity;

    use App\Dto\UserDto;
    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(target: UserDto::class)]
    class User
    {
        public string $name = '';
        public ?User $manager = null;
    }

The target DTO object defines the ``User`` class as its source and the
ObjectMapper component detects the cyclic reference::

    // src/Dto/UserDto.php
    namespace App\Dto;

    use Symfony\Component\ObjectMapper\Attribute\Map;

    #[Map(source: \App\Entity\User::class)] // can also define mapping here
    class UserDto
    {
        public string $name = '';
        public ?UserDto $manager = null;
    }

Using it in practice::

    $manager = new User();
    $manager->name = 'Alice';
    $employee = new User();
    $employee->name = 'Bob';
    $employee->manager = $manager;
    // manager's manager is the employee:
    $manager->manager = $employee;

    $mapper = new ObjectMapper();
    $employeeDto = $mapper->map($employee, UserDto::class);

    // recursion is handled correctly:
    // $employeeDto->name === 'Bob'
    // $employeeDto->manager->name === 'Alice'
    // $employeeDto->manager->manager === $employeeDto

.. _objectmapper-custom-mapping-logic:

Custom Mapping Logic
--------------------

For very complex mapping scenarios or if you prefer separating mapping rules from
your DTOs/Entities, you can implement a custom mapping strategy using the
:class:`Symfony\\Component\\ObjectMapper\\Metadata\\ObjectMapperMetadataFactoryInterface`.
This allows defining mapping rules within dedicated mapper services, similar
to the approach used by libraries like MapStruct in the Java ecosystem.

First, create your custom metadata factory. The following example reads mapping
rules defined via ``#[Map]`` attributes on a dedicated mapper service class,
specifically on its ``map`` method for property mappings and on the class itself
for the source-to-target relationship::

    namespace App\ObjectMapper\Metadata;

    use Symfony\Component\ObjectMapper\Attribute\Map;
    use Symfony\Component\ObjectMapper\Metadata\Mapping;
    use Symfony\Component\ObjectMapper\Metadata\ObjectMapperMetadataFactoryInterface;
    use Symfony\Component\ObjectMapper\ObjectMapperInterface;

    /**
     * A Metadata factory that implements basics similar to MapStruct.
     * Reads mapping configuration from attributes on a dedicated mapper service.
     */
    final class MapStructMapperMetadataFactory implements ObjectMapperMetadataFactoryInterface
    {
        /**
         * @param class-string<ObjectMapperInterface> $mapperClass The FQCN of the mapper service class
         */
        public function __construct(private readonly string $mapperClass)
        {
            if (!is_a($this->mapperClass, ObjectMapperInterface::class, true)) {
                throw new \RuntimeException(sprintf('Mapper class "%s" must implement "%s".', $this->mapperClass, ObjectMapperInterface::class));
            }
        }

        public function create(object $object, ?string $property = null, array $context = []): array
        {
            try {
                $refl = new \ReflectionClass($this->mapperClass);
            } catch (\ReflectionException $e) {
                throw new \RuntimeException("Failed to reflect mapper class: " . $e->getMessage(), 0, $e);
            }

            $mapConfigs = [];
            $sourceIdentifier = $property ?? $object::class;

            // read attributes from the map method (for property mapping) or the class (for class mapping)
            $attributesSource = $property ? $refl->getMethod('map') : $refl;
            foreach ($attributesSource->getAttributes(Map::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $map = $attribute->newInstance();

                // check if the attribute's source matches the current property or source class
                if ($map->source === $sourceIdentifier) {
                    $mapConfigs[] = new Mapping($map->target, $map->source, $map->if, $map->transform);
                }
            }

            // if it's a property lookup and no specific mapping was found, map to the same property
            if ($property && empty($mapConfigs)) {
                $mapConfigs[] = new Mapping(target: $property, source: $property);
            }

            return $mapConfigs;
        }
    }

Next, define your mapper service class. This class implements ``ObjectMapperInterface``
but typically delegates the actual mapping back to a standard ``ObjectMapper``
instance configured with the custom metadata factory. Mapping rules are defined
using ``#[Map]`` attributes on this class and its ``map`` method::

    namespace App\ObjectMapper;

    use App\Dto\LegacyUser;
    use App\Dto\UserDto;
    use App\ObjectMapper\Metadata\MapStructMapperMetadataFactory;
    use Symfony\Component\ObjectMapper\Attribute\Map;
    use Symfony\Component\ObjectMapper\ObjectMapper;
    use Symfony\Component\ObjectMapper\ObjectMapperInterface;

    // define the source-to-target mapping at the class level
    #[Map(source: LegacyUser::class, target: UserDto::class)]
    class LegacyUserMapper implements ObjectMapperInterface
    {
        private readonly ObjectMapperInterface $objectMapper;

        // inject the standard ObjectMapper or necessary dependencies
        public function __construct(?ObjectMapperInterface $objectMapper = null)
        {
            // create an ObjectMapper instance configured with *this* mapper's rules
            $metadataFactory = new MapStructMapperMetadataFactory(self::class);
            $this->objectMapper = $objectMapper ?? new ObjectMapper($metadataFactory);
        }

        // define property-specific mapping rules on the map method
        #[Map(source: 'fullName', target: 'name')] // Map LegacyUser::fullName to UserDto::name
        #[Map(source: 'creationTimestamp', target: 'registeredAt', transform: [\DateTimeImmutable::class, 'createFromFormat'])]
        #[Map(source: 'status', if: false)] // Ignore the 'status' property from LegacyUser
        public function map(object $source, object|string|null $target = null): object
        {
            // delegate the actual mapping to the configured ObjectMapper
            return $this->objectMapper->map($source, $target);
        }
    }

Finally, use your custom mapper service::

    use App\Dto\LegacyUser;
    use App\ObjectMapper\LegacyUserMapper;

    $legacyUser = new LegacyUser();
    $legacyUser->fullName = 'Jane Doe';
    $legacyUser->status = 'active'; // this will be ignored

    // instantiate your custom mapper service
    $mapperService = new LegacyUserMapper();

    // use the map method of your service
    $userDto = $mapperService->map($legacyUser); // Target (UserDto) is inferred from #[Map] on LegacyUserMapper

This approach keeps mapping logic centralized within dedicated services, which can
be beneficial for complex applications or when adhering to specific architectural patterns.

Advanced Configuration
----------------------

The ``ObjectMapper`` constructor accepts optional arguments for advanced usage:

* ``ObjectMapperMetadataFactoryInterface $metadataFactory``: Allows custom metadata
  factories, such as the one shown in :ref:`the MapStruct-like example <objectmapper-custom-mapping-logic>`.
  The default is :class:`Symfony\\Component\\ObjectMapper\\Metadata\\ReflectionObjectMapperMetadataFactory`,
  which uses ``#[Map]`` attributes from source and target classes.
* ``?PropertyAccessorInterface $propertyAccessor``: Lets you customize how
  properties are read and written to the target object, useful for accessing
  private properties or using getters/setters.
* ``?ContainerInterface $transformCallableLocator``: A PSR-11 container (service locator)
  that resolves service IDs referenced by the ``transform`` option in ``#[Map]``.
* ``?ContainerInterface $conditionCallableLocator``: A PSR-11 container for resolving
  service IDs used in ``if`` conditions within ``#[Map]``.

These dependencies are automatically configured when you use the
``ObjectMapperInterface`` service provided by Symfony.
