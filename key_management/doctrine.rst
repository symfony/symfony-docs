.. _key-management-doctrine:

Encrypting Doctrine Columns
===========================

With ``symfony/doctrine-orm-key-management`` installed, you can encrypt a
Doctrine column without writing any code. Mark the property and name the
key it is encrypted under::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\KeyManagement\Bridge\DoctrineOrm\Attribute\Encrypted;

    #[ORM\Entity]
    class User
    {
        #[ORM\Column]
        #[Encrypted(key: 'user.email')]
        private string $email = '';

        #[ORM\Column(type: 'date_immutable', nullable: true)]
        #[Encrypted(key: 'user.born_on')]
        private ?\DateTimeImmutable $bornOn = null;
    }

Nothing else is needed. The bundle collects those columns while the container is
built, and each Doctrine connection registers the types they need when the
connection is created. ``key`` is the master key encrypting the values, or the
:ref:`scope <key-management-store-config>` of their data key when a store is
configured.

When ``key`` is set, the bundle looks for these attributes in the entities,
embeddables and mapped superclasses of the directories that your services are
loaded from. With the default ``App\`` resource, this includes ``src/Entity/``.

The column keeps the type its mapping gives it, declared on
``#[ORM\Column]`` or guessed from the type of the property. The value goes
through that type first and is encrypted afterwards, so a date is stored as
an encrypted date, not as an encrypted string.

.. include:: /key_management/_experimental.rst.inc

.. note::

    The attribute requires a ``store`` or a default client: the one that
    ``default_client`` names, or the only client configured.

An encrypted column cannot be searched. See
:doc:`/key_management/searching` for how to find a row by a value the
database holds only as ciphertext.

Omitting the Key
----------------

When a ``store`` is configured, ``key`` can be left out. Each column then gets
its own scope, derived from the names of the schema, the table and the column
that hold the values::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\KeyManagement\Bridge\DoctrineOrm\Attribute\Encrypted;

    #[ORM\Entity]
    class User
    {
        #[ORM\Column]
        #[Encrypted]
        private string $email = '';
    }

The scope follows the physical names that Doctrine resolves, naming strategies,
embedded prefixes and inherited columns included. Renaming the entity or the
property keeps existing values readable only while the table and the column
keep their names, for instance through the ``name`` option of ``#[ORM\Table]``
and ``#[ORM\Column]``. Renaming the table or the column changes the scope.
Before such a rename, set ``key`` to the scope that was
generated for the old names. That scope is
``'orm:'.hash('sha256', serialize([$schema, $table, $column]))``, where
``$schema`` is ``null`` for a table without a schema.

The scope doesn't contain the database, the connection or the tenant. Columns
with the same names in two databases sharing one store therefore share their
data keys, and a value copied from one to the other still decrypts. Give them
explicit keys when that matters.

The first time an entity manager is created, the bundle finds the types of these
columns, which loads the metadata of every mapped class. It then keeps them in
the ``cache.key_management.encrypted_columns`` pool, a child of
``cache.system``, so the next entity managers load no metadata. In debug mode,
the types are found again for each entity manager, so that a change to the
mapping is seen at once.

The bundle handles the columns that omit their key only when it finds at least
one of them in the directories your services are loaded from. Otherwise,
loading such a column fails as if no store was configured.

Declaring the Types Yourself
----------------------------

The attribute is a shortcut over named Doctrine types, which you can also
declare directly. Use this when a column is mapped in XML or PHP rather
than by attribute, or when you want to pick the type name.

You also need it when the bundle misses an ``#[Encrypted]`` column with a
``key``, because its entity is mapped in XML, or is outside or excluded from the
directories that your services are loaded from. The same happens when a custom
typed field mapper makes the ORM use another type than the one guessed by the
bundle. Loading the metadata of such an entity then fails, ``cache:warmup``
included. Declare the types of its columns and the attribute reuses them:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            clients:
                app: '%env(KMS_DSN)%'
            store:
                key_id: 'alias/app-key'
            doctrine_dbal:
                types:
                    app_user_email:
                        type: string
                        key: 'user.email'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'clients' => [
                    'app' => env('KMS_DSN'),
                ],
                'store' => [
                    'key_id' => 'alias/app-key',
                ],
                'doctrine_dbal' => [
                    'types' => [
                        'app_user_email' => [
                            'type' => 'string',
                            'key' => 'user.email',
                        ],
                    ],
                ],
            ],
        ]);

Like the types of the attribute, the bundle registers these types each time
DoctrineBundle creates a connection. They exist before the first query, whether
the application runs through the front controller, the console (including the
schema tool and migrations) or the tests. Without DoctrineBundle, the bundle
registers them when the kernel boots. Registering them doesn't create the store
or its database connection. Also like the attribute, they require a ``store``
or a ``default_client`` to be configured; otherwise, the container fails to
compile.

The name is then used like any Doctrine type::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class User
    {
        #[ORM\Column(type: 'app_user_email')]
        private string $email = '';
    }

A declared type and an ``#[Encrypted]`` column that share the same ``type``
and ``key`` are the same type: the attribute reuses the declared one
instead of registering a second. Otherwise the attribute generates the name
``encrypted_<type>@<key>``.

Both forms use the service that
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface`
resolves to: the store-backed encrypter when a ``store`` is configured, and
the envelope encrypter of the default client otherwise. A column that has to use
another encrypter needs an
:class:`Symfony\\Component\\KeyManagement\\Bridge\\DoctrineDbal\\EncryptedTypes`
service of its own. Declare it as a public service and call its
``register()`` method from the ``boot()`` method of your kernel. Such types
can only wrap the types registered before, such as the built-in ones, since
DoctrineBundle registers the types of its configuration later.

What the Column Holds
---------------------

The stored value is the envelope, as bytes, in a binary column. Reading the
row decrypts it and hands the plaintext back to the underlying type, so the
property behaves exactly as it did before.

The column is always declared as an unbounded blob, and a ``length`` on the
mapping is ignored on purpose. An envelope is larger than the value it
holds, by 47 bytes with a store and by a hundred bytes or more without one,
and a column sized after the plaintext would truncate it. On MySQL and
MariaDB outside strict mode that truncation is a warning rather than an
error, which would leave a row that no longer decrypts.

A column holding zero bytes is refused, like any other value that is not
an envelope. Encrypting an empty string gives a non-empty envelope, so an
empty column can only mean the value was never written through the type,
and handing it to the underlying type unchecked would return ``''``,
``false`` or ``0`` without authenticating anything.

Each value is authenticated together with the key of its column and the name of
the Doctrine type it wraps, such as ``string``. A value copied to a column with
another key or another type name is refused. Changing the key or the type name
of a column therefore means rewriting its values, even when both type names
resolve to the same PHP class. Neither the name of the encrypted type nor the
configuration of the wrapped type is authenticated: renaming the type doesn't
require rewriting, and neither does changing the PHP class behind the type
name, as long as the new class reads the old values.

Two columns using the same key and the same type name can read each other's
values. Give them different keys or scopes to keep them apart.

Rotating the master key behind a key that keeps its name, such as an alias moved
to a new key, doesn't require rewriting values. Changing the configured
``key``, whether an alias, a key name, an ARN or a scope, does, even if the KMS
can still read the old key.

When you build an
:class:`Symfony\\Component\\KeyManagement\\Bridge\\DoctrineDbal\\EncryptedType`
yourself, pass the type name as its fourth argument. Only ``null``, the
default, falls back to the class of the wrapped type, which then changes with
that class and requires rewriting the values when it is renamed or subclassed.
Use the ``type`` option of ``key_management.doctrine_dbal.types`` as the name
when a type you build and a configured one must read each other's values.

Copied, Deleted and Restored Values
-----------------------------------

An attacker who can write to your database can't read an encrypted value.
Decryption fails when the attacker changes the value. They can still copy a
value to another row of the same column, since a Doctrine type never sees the
row it converts. They can set the column to ``NULL``, which the type stores as
is, without encrypting or authenticating it. And they can put back an older
value of the same row.

When a value protects something, like a two-factor authentication secret, store
the identity of the row inside the value and check it after decryption. Also
give every row a value, so that a missing value is always an error, never a user
without two-factor authentication::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Types\UuidType;
    use Symfony\Component\KeyManagement\Bridge\DoctrineOrm\Attribute\Encrypted;
    use Symfony\Component\Uid\Uuid;

    #[ORM\Entity]
    class User
    {
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME, unique: true)]
        private Uuid $id;

        /**
         * @var array{user: string, secret: ?string}
         */
        #[ORM\Column(type: 'json')]
        #[Encrypted(key: 'user.two_factor')]
        private array $twoFactor;

        public function __construct()
        {
            $this->id = Uuid::v7();
            $this->setTwoFactorSecret(null);
        }

        public function setTwoFactorSecret(?string $secret): void
        {
            $this->twoFactor = ['user' => (string) $this->id, 'secret' => $secret];
        }

        public function getTwoFactorSecret(): ?string
        {
            if ($this->twoFactor['user'] !== (string) $this->id) {
                throw new \RuntimeException('This secret belongs to another user.');
            }

            return $this->twoFactor['secret'];
        }
    }

A user without two-factor authentication has a ``null`` secret inside the
encrypted value, instead of a ``NULL`` column. Copying that value to the row of
another user makes ``getTwoFactorSecret()`` throw an exception. The column isn't
nullable, so the database refuses ``NULL``. If a row contains ``NULL`` anyway,
the property stays uninitialized, and reading the secret throws an error too.

Putting back an older value of the same row isn't detected, for example the
value saved before the user turned two-factor authentication on. To detect it,
you need data that the attacker can't change, stored outside the database. A
version number stored next to the value, or inside it, doesn't help. The
attacker puts the old version number back together with the old value.

Encrypting a Column That Already Holds Values
---------------------------------------------

Adding ``#[Encrypted]`` to a column doesn't encrypt the values it already
holds. The column becomes a blob, and reading a value that isn't an envelope
fails. Encrypt them in a migration of your own: read each value with the type
the column had, then write it back through the encrypted type.

Moving a Column to a Stored Data Key
------------------------------------

Configure a :doc:`store </key_management/store>` with
``read_self_contained: true``. Keep the ``key`` and the type of the column,
since both are authenticated with every value. The key then becomes the scope of
the stored data keys, so don't leave it out at the same time. Values written
from then on contain a reference to a stored data key, while older ones keep
being read through the KMS. Doctrine writes a value again only when it changes,
so rewrite the old ones with a script of your own that reads and writes each of
them through the encrypted type. Then turn the option off, since a
self-contained payload carries its own wrapped data key and skips the scope and
binding checks of the store. The envelope still authenticates the key and the
type name of the column.

Re-wrapping those data keys later moves the whole column to another master
key, or another provider, without touching a single row.
