.. _key-management-doctrine:

Encrypting Doctrine Columns
===========================

With ``symfony/doctrine-orm-key-management`` installed, a Doctrine column
can be encrypted with no code at all. Mark the property and name the key it
is encrypted under::

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

The column keeps the type its mapping gives it, declared on
``#[ORM\Column]`` or guessed from the type of the property. The value goes
through that type first and is encrypted afterwards, so a date is stored as
an encrypted date, not as an encrypted string.

.. include:: /key_management/_experimental.rst.inc

.. note::

    The attribute needs an encrypter to exist, which means either a
    configured ``store`` or a ``default_client``. Without one, the
    container fails to compile and says so.

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
property keeps existing values readable, but renaming the table or the column
changes the scope. Before such a rename, set ``key`` to the scope that was
generated for the old names. That scope is
``'orm:'.hash('sha256', serialize([$schema, $table, $column]))``, where
``$schema`` is ``null`` for a table without a schema.

The scope doesn't contain the database, the connection or the tenant. Columns
that have the same names in two databases sharing one store can therefore read
each other's values. Give them explicit keys when that matters. Without a store,
leaving the key out fails, since there is no default master key to encrypt with.

The bundle registers the types of these columns when each entity manager is
created. This loads the metadata of every mapped class at that time, but only
when a column leaves its key out.

Declaring the Types Yourself
----------------------------

The attribute is a shortcut over named Doctrine types, which you can also
declare directly. Use this when a column is mapped in XML or PHP rather
than by attribute, or when you want to pick the type name:

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

Both forms use whatever
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface`
resolves to, which is the store-backed encrypter when a ``store`` is
configured and the default client's otherwise. A column that has to use
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
mapping is ignored on purpose. An envelope is a hundred bytes or so larger
than the value it holds, and a column sized after the plaintext would
truncate it. On MySQL and MariaDB outside strict mode that truncation is a
warning rather than an error, which would leave a row that no longer
decrypts.

A column holding zero bytes is refused with Doctrine's
``ValueNotConvertible``, like any other value that is not an envelope.
Encrypting an empty string gives a non-empty envelope, so an empty column
can only mean the value was never written through the type, and handing it
to the underlying type unchecked would return ``''``, ``false`` or ``0``
without ever authenticating anything. A leading byte naming a format that
does not exist is refused the same way, so a reader cannot tell an attempt
at downgrading a payload from a corrupt column.

Each value is authenticated together with the key of its column and the name of
the Doctrine type it wraps. A value copied to a column with another key or
another type is refused. Changing the key or the type of a column therefore
means rewriting its values. Renaming the encrypted type doesn't require that.
Neither does changing the PHP class behind a type name, as long as the new class
reads the old values.

A value copied to another row of the same column still decrypts, though, since a
Doctrine type never sees the row it converts. When the owner of a value matters,
store the row identity inside the value and check it after decryption.

``NULL`` is stored as is: it is neither encrypted nor authenticated. Deny
protected operations when a required encrypted value is missing, so that
deleting it doesn't grant anything.

Moving a Column to a Stored Data Key
------------------------------------

Configure a :doc:`store </key_management/store>` with
``read_self_contained: true``. Keep the ``key`` and the type of the column,
since both are authenticated with every value. The key then becomes the scope of
the stored data key, so don't leave it out at the same time. New rows then
contain a reference to a stored data key while older ones keep being read
through the KMS. Once no old row is left, turn the option off.

Re-wrapping that data key later moves the whole column to another master
key, or another provider, without touching a single row.
