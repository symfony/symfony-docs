The UID Component
=================

    The UID component provides utilities to work with `unique identifiers`_ (UIDs)
    such as UUIDs and ULIDs.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/uid

.. include:: /components/require_autoload.rst.inc

.. _uuid:

UUIDs
-----

`UUIDs`_ (*universally unique identifiers*) are one of the most popular UIDs in
the software industry. UUIDs are 128-bit numbers usually represented as five
groups of hexadecimal characters: ``xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx``
(the ``M`` digit is the UUID version and the ``N`` digit is the UUID variant).

Generating UUIDs
~~~~~~~~~~~~~~~~

Use the named constructors of the ``Uuid`` class or any of the specific classes
to create each type of UUID:

**UUID v1** (time-based)

Generates the UUID using a timestamp and the MAC address of your device
(`read UUIDv1 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-1>`__).
Both are obtained automatically, so you don't have to pass any constructor argument::

    use Symfony\Component\Uid\Uuid;

    // $uuid is an instance of Symfony\Component\Uid\UuidV1
    $uuid = Uuid::v1();

.. tip::

    It's recommended to use UUIDv7 instead of UUIDv1 because it provides
    better entropy.

**UUID v2** (DCE security)

Similar to UUIDv1 but with a very high likelihood of ID collision
(`read UUIDv2 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-2>`__).
It's part of the authentication mechanism of DCE (Distributed Computing Environment)
and the UUID includes the POSIX UIDs (user/group ID) of the user who generated it.
This UUID variant is **not implemented** by the Uid component.

**UUID v3** (name-based, MD5)

Generates UUIDs from names that belong, and are unique within, some given namespace
(`read UUIDv3 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-3>`__).
This variant is useful to generate deterministic UUIDs from arbitrary strings.
It works by populating the UUID contents with the``md5`` hash of concatenating
the namespace and the name::

    use Symfony\Component\Uid\Uuid;

    // you can use any of the predefined namespaces...
    $namespace = Uuid::fromString(Uuid::NAMESPACE_OID);
    // ...or use a random namespace:
    // $namespace = Uuid::v4();

    // $name can be any arbitrary string
    // $uuid is an instance of Symfony\Component\Uid\UuidV3
    $uuid = Uuid::v3($namespace, $name);

These are the default namespaces defined by the standard:

* ``Uuid::NAMESPACE_DNS`` if you are generating UUIDs for `DNS entries <https://en.wikipedia.org/wiki/Domain_Name_System>`__
* ``Uuid::NAMESPACE_URL`` if you are generating UUIDs for `URLs <https://en.wikipedia.org/wiki/URL>`__
* ``Uuid::NAMESPACE_OID`` if you are generating UUIDs for `OIDs (object identifiers) <https://en.wikipedia.org/wiki/Object_identifier>`__
* ``Uuid::NAMESPACE_X500`` if you are generating UUIDs for `X500 DNs (distinguished names) <https://en.wikipedia.org/wiki/X.500>`__

**UUID v4** (random)

Generates a random UUID (`read UUIDv4 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-4>`__).
Because of its randomness, it ensures uniqueness across distributed systems
without the need for a central coordinating entity. It's privacy-friendly
because it doesn't contain any information about where and when it was generated::

    use Symfony\Component\Uid\Uuid;

    // $uuid is an instance of Symfony\Component\Uid\UuidV4
    $uuid = Uuid::v4();

**UUID v5** (name-based, SHA-1)

It's the same as UUIDv3 (explained above) but it uses ``sha1`` instead of
``md5`` to hash the given namespace and name (`read UUIDv5 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-5>`__).
This makes it more secure and less prone to hash collisions.

.. _uid-uuid-v6:

**UUID v6** (reordered time-based)

It rearranges the time-based fields of the UUIDv1 to make it lexicographically
sortable (like :ref:`ULIDs <ulid>`). It's more efficient for database indexing
(`read UUIDv6 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-6>`__)::

    use Symfony\Component\Uid\Uuid;

    // $uuid is an instance of Symfony\Component\Uid\UuidV6
    $uuid = Uuid::v6();

.. tip::

    It's recommended to use UUIDv7 instead of UUIDv6 because it provides
    better entropy.

.. _uid-uuid-v7:

**UUID v7** (UNIX timestamp)

Generates time-ordered UUIDs based on a high-resolution Unix Epoch timestamp
source (the number of milliseconds since midnight 1 Jan 1970 UTC, leap seconds excluded)
(`read UUIDv7 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-7>`__).
It's recommended to use this version over UUIDv1 and UUIDv6 because it provides
better entropy (and a more strict chronological order of UUID generation)::

    use Symfony\Component\Uid\Uuid;

    // $uuid is an instance of Symfony\Component\Uid\UuidV7
    $uuid = Uuid::v7();

**UUID v8** (custom)

Provides an RFC-compatible format for experimental or vendor-specific use cases
(`read UUIDv8 spec <https://datatracker.ietf.org/doc/html/draft-ietf-uuidrev-rfc4122bis#name-uuid-version-8>`__).
The only requirement is to set the variant and version bits of the UUID. The rest
of the UUID value is specific to each implementation and no format should be assumed::

    use Symfony\Component\Uid\Uuid;

    // $uuid is an instance of Symfony\Component\Uid\UuidV8
    $uuid = Uuid::v8();

If your UUID value is already generated in another format, use any of the
following methods to create a ``Uuid`` object from it::

    // all the following examples would generate the same Uuid object
    $uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');
    $uuid = Uuid::fromBinary("\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0");
    $uuid = Uuid::fromBase32('6SWYGR8QAV27NACAHMK5RG0RPG');
    $uuid = Uuid::fromBase58('TuetYWNHhmuSQ3xPoVLv9M');
    $uuid = Uuid::fromRfc4122('d9e7a184-5d5b-11ea-a62a-3499710062d0');

You can also use the ``UuidFactory`` to generate UUIDs. First, you may
configure the behavior of the factory using configuration files::

.. configuration-block::

    .. code-block:: yaml

        # config/packages/uid.yaml
        framework:
            uid:
                default_uuid_version: 7
                name_based_uuid_version: 5
                name_based_uuid_namespace: 6ba7b810-9dad-11d1-80b4-00c04fd430c8
                time_based_uuid_version: 7
                time_based_uuid_node: 121212121212

    .. code-block:: xml

        <!-- config/packages/uid.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:framework="http://symfony.com/schema/dic/symfony"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                        https://symfony.com/schema/dic/services/services-1.0.xsd
                        http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:uid
                    default_uuid_version="7"
                    name_based_uuid_version="5"
                    name_based_uuid_namespace="6ba7b810-9dad-11d1-80b4-00c04fd430c8"
                    time_based_uuid_version="7"
                    time_based_uuid_node="121212121212"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/uid.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                ->autowire()
                ->autoconfigure();

            $container->extension('framework', [
                'uid' => [
                    'default_uuid_version' => 7,
                    'name_based_uuid_version' => 5,
                    'name_based_uuid_namespace' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
                    'time_based_uuid_version' => 7,
                    'time_based_uuid_node' => 121212121212,
                ],
            ]);
        };

Then, you can inject the factory in your services and use it to generate UUIDs based
on the configuration you defined::

    namespace App\Service;

    use Symfony\Component\Uid\Factory\UuidFactory;

    class FooService
    {
        public function __construct(
            private UuidFactory $uuidFactory,
        ) {
        }

        public function generate(): void
        {
            // This creates a UUID of the version given in the configuration file (v7 by default)
            $uuid = $this->uuidFactory->create();

            $nameBasedUuid = $this->uuidFactory->nameBased(/** ... */);
            $randomBasedUuid = $this->uuidFactory->randomBased();
            $timestampBased = $this->uuidFactory->timeBased();

            // ...
        }
    }

Converting UUIDs
~~~~~~~~~~~~~~~~

Use these methods to transform the UUID object into different bases::

    $uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');

    $uuid->toBinary();  // string(16) "\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0"
    $uuid->toBase32();  // string(26) "6SWYGR8QAV27NACAHMK5RG0RPG"
    $uuid->toBase58();  // string(22) "TuetYWNHhmuSQ3xPoVLv9M"
    $uuid->toRfc4122(); // string(36) "d9e7a184-5d5b-11ea-a62a-3499710062d0"
    $uuid->toHex();     // string(34) "0xd9e7a1845d5b11eaa62a3499710062d0"
    $uuid->toString();  // string(36) "d9e7a184-5d5b-11ea-a62a-3499710062d0"

.. versionadded:: 7.1

    The ``toString()`` method was introduced in Symfony 7.1.

You can also convert some UUID versions to others::

    // convert V1 to V6 or V7
    $uuid = Uuid::v1();

    $uuid->toV6(); // returns a Symfony\Component\Uid\UuidV6 instance
    $uuid->toV7(); // returns a Symfony\Component\Uid\UuidV7 instance

    // convert V6 to V7
    $uuid = Uuid::v6();

    $uuid->toV7(); // returns a Symfony\Component\Uid\UuidV7 instance

.. versionadded:: 7.1

    The :method:`Symfony\\Component\\Uid\\UuidV1::toV6`,
    :method:`Symfony\\Component\\Uid\\UuidV1::toV7` and
    :method:`Symfony\\Component\\Uid\\UuidV6::toV7`
    methods were introduced in Symfony 7.1.

Working with UUIDs
~~~~~~~~~~~~~~~~~~

UUID objects created with the ``Uuid`` class can use the following methods
(which are equivalent to the ``uuid_*()`` method of the PHP extension)::

    use Symfony\Component\Uid\NilUuid;
    use Symfony\Component\Uid\Uuid;

    // checking if the UUID is null (note that the class is called
    // NilUuid instead of NullUuid to follow the UUID standard notation)
    $uuid = Uuid::v4();
    $uuid instanceof NilUuid; // false

    // checking the type of UUID
    use Symfony\Component\Uid\UuidV4;
    $uuid = Uuid::v4();
    $uuid instanceof UuidV4; // true

    // getting the UUID datetime (it's only available in certain UUID types)
    $uuid = Uuid::v1();
    $uuid->getDateTime(); // returns a \DateTimeImmutable instance

    // checking if a given value is valid as UUID
    $isValid = Uuid::isValid($uuid); // true or false

    // comparing UUIDs and checking for equality
    $uuid1 = Uuid::v1();
    $uuid4 = Uuid::v4();
    $uuid1->equals($uuid4); // false

    // this method returns:
    //   * int(0) if $uuid1 and $uuid4 are equal
    //   * int > 0 if $uuid1 is greater than $uuid4
    //   * int < 0 if $uuid1 is less than $uuid4
    $uuid1->compare($uuid4); // e.g. int(4)

Storing UUIDs in Databases
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you :doc:`use Doctrine </doctrine>`, consider using the ``uuid`` Doctrine
type, which converts to/from UUID objects automatically::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Types\UuidType;
    use Symfony\Component\Uid\Uuid;

    #[ORM\Entity(repositoryClass: ProductRepository::class)]
    class Product
    {
        #[ORM\Column(type: UuidType::NAME)]
        private Uuid $someProperty;

        // ...
    }

There's also a Doctrine generator to help auto-generate UUID values for the
entity primary keys::

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Types\UuidType;
    use Symfony\Component\Uid\Uuid;

    class User implements UserInterface
    {
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        #[ORM\GeneratedValue(strategy: 'CUSTOM')]
        #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
        private ?Uuid $id;

        public function getId(): ?Uuid
        {
            return $this->id;
        }

        // ...
    }

.. caution::

    Using UUIDs as primary keys is usually not recommended for performance reasons:
    indexes are slower and take more space (because UUIDs in binary format take
    128 bits instead of 32/64 bits for auto-incremental integers) and the non-sequential
    nature of UUIDs fragments indexes. :ref:`UUID v6 <uid-uuid-v6>` and :ref:`UUID v7 <uid-uuid-v7>`
    are the only variants that solve the fragmentation issue (but the index size issue remains).

When using built-in Doctrine repository methods (e.g. ``findOneBy()``), Doctrine
knows how to convert these UUID types to build the SQL query
(e.g. ``->findOneBy(['user' => $user->getUuid()])``). However, when using DQL
queries or building the query yourself, you'll need to set ``uuid`` as the type
of the UUID parameters::

    // src/Repository/ProductRepository.php

    // ...
    use Doctrine\DBAL\ParameterType;
    use Symfony\Bridge\Doctrine\Types\UuidType;

    class ProductRepository extends ServiceEntityRepository
    {
        // ...

        public function findUserProducts(User $user): array
        {
            $qb = $this->createQueryBuilder('p')
                // ...
                // add UuidType::NAME as the third argument to tell Doctrine that this is a UUID
                ->setParameter('user', $user->getUuid(), UuidType::NAME)

                // alternatively, you can convert it to a value compatible with
                // the type inferred by Doctrine
                ->setParameter('user', $user->getUuid()->toBinary(), ParameterType::BINARY)
            ;

            // ...
        }
    }

.. _ulid:

ULIDs
-----

`ULIDs`_ (*Universally Unique Lexicographically Sortable Identifier*) are 128-bit
numbers usually represented as a 26-character string: ``TTTTTTTTTTRRRRRRRRRRRRRRRR``
(where ``T`` represents a timestamp and ``R`` represents the random bits).

ULIDs are an alternative to UUIDs when using those is impractical. They provide
128-bit compatibility with UUID, they are lexicographically sortable and they
are encoded as 26-character strings (vs 36-character UUIDs).

.. note::

    If you generate more than one ULID during the same millisecond in the
    same process then the random portion is incremented by one bit in order
    to provide monotonicity for sorting. The random portion is not random
    compared to the previous ULID in this case.

Generating ULIDs
~~~~~~~~~~~~~~~~

Instantiate the ``Ulid`` class to generate a random ULID value::

    use Symfony\Component\Uid\Ulid;

    $ulid = new Ulid();  // e.g. 01AN4Z07BY79KA1307SR9X4MV3

If your ULID value is already generated in another format, use any of the
following methods to create a ``Ulid`` object from it::

    // all the following examples would generate the same Ulid object
    $ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');
    $ulid = Ulid::fromBinary("\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08");
    $ulid = Ulid::fromBase32('01E439TP9XJZ9RPFH3T1PYBCR8');
    $ulid = Ulid::fromBase58('1BKocMc5BnrVcuq2ti4Eqm');
    $ulid = Ulid::fromRfc4122('0171069d-593d-97d3-8b3e-23d06de5b308');

Like UUIDs, ULIDs have their own factory, ``UlidFactory``, that can be used to generate them::

    namespace App\Service;

    use Symfony\Component\Uid\Factory\UlidFactory;

    class FooService
    {
        public function __construct(
            private UlidFactory $ulidFactory,
        ) {
        }

        public function generate(): void
        {
            $ulid = $this->ulidFactory->create();

            // ...
        }
    }

There's also a special ``NilUlid`` class to represent ULID ``null`` values::

    use Symfony\Component\Uid\NilUlid;

    $ulid = new NilUlid();
    // equivalent to $ulid = new Ulid('00000000000000000000000000');

Converting ULIDs
~~~~~~~~~~~~~~~~

Use these methods to transform the ULID object into different bases::

    $ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');

    $ulid->toBinary();  // string(16) "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
    $ulid->toBase32();  // string(26) "01E439TP9XJZ9RPFH3T1PYBCR8"
    $ulid->toBase58();  // string(22) "1BKocMc5BnrVcuq2ti4Eqm"
    $ulid->toRfc4122(); // string(36) "0171069d-593d-97d3-8b3e-23d06de5b308"
    $ulid->toHex();     // string(34) "0x0171069d593d97d38b3e23d06de5b308"

Working with ULIDs
~~~~~~~~~~~~~~~~~~

ULID objects created with the ``Ulid`` class can use the following methods::

    use Symfony\Component\Uid\Ulid;

    $ulid1 = new Ulid();
    $ulid2 = new Ulid();

    // checking if a given value is valid as ULID
    $isValid = Ulid::isValid($ulidValue); // true or false

    // getting the ULID datetime
    $ulid1->getDateTime(); // returns a \DateTimeImmutable instance

    // comparing ULIDs and checking for equality
    $ulid1->equals($ulid2); // false
    // this method returns $ulid1 <=> $ulid2
    $ulid1->compare($ulid2); // e.g. int(-1)

Storing ULIDs in Databases
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you :doc:`use Doctrine </doctrine>`, consider using the ``ulid`` Doctrine
type, which converts to/from ULID objects automatically::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Types\UlidType;
    use Symfony\Component\Uid\Ulid;

    #[ORM\Entity(repositoryClass: ProductRepository::class)]
    class Product
    {
        #[ORM\Column(type: UlidType::NAME)]
        private Ulid $someProperty;

        // ...
    }

There's also a Doctrine generator to help auto-generate ULID values for the
entity primary keys::

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Types\UlidType;
    use Symfony\Component\Uid\Ulid;

    class Product
    {
        #[ORM\Id]
        #[ORM\Column(type: UlidType::NAME, unique: true)]
        #[ORM\GeneratedValue(strategy: 'CUSTOM')]
        #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
        private ?Ulid $id;

        public function getId(): ?Ulid
        {
            return $this->id;
        }

        // ...
    }

.. caution::

    Using ULIDs as primary keys is usually not recommended for performance reasons.
    Although ULIDs don't suffer from index fragmentation issues (because the values
    are sequential), their indexes are slower and take more space (because ULIDs
    in binary format take 128 bits instead of 32/64 bits for auto-incremental integers).

When using built-in Doctrine repository methods (e.g. ``findOneBy()``), Doctrine
knows how to convert these ULID types to build the SQL query
(e.g. ``->findOneBy(['user' => $user->getUlid()])``). However, when using DQL
queries or building the query yourself, you'll need to set ``ulid`` as the type
of the ULID parameters::

    // src/Repository/ProductRepository.php

    // ...
    use Symfony\Bridge\Doctrine\Types\UlidType;

    class ProductRepository extends ServiceEntityRepository
    {
        // ...

        public function findUserProducts(User $user): array
        {
            $qb = $this->createQueryBuilder('p')
                // ...
                // add UlidType::NAME as the third argument to tell Doctrine that this is a ULID
                ->setParameter('user', $user->getUlid(), UlidType::NAME)

                // alternatively, you can convert it to a value compatible with
                // the type inferred by Doctrine
                ->setParameter('user', $user->getUlid()->toBinary())
            ;

            // ...
        }
    }

Generating and Inspecting UUIDs/ULIDs in the Console
----------------------------------------------------

This component provides several commands to generate and inspect UUIDs/ULIDs in
the console. They are not enabled by default, so you must add the following
configuration in your application before using these commands:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Component\Uid\Command\GenerateUlidCommand: ~
            Symfony\Component\Uid\Command\GenerateUuidCommand: ~
            Symfony\Component\Uid\Command\InspectUlidCommand: ~
            Symfony\Component\Uid\Command\InspectUuidCommand: ~

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="Symfony\Component\Uid\Command\GenerateUlidCommand"/>
                <service id="Symfony\Component\Uid\Command\GenerateUuidCommand"/>
                <service id="Symfony\Component\Uid\Command\InspectUlidCommand"/>
                <service id="Symfony\Component\Uid\Command\InspectUuidCommand"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Uid\Command\GenerateUlidCommand;
        use Symfony\Component\Uid\Command\GenerateUuidCommand;
        use Symfony\Component\Uid\Command\InspectUlidCommand;
        use Symfony\Component\Uid\Command\InspectUuidCommand;

        return static function (ContainerConfigurator $container): void {
            // ...

            $services
                ->set(GenerateUlidCommand::class)
                ->set(GenerateUuidCommand::class)
                ->set(InspectUlidCommand::class)
                ->set(InspectUuidCommand::class);
        };

Now you can generate UUIDs/ULIDs as follows (add the ``--help`` option to the
commands to learn about all their options):

.. code-block:: terminal

    # generate 1 random-based UUID
    $ php bin/console uuid:generate --random-based

    # generate 1 time-based UUID with a specific node
    $ php bin/console uuid:generate --time-based=now --node=fb3502dc-137e-4849-8886-ac90d07f64a7

    # generate 2 UUIDs and output them in base58 format
    $ php bin/console uuid:generate --count=2 --format=base58

    # generate 1 ULID with the current time as the timestamp
    $ php bin/console ulid:generate

    # generate 1 ULID with a specific timestamp
    $ php bin/console ulid:generate --time="2021-02-02 14:00:00"

    # generate 2 ULIDs and output them in RFC4122 format
    $ php bin/console ulid:generate --count=2 --format=rfc4122

In addition to generating new UIDs, you can also inspect them with the following
commands to show all the information for a given UID:

.. code-block:: terminal

    $ php bin/console uuid:inspect d0a3a023-f515-4fe0-915c-575e63693998
     ---------------------- --------------------------------------
      Label                  Value
     ---------------------- --------------------------------------
      Version                4
      Canonical (RFC 4122)   d0a3a023-f515-4fe0-915c-575e63693998
      Base 58                SmHvuofV4GCF7QW543rDD9
      Base 32                6GMEG27X8N9ZG92Q2QBSHPJECR
     ---------------------- --------------------------------------

    $ php bin/console ulid:inspect 01F2TTCSYK1PDRH73Z41BN1C4X
     --------------------- --------------------------------------
      Label                 Value
     --------------------- --------------------------------------
      Canonical (Base 32)   01F2TTCSYK1PDRH73Z41BN1C4X
      Base 58               1BYGm16jS4kX3VYCysKKq6
      RFC 4122              0178b5a6-67d3-0d9b-889c-7f205750b09d
     --------------------- --------------------------------------
      Timestamp             2021-04-09 08:01:24.947
     --------------------- --------------------------------------

.. _`unique identifiers`: https://en.wikipedia.org/wiki/UID
.. _`UUIDs`: https://en.wikipedia.org/wiki/Universally_unique_identifier
.. _`ULIDs`: https://github.com/ulid/spec
