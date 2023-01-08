.. index::
   single: UID
   single: Components; UID

The UID Component
=================

    The UID component provides utilities to work with `unique identifiers`_ (UIDs)
    such as UUIDs and ULIDs.

.. versionadded:: 5.1

    The UID component was introduced in Symfony 5.1.

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
to create each type of UUID::

    use Symfony\Component\Uid\Uuid;

    // UUID type 1 generates the UUID using the MAC address of your device and a timestamp.
    // Both are obtained automatically, so you don't have to pass any constructor argument.
    $uuid = Uuid::v1(); // $uuid is an instance of Symfony\Component\Uid\UuidV1

    // UUID type 4 generates a random UUID, so you don't have to pass any constructor argument.
    $uuid = Uuid::v4(); // $uuid is an instance of Symfony\Component\Uid\UuidV4

    // UUID type 3 and 5 generate a UUID hashing the given namespace and name. Type 3 uses
    // MD5 hashes and Type 5 uses SHA-1. The namespace is another UUID (e.g. a Type 4 UUID)
    // and the name is an arbitrary string (e.g. a product name; if it's unique).
    $namespace = Uuid::v4();
    $name = $product->getUniqueName();

    $uuid = Uuid::v3($namespace, $name); // $uuid is an instance of Symfony\Component\Uid\UuidV3
    $uuid = Uuid::v5($namespace, $name); // $uuid is an instance of Symfony\Component\Uid\UuidV5

    // the namespaces defined by RFC 4122 (see https://tools.ietf.org/html/rfc4122#appendix-C)
    // are available as PHP constants and as string values
    $uuid = Uuid::v3(Uuid::NAMESPACE_DNS, $name);  // same as: Uuid::v3('dns', $name);
    $uuid = Uuid::v3(Uuid::NAMESPACE_URL, $name);  // same as: Uuid::v3('url', $name);
    $uuid = Uuid::v3(Uuid::NAMESPACE_OID, $name);  // same as: Uuid::v3('oid', $name);
    $uuid = Uuid::v3(Uuid::NAMESPACE_X500, $name); // same as: Uuid::v3('x500', $name);

    // UUID type 6 is not part of the UUID standard. It's lexicographically sortable
    // (like ULIDs) and contains a 60-bit timestamp and 63 extra unique bits.
    // It's defined in http://gh.peabody.io/uuidv6/
    $uuid = Uuid::v6(); // $uuid is an instance of Symfony\Component\Uid\UuidV6

.. versionadded:: 5.3

    The ``Uuid::NAMESPACE_*`` constants and the namespace string values (``'dns'``,
    ``'url'``, etc.) were introduced in Symfony 5.3.

If your UUID value is already generated in another format, use any of the
following methods to create a ``Uuid`` object from it::

    // all the following examples would generate the same Uuid object
    $uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');
    $uuid = Uuid::fromBinary("\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0");
    $uuid = Uuid::fromBase32('6SWYGR8QAV27NACAHMK5RG0RPG');
    $uuid = Uuid::fromBase58('TuetYWNHhmuSQ3xPoVLv9M');
    $uuid = Uuid::fromRfc4122('d9e7a184-5d5b-11ea-a62a-3499710062d0');

.. versionadded:: 5.3

    The ``fromBinary()``, ``fromBase32()``, ``fromBase58()`` and ``fromRfc4122()``
    methods were introduced in Symfony 5.3.

You can also use the ``UuidFactory`` to generate UUIDs. First, you may
configure the behavior of the factory using configuration files::

.. configuration-block::

    .. code-block:: yaml

        # config/packages/uid.yaml
        framework:
            uid:
                default_uuid_version: 6
                name_based_uuid_version: 5
                name_based_uuid_namespace: ~
                time_based_uuid_version: 6
                time_based_uuid_node: ~

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
                    default_uuid_version="6"
                    name_based_uuid_version="5"
                    name_based_uuid_namespace=""
                    time_based_uuid_version="6"
                    time_based_uuid_node=""
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/uid.php
        <?php

        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $configurator): void {
            $services = $configurator->services()
                ->defaults()
                ->autowire()
                ->autoconfigure();

            $configurator->extension('framework', [
                'uid' => [
                    'default_uuid_version' => 6,
                    'name_based_uuid_version' => 5,
                    'name_based_uuid_namespace' => '',
                    'time_based_uuid_version' => 6,
                    'time_based_uuid_node' => '',
                ],
            ]);
        };

Then, you can inject the factory in your services and use it to generate UUIDs based
on the configuration you defined::

    <?php

    namespace App\Service;

    use Symfony\Component\Uid\Factory\UuidFactory;

    class FooService
    {
        private UuidFactory $uuidFactory;

        public function __construct(UuidFactory $uuidFactory)
        {
            $this->uuidFactory = $uuidFactory;
        }

        public function generate(): void
        {
            // This creates a UUID of the version given in the configuration file (v6 by default)
            $uuid = $this->uuidFactory->create();

            $nameBasedUuid = $this->uuidFactory->nameBased(/** ... */);
            $randomBasedUuid = $this->uuidFactory->randomBased();
            $timestampBased = $this->uuidFactory->timeBased();

            // ...
        }
    }

.. versionadded:: 5.3

    The ``UuidFactory`` was introduced in Symfony 5.3.

Converting UUIDs
~~~~~~~~~~~~~~~~

Use these methods to transform the UUID object into different bases::

    $uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');

    $uuid->toBinary();  // string(16) "\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0"
    $uuid->toBase32();  // string(26) "6SWYGR8QAV27NACAHMK5RG0RPG"
    $uuid->toBase58();  // string(22) "TuetYWNHhmuSQ3xPoVLv9M"
    $uuid->toRfc4122(); // string(36) "d9e7a184-5d5b-11ea-a62a-3499710062d0"

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

.. versionadded:: 5.3

    The ``getDateTime()`` method was introduced in Symfony 5.3. In previous
    versions it was called ``getTime()``.

Storing UUIDs in Databases
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you :doc:`use Doctrine </doctrine>`, consider using the ``uuid`` Doctrine
type, which converts to/from UUID objects automatically::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
     */
    class Product
    {
        /**
         * @ORM\Column(type="uuid")
         */
        private $someProperty;

        // ...
    }

.. versionadded:: 5.2

    The UUID type was introduced in Symfony 5.2.

There's also a Doctrine generator to help auto-generate UUID values for the
entity primary keys::

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Uid\Uuid;

    class User implements UserInterface
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="uuid", unique=true)
         * @ORM\GeneratedValue(strategy="CUSTOM")
         * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
         */
        private $id;

        public function getId(): ?Uuid
        {
            return $this->id;
        }

        // ...
    }

When using built-in Doctrine repository methods (e.g. ``findOneBy()``), Doctrine
knows how to convert these UUID types to build the SQL query
(e.g. ``->findOneBy(['user' => $user->getUuid()])``). However, when using DQL
queries or building the query yourself, you'll need to set ``uuid`` as the type
of the UUID parameters::

    // src/Repository/ProductRepository.php

    // ...
    class ProductRepository extends ServiceEntityRepository
    {
        // ...

        public function findUserProducts(User $user): array
        {
            $qb = $this->createQueryBuilder('p')
                // ...
                // add 'uuid' as the third argument to tell Doctrine that this is a UUID
                ->setParameter('user', $user->getUuid(), 'uuid')

                // alternatively, you can convert it to a value compatible with
                // the type inferred by Doctrine
                ->setParameter('user', $user->getUuid()->toBinary())
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

.. versionadded:: 5.3

    The ``fromBinary()``, ``fromBase32()``, ``fromBase58()`` and ``fromRfc4122()``
    methods were introduced in Symfony 5.3.

Like UUIDs, ULIDs have their own factory, ``UlidFactory``, that can be used to generate them::

    <?php

    namespace App\Service;

    use Symfony\Component\Uid\Factory\UlidFactory;

    class FooService
    {
        private UlidFactory $ulidFactory;

        public function __construct(UlidFactory $ulidFactory)
        {
            $this->ulidFactory = $ulidFactory;
        }

        public function generate(): void
        {
            $ulid = $this->ulidFactory->create();

            // ...
        }
    }

.. versionadded:: 5.3

    The ``UlidFactory`` was introduced in Symfony 5.3.

There's also a special ``NilUlid`` class to represent ULID ``null`` values::

    use Symfony\Component\Uid\NilUlid;

    $ulid = new NilUlid();
    // equivalent to $ulid = new Ulid('00000000000000000000000000');

.. versionadded:: 5.4

    The ``NilUlid`` class was introduced in Symfony 5.4.

Converting ULIDs
~~~~~~~~~~~~~~~~

Use these methods to transform the ULID object into different bases::

    $ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');

    $ulid->toBinary();  // string(16) "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
    $ulid->toBase32();  // string(26) "01E439TP9XJZ9RPFH3T1PYBCR8"
    $ulid->toBase58();  // string(22) "1BKocMc5BnrVcuq2ti4Eqm"
    $ulid->toRfc4122(); // string(36) "0171069d-593d-97d3-8b3e-23d06de5b308"

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

.. versionadded:: 5.3

    The ``getDateTime()`` method was introduced in Symfony 5.3. In previous
    versions it was called ``getTime()``.

Storing ULIDs in Databases
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you :doc:`use Doctrine </doctrine>`, consider using the ``ulid`` Doctrine
type, which converts to/from ULID objects automatically::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
     */
    class Product
    {
        /**
         * @ORM\Column(type="ulid")
         */
        private $someProperty;

        // ...
    }

There's also a Doctrine generator to help auto-generate ULID values for the
entity primary keys::

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Uid\Ulid;

    class Product
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="ulid", unique=true)
         * @ORM\GeneratedValue(strategy="CUSTOM")
         * @ORM\CustomIdGenerator(class="doctrine.ulid_generator")
         */
        private $id;

        public function getId(): ?Ulid
        {
            return $this->id;
        }

        // ...

    }

.. versionadded:: 5.2

    The ULID type and generator were introduced in Symfony 5.2.

When using built-in Doctrine repository methods (e.g. ``findOneBy()``), Doctrine
knows how to convert these ULID types to build the SQL query
(e.g. ``->findOneBy(['user' => $user->getUlid()])``). However, when using DQL
queries or building the query yourself, you'll need to set ``ulid`` as the type
of the ULID parameters::

    // src/Repository/ProductRepository.php

    // ...
    class ProductRepository extends ServiceEntityRepository
    {
        // ...

        public function findUserProducts(User $user): array
        {
            $qb = $this->createQueryBuilder('p')
                // ...
                // add 'ulid' as the third argument to tell Doctrine that this is a ULID
                ->setParameter('user', $user->getUlid(), 'ulid')

                // alternatively, you can convert it to a value compatible with
                // the type inferred by Doctrine
                ->setParameter('user', $user->getUlid()->toBinary())
            ;

            // ...
        }
    }

Generating and Inspecting UUIDs/ULIDs in the Console
----------------------------------------------------

.. versionadded:: 5.3

    The commands to inspect and generate UUIDs/ULIDs were introduced in Symfony 5.3.

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

        return static function (ContainerConfigurator $configurator): void {
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

    # generate 2 ULIDs and ouput them in RFC4122 format
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
