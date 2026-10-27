Storing the Data Keys
=====================

By default each encrypted payload carries its own data key. That means one
KMS call per value, a couple hundred extra bytes per value, and a master
key that cannot be changed without rewriting every payload.

Configuring a ``store`` changes all three. Data keys are kept in a database
table, payloads carry a 16-byte reference to one, and the KMS is called
once per key and per process. Changing the master key, or the provider,
then means re-encrypting those few rows and no payload at all.

.. include:: /key_management/_experimental.rst.inc

.. _key-management-store-config:

Configuration
-------------

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            clients:
                app: '%env(KMS_DSN)%'
            store:
                # the master key wrapping the data keys; the only required option
                key_id: 'alias/app-key'
                # the client that key belongs to; defaults to the default client
                client: app
                # defaults below
                # other clients the store accepts, for example during a migration
                allowed_clients: []
                # signs the rows of the table; list several secrets to rotate it
                secret: '%kernel.secret%'
                connection: 'doctrine.dbal.default_connection'
                table: 'key_management_data_keys'
                # seconds before the current data key of a scope is replaced by a
                # fresh one; 0 replaces it on every call, null never replaces it
                max_age: 2592000
                # read payloads that carry their own data key (see below)
                read_self_contained: false
                # have the provider authenticate each data key (see below)
                bind_wrapping_context: false

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'clients' => [
                    'app' => env('KMS_DSN'),
                ],
                'store' => [
                    // the master key wrapping the data keys; the only required one
                    'key_id' => 'alias/app-key',
                    // the client that key belongs to; defaults to the default client
                    'client' => 'app',
                    // defaults below
                    // other clients the store accepts, such as during a migration
                    'allowed_clients' => [],
                    // signs the rows of the table; list several secrets to rotate it
                    'secret' => param('kernel.secret'),
                    'connection' => 'doctrine.dbal.default_connection',
                    'table' => 'key_management_data_keys',
                    // seconds before the current data key of a scope is replaced
                    // by a fresh one; 0 replaces it on every call, null never does
                    'max_age' => 2592000,
                    // read payloads that carry their own data key (see below)
                    'read_self_contained' => false,
                    // have the provider authenticate each data key (see below)
                    'bind_wrapping_context' => false,
                ],
            ],
        ]);

This requires the ``symfony/doctrine-dbal-key-management`` package. The
Doctrine DBAL table is the only store shipped for production use, and an
application configures a single one. To keep data keys anywhere else, implement
:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface`
and build a :class:`Symfony\\Component\\KeyManagement\\StoredEnvelopeEncrypter`
on it yourself: the ``store`` option, the autowiring aliases it registers and
the ``key-management:rewrap-data-keys`` command only cover the Doctrine DBAL
store.

Configuring a store changes what
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\EnvelopeDecrypterInterface`
resolve to, so a service injecting either of them starts writing the stored
shape with no change of its own. The envelope encrypter of each client is
still available under its own service id and autowiring alias, for the
services that must keep writing payloads that carry their own data key.

Scopes
------

Once a store is configured, the first argument of ``encrypt()`` is no
longer a master key but a **scope**: the unit that may share one data key.
A column, a tenant or a purpose are the usual choices::

    $envelope = $this->envelopeEncrypter->encrypt('user.email', $email);

Values written under the same scope share a key until ``max_age`` elapses,
after which the next write creates a fresh one. Payloads written earlier
keep referring to the key they were written with, so nothing has to be
rewritten.

The table compares scopes with the collation of its connection. Under a
case-insensitive one, such as the default collations of MySQL and MariaDB,
``user.email`` and ``User.Email`` therefore share their data keys.

The default of 30 days is not arbitrary. Each payload draws a random 96-bit
nonce, and NIST SP 800-38D limits a key used this way to 2^32 encryptions,
across all your servers. ``max_age`` doesn't count them: 30 days stays
under that limit up to about 1,650 writes per second in one scope. Lower it
for a scope written more often than that; ``null`` turns the replacement
off entirely.

Reading Payloads Written Before the Store
-----------------------------------------

The two envelope shapes never mix inside one payload, but a dataset being
migrated holds both. A payload that carries its own data key resolves no
row, so none of the checks below apply to it, and the store refuses it
unless you ask for it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            store:
                key_id: 'alias/app-key'
                read_self_contained: true

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'store' => [
                    'key_id' => 'alias/app-key',
                    'read_self_contained' => true,
                ],
            ],
        ]);

Turn it on while you migrate a dataset to the stored shape, and off once no
payload is left in the old one. It needs a default client, since that is
the client that reads those payloads.

.. _key-management-store-binding:

What a Row Is Bound To
----------------------

A data key sitting in a table is only as trustworthy as the row around it.
Without a check, a row edited underneath the store resolves all the same:
the key created for one tenant comes to serve another, and a replaced key
copied under a fresh reference comes back into service.

Every row therefore carries a 32-byte **binding**, computed from the
reference and the scope that row states, under a subkey derived from the
data key and from the ``secret`` option. The store recomputes it after
decrypting the key and refuses the key when the two don't match. This check
needs nothing from the provider and is always performed.

The secret stops a database writer from inserting a data key of their own as the
newest row of a scope. Without it, your application would encrypt new values
with that key. Wrapping such a key doesn't need your credentials. The public key
of a sealed box is enough. So is a master key of the attacker that your KMS
credentials are allowed to use. The secret defaults to the ``kernel.secret``
parameter. Keep it out of the database, like your other secrets.

To change the secret, list the new one first and keep the old one. New
keys are signed with the first secret, and keys signed with any of them
are accepted:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            store:
                key_id: 'alias/app-key'
                secret: ['%env(STORE_SECRET)%', '%kernel.secret%']

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'store' => [
                    'key_id' => 'alias/app-key',
                    'secret' => [env('STORE_SECRET'), param('kernel.secret')],
                ],
            ],
        ]);

Then sign every key again with the new secret, by re-wrapping the keys under the
client and the master key of the store:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys

Then remove the old secret. Removing a secret before that command has run makes
the keys signed with it unreadable. This includes changing ``kernel.secret``
while the store uses it.

``bind_wrapping_context`` adds a second, independent check carried out by
the provider itself. The reference and the scope are passed as the
authenticated data of the wrap and unwrap calls, so the provider refuses to
unwrap a key whose row has been altered.

It is off by default for two reasons. A provider that cannot authenticate
data refuses to wrap anything at all when given some, which rules out the
sealed box, Azure Key Vault under any ``wrap_algorithm`` but the ``A*GCM``
ones, a Scaleway key whose usage is ``asymmetric_encryption``, and any Vault
Transit key not created with ``derived=true``.
And a provider that ignores authenticated data instead of refusing it adds
no protection, since only the provider ever checks the context.

Turning it on takes effect immediately: the store refuses the keys already
stored until they are re-wrapped, so encrypting under a scope whose current key
is one of them fails too. Re-wrap all of them with one command, which you can
run again if it is interrupted:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys --accept-unbound

The command wraps every key with its context, including the keys it accepted
without one.

.. warning::

    This can't be undone. If you turn the option off later, the keys written or
    re-wrapped while it was on become unreadable, and the command can't re-wrap
    them without their context.

The Table
---------

With ``symfony/doctrine-orm-key-management`` installed, the table joins the
schema the ORM generates, so ``doctrine:schema:update`` and a migration
diff treat it like any mapped table instead of proposing to drop it.
Without that package, declare it in a migration of your own or call
``createTable()`` on the store once::

    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\KeyManagement\Bridge\DoctrineDbal\DataKeyStore;

    final class CreateDataKeyTable
    {
        public function __construct(
            #[Autowire(service: 'key_management.store')]
            private readonly DataKeyStore $store,
        ) {
        }

        public function __invoke(): void
        {
            $this->store->createTable();
        }
    }

Either way it holds exactly six columns:

============================  ================  ==============================================================
Column                        Type              Role
============================  ================  ==============================================================
``id``                        ``BINARY(16)``    A UUIDv7, primary key, and the reference payloads record
``scope``                     ``VARCHAR(191)``  The unit this data key is shared over
``key_material``              ``BLOB``          The data key, encrypted under the master key
``master_key_id``             ``VARCHAR(255)``  The master key that encrypted it
``client``                    ``VARCHAR(64)``   The configured client able to decrypt it
``binding``                   ``BINARY(32)``    Ties the key to the ``id`` and ``scope`` of its row
============================  ================  ==============================================================

Besides its primary key on ``id``, the table has an index on ``scope`` and
``id``, named ``<table>_scope_idx``, which finds the current key of a scope.
Declare it in a migration of your own too.

Every query names those six columns explicitly, so columns of your own are
invisible to the store, as long as they are nullable or have a default.

There is no timestamp column on purpose: a UUIDv7 carries its creation
instant and sorts chronologically, so the newest row of a scope is its
current key and its age is read back from the reference itself. The store
requires Doctrine DBAL 4.3 or later.

Re-wrapping and Replacing Keys
------------------------------

Moving an entire dataset to another master key, or another provider, is a
matter of rewriting a handful of rows rather than any payload. The
``key-management:rewrap-data-keys`` command does it; see
:doc:`/key_management/commands`.

The store only uses its ``client`` and the clients listed in
``allowed_clients``, regardless of the client a row of the table names. To move the keys to
another client, add it to ``allowed_clients`` and re-wrap the keys. Then make
the new client the ``client`` of the store. Keep the old client in
``allowed_clients`` until no row refers to it anymore.

:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface`
is what that command drives, and it is kept separate from
:class:`Symfony\\Component\\KeyManagement\\DataKeyStoreInterface` so that
the encryption path never sees it:

``all(?string $client = null)``
    Walks the stored keys as
    :class:`Symfony\\Component\\KeyManagement\\StoredDataKey` instances,
    oldest first, optionally only those wrapped by one client.

``rewrap(string $reference, Ciphertext $wrapped, string $client)``
    Replaces the wrapping of a stored key. ``$wrapped`` is the data key
    wrapped again with the context returned by ``wrappingContextOf()``.
    The store doesn't decrypt it to check it, so the command checks the
    binding of the row before calling this method, and any other caller
    must do the same. The reference and the scope stay as they are, so
    payloads referring to the key keep resolving.

``wrappingContextOf(StoredDataKey $row)``
    The authenticated data this store wraps that row with, or an empty
    string when ``bind_wrapping_context`` is off. Re-wrapping happens
    outside the store, so it has to bind what the store binds.

``rotate(string $scope)``
    Replaces the current key of a scope with a fresh one. Payloads already
    written keep referring to the old one, so nothing has to be rewritten.

Keys Held in Memory
-------------------

A store exists to decrypt a key once and encrypt many payloads with it, so
it keeps the plaintext key for as long as it lives. That is what spares the
round trips, and it means the process holds key material.

``forget()`` drops everything the store is holding. The service is tagged
``kernel.reset``, so a long-running worker calls it between two units of
work, which is also when a key replaced elsewhere is picked up rather than
at the next payload.

What the store hands out is a
:class:`Symfony\\Component\\KeyManagement\\DataKeyHandle`, not a
:class:`Symfony\\Component\\KeyManagement\\DataKey`; see
:doc:`/key_management/envelopes` for the difference and for what either one
wipes.
