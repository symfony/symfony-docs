Encrypting Data with KMS
========================

Symfony provides a Key Management System integration through the
:doc:`KeyManagement component </components/key-management>` and the ``framework.key_management``
configuration entry. It lets you encrypt and decrypt application data
through a central key (held by AWS KMS, HashiCorp Vault Transit, a local
keystore, ...) without ever exposing the master key material to the
application.

.. warning::

    The KeyManagement component is :doc:`experimental </contributing/code/experimental>`
    and is not covered by Symfony's :doc:`Backward Compatibility Promise </contributing/code/bc>`.

.. versionadded:: 8.2

    KMS integration was introduced in Symfony 8.2.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/key-management

Then install one or more bridges depending on where your master keys live:

.. code-block:: terminal

    # Cloud-managed KMS (server-side encryption, never exposes the master key)
    $ composer require symfony/aws-key-management
    $ composer require symfony/azure-keyvault-key-management
    $ composer require symfony/google-cloud-key-management
    $ composer require symfony/vault-key-management

    # Source local keys from S3, FTP, Azure Blob, ...
    $ composer require symfony/flysystem-key-management

    # Column-level envelope encryption and data key storage with Doctrine
    $ composer require symfony/doctrine-dbal-key-management

The component itself ships local backends (libsodium XChaCha20-Poly1305,
OpenSSL AES-256-GCM, sealed box) suitable for development, tests and
self-hosted setups.

Configuration
-------------

KMS clients are declared by DSN under ``framework.key_management``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        framework:
            key_management:
                default_client: app
                clients:
                    app: '%env(KMS_DSN)%'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'key_management' => [
                    'default_client' => 'app',
                    'clients' => [
                        'app' => env('KMS_DSN'),
                    ],
                ],
            ],
        ]);

When a single client is registered, the ``default_client`` entry is
inferred and can be omitted. As a shortcut, you can also pass a single
DSN as the value of ``framework.key_management`` itself; it expands to one client
named ``default``.

DSN Schemes
~~~~~~~~~~~

Each backend documents its own DSN scheme. The most common ones are:

============================  =========================================  =======================================
Scheme                        Required package                           Backend
============================  =========================================  =======================================
``sodium://``                 ``symfony/key-management``                 Local XChaCha20-Poly1305 (inline keys)
``sodium+dir://``             ``symfony/key-management``                 Local XChaCha20-Poly1305 (key dir)
``openssl://``                ``symfony/key-management``                 Local AES-256-GCM (inline keys)
``openssl+dir://``            ``symfony/key-management``                 Local AES-256-GCM (key dir)
``sodium-sealed-box://``      ``symfony/key-management``                 Local sealed box (inline keys)
``sodium-sealed-box+dir://``  ``symfony/key-management``                 Local sealed box (key dir)
``aws-kms://``                ``symfony/aws-key-management``             AWS Key Management Service
``azure-keyvault://``         ``symfony/azure-keyvault-key-management``  Azure Key Vault / Managed HSM
``gcp-kms://``                ``symfony/google-cloud-key-management``    Google Cloud KMS
``vault-transit://``          ``symfony/vault-key-management``           HashiCorp Vault Transit
``sodium+fly://``             ``symfony/flysystem-key-management``       XChaCha20 + Flysystem-loaded keys
``openssl+fly://``            ``symfony/flysystem-key-management``       AES-256-GCM + Flysystem-loaded keys
``sodium-sealed-box+fly://``  ``symfony/flysystem-key-management``       Sealed box + Flysystem-loaded keys
============================  =========================================  =======================================

See :doc:`/components/key-management` for the syntax and options of each scheme.

Local backends require their respective extension at runtime: ``ext-openssl``
for ``openssl://``/``openssl+dir://``/``openssl+fly://``, ``ext-sodium`` for
``sodium://``/``sodium+dir://``/``sodium+fly://`` and the sealed-box
schemes. The :class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypter` itself
also requires ``ext-openssl`` for the local AEAD step. ``ext-fileinfo``
must be present whenever the Flysystem bridge is loaded, because
``league/flysystem`` pulls in ``league/mime-type-detection`` which uses it.

Environment-driven configuration is the recommended pattern: keep the DSN
in ``.env``/``.env.local``/secrets and reference it via ``%env(...)%``.

.. code-block:: env

    # .env (example: AWS KMS)
    KMS_DSN=aws-kms://default?region=eu-west-1

    # .env.local (example: local libsodium for development)
    KMS_DSN=sodium+dir:///etc/kms?ext=.bin

Multiple Clients
~~~~~~~~~~~~~~~~

Most applications need a single client, but you can declare more than one
when different parts of the system have different threat models, regions
or rotation cadences:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        framework:
            key_management:
                default_client: app
                clients:
                    app: '%env(KMS_APP_DSN)%'
                    pii: '%env(KMS_PII_DSN)%'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'key_management' => [
                    'default_client' => 'app',
                    'clients' => [
                        'app' => env('KMS_APP_DSN'),
                        'pii' => env('KMS_PII_DSN'),
                    ],
                ],
            ],
        ]);

.. _key-management-store-config:

Storing the Data Keys
~~~~~~~~~~~~~~~~~~~~~

By default every envelope-encrypted payload carries its own data key,
wrapped by the master key: the KMS is contacted once per encrypted value,
each payload is a couple hundred bytes larger, and the master key cannot be
rotated without rewriting every payload that refers to it.

Configuring a ``store`` inverts that. Data keys are persisted in a Doctrine
DBAL table and payloads carry a 16-byte reference to one, so the KMS is
contacted once per data key and per process, and the master key protecting
those keys can be rotated (or swapped for another provider) by rewrapping a
handful of rows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        framework:
            key_management:
                clients:
                    app: '%env(KMS_DSN)%'
                store:
                    # the client wrapping the data keys this store creates
                    client: app
                    # the master key on that client
                    key_id: 'alias/app-key'
                    # defaults below
                    connection: 'doctrine.dbal.default_connection'
                    table: 'key_management_data_keys'
                    # seconds after which the current data key of a scope is
                    # retired in favour of a fresh one; null keeps it in use
                    max_age: null

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'key_management' => [
                    'clients' => [
                        'app' => env('KMS_DSN'),
                    ],
                    'store' => [
                        'client' => 'app',
                        'key_id' => 'alias/app-key',
                    ],
                ],
            ],
        ]);

This section requires the ``symfony/doctrine-dbal-key-management`` package;
without it, the container fails to compile with the command to run.

A data key is shared over a **scope**: whatever the application decides may
share a key, typically a column, a tenant or a purpose. It is what the first
argument of ``encrypt()`` names once a store is configured. Retiring a key
(through ``max_age`` or an explicit rotation) only affects payloads written
afterwards, since older payloads keep referring to the key they were written
with.

Configuring a store also changes what the envelope interfaces resolve to:
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\EnvelopeDecrypterInterface` are then
aliased to the store-backed encrypter, which is given the default client's
encrypter as a fallback so it reads the payloads written before it as well
as the ones it writes. The per-client encrypters stay reachable under their
own name (see `Service IDs`_) for whoever wants the other regime explicitly.

The table is not created for you. Either declare it in your own migration,
or call ``createTable()`` on the store once::

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

Either way the table holds exactly five columns:

============================  ================  ==============================================================
Column                        Type              Role
============================  ================  ==============================================================
``id``                        ``BINARY(16)``    A UUIDv7, primary key, and the reference recorded by payloads
``scope``                     ``VARCHAR(191)``  The unit a data key is shared over
``key_material``              ``BLOB``          The wrapped data key
``master_key_id``             ``VARCHAR(255)``  The master key that wrapped it
``client``                    ``VARCHAR(64)``   The configured KMS client able to unwrap it
============================  ================  ==============================================================

Every query names those five explicitly, so columns of your own are invisible
to the store as long as they are nullable or have a default: nothing fills
them on insert.

.. note::

    The store holds plaintext key material in memory for as long as it lives:
    that is what spares the round trips. The service is tagged
    ``kernel.reset``, so a long-running worker drops everything it retained
    between two units of work. A rotation performed elsewhere is picked up at
    that point rather than at the next payload.

Using KMS in Services
---------------------

Inject the encrypter / decrypter abstractions where you need them. The
default client is autowired via the unqualified interfaces::

    use Symfony\Component\KeyManagement\DecrypterInterface;
    use Symfony\Component\KeyManagement\EncrypterInterface;

    final class TokenVault
    {
        public function __construct(
            private EncrypterInterface $encrypter,
            private DecrypterInterface $decrypter,
        ) {
        }

        public function store(string $token): string
        {
            return (string) $this->encrypter->encrypt('app-key', $token);
        }
    }

To inject a non-default client, name it with the
:ref:`#[Target] attribute <autowiring-multiple-implementations-same-type>`::

    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\KeyManagement\EncrypterInterface;

    final class PiiVault
    {
        public function __construct(
            #[Target('pii')]
            private EncrypterInterface $encrypter,
        ) {
        }
    }

Each client is also aliased to a parameter named after it and suffixed by
``KeyManagement`` (``$piiKeyManagement``), which autowiring still honours
without the attribute.

For envelope encryption, inject
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface` /
:class:`Symfony\\Component\\KeyManagement\\EnvelopeDecrypterInterface` instead.
``#[Target]`` works the same way there, and the fallback parameter name is
suffixed by ``EnvelopeEncrypter`` (``$piiEnvelopeEncrypter``)::

    use Symfony\Component\KeyManagement\EnvelopeDecrypterInterface;
    use Symfony\Component\KeyManagement\EnvelopeEncrypterInterface;

    final class DocumentArchive
    {
        public function __construct(
            private EnvelopeEncrypterInterface $envelopeEncrypter,
            private EnvelopeDecrypterInterface $envelopeDecrypter,
        ) {
        }
    }

The component splits the encrypt/decrypt halves into separate interfaces
so that components which only need to read or only need to write can
declare that intent at the type level.

When a store is configured, the unqualified interfaces resolve to the
store-backed encrypter, and ``$storedEnvelopeEncrypter`` names it explicitly.
The store itself is autowired through
:class:`Symfony\\Component\\KeyManagement\\DataKeyStoreInterface` (or
:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface` for the
administration half: listing, rewrapping and rotating).

Service IDs
~~~~~~~~~~~

Beyond autowiring, ``framework.key_management`` registers stable service ids that you
can reference manually in YAML / PHP service definitions:

* ``key_management.<name>`` : the configured client. Implements
  ``EncrypterInterface & DecrypterInterface`` (and
  ``DataKeyGeneratorInterface`` when the backend supports it). Tagged
  ``key_management.client`` with the client name as ``key``.
* ``key_management.envelope_encrypter.<name>`` : the matching
  :class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypter`, which writes
  self-contained envelopes. Implements
  ``EnvelopeEncrypterInterface & EnvelopeDecrypterInterface``.
* ``key_management.store`` : the
  :class:`Symfony\\Component\\KeyManagement\\Bridge\\DoctrineDbal\\DataKeyStore`,
  registered only when ``store`` is configured.
* ``key_management.stored_envelope_encrypter`` : the
  :class:`Symfony\\Component\\KeyManagement\\StoredEnvelopeEncrypter` built on that
  store, with the default client's encrypter as a fallback for the payloads
  written before it.
* ``key_management.factory`` : the
  :class:`Symfony\\Component\\KeyManagement\\Factory\\FactoryRegistry` that turns a
  DSN string into a backend instance (used internally by the bundle).

The unqualified interfaces (``EncrypterInterface``,
``EnvelopeEncrypterInterface``, ...) are aliased to the default client so
type-hinted services pick it up without further wiring.

Console Commands
----------------

Four commands ship with the bundle to operate the configured client(s)
from the CLI:

.. code-block:: terminal

    # Encrypts the argument (or standard input) through the default client and
    # writes a base64 envelope to standard output.
    $ php bin/console key-management:encrypt app-key 'hello world'
    $ cat secrets.json | php bin/console key-management:encrypt app-key

    # Inverse operation: the key id is read from the envelope itself.
    $ php bin/console key-management:decrypt < envelope.b64

    # Generates a fresh data key and prints JSON with key_id, length,
    # base64 plaintext and base64 wrapped form.
    $ php bin/console key-management:generate-data-key app-key

    # Moves every stored data key under another master key, or another
    # provider. Payloads are neither read nor rewritten.
    $ php bin/console key-management:rewrap-data-keys --to=azure --key-id=my-vault-key

The first three commands take ``--client=<name>`` to pick a client, which is
required as soon as several are registered and none of them is named
``default``. They also take ``--aad``, and ``key-management:generate-data-key``
accepts ``--length`` (16 bytes minimum).

Both ``key-management:encrypt`` and ``key-management:decrypt`` read STDIN when
their payload argument is omitted, and write their diagnostics to STDERR, so
they compose in a shell pipeline. Re-encrypting an envelope under another key
after a rotation is one command:

.. code-block:: terminal

    $ php bin/console key-management:decrypt < old.b64 \
        | php bin/console key-management:encrypt new-key > new.b64

These two commands always work with self-contained envelopes; an envelope
referring to a stored data key is reported as such rather than decrypted,
since the CLI has no store to resolve it with. Decrypt those through the
application.

``key-management:rewrap-data-keys`` requires a configured store. It unwraps
each row with the client that row records and re-wraps it with ``--to``,
committing row by row, so an interrupted run is resumed by running the same
command again. Restrict it with ``--from=<client>`` while both the old and
the new client are configured, and start with ``--dry-run`` to see the scope
of the operation:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys --from=aws --to=azure --key-id=my-vault-key --dry-run

.. _key-management-doctrine:

Encrypted Doctrine Columns
--------------------------

The ``symfony/doctrine-dbal-key-management`` bridge ships a Doctrine DBAL
``Type`` that decorates any parent type with column-level envelope
encryption. What a row ends up carrying is decided by the encrypter you give
it: a self-contained envelope with the default wiring, or a reference to a
stored data key when a :ref:`store <key-management-store-config>` is
configured.

Register one ``Type`` per ``(parent type, key)`` pair you need. Inject the
default envelope encrypter (autowired by ``framework.key_management``) and
call ``Type::getTypeRegistry()->register()``::

    // src/Kms/EncryptedTypesRegistrar.php
    use Doctrine\DBAL\Types\Type;
    use Symfony\Component\KeyManagement\Bridge\DoctrineDbal\EncryptedType;
    use Symfony\Component\KeyManagement\EnvelopeDecrypterInterface;
    use Symfony\Component\KeyManagement\EnvelopeEncrypterInterface;

    final class EncryptedTypesRegistrar
    {
        public function __construct(
            private readonly EnvelopeEncrypterInterface&EnvelopeDecrypterInterface $envelopes,
        ) {
        }

        public function register(): void
        {
            Type::getTypeRegistry()->register(
                'app_user_email',
                new EncryptedType(
                    Type::getTypeRegistry()->get('string'),
                    $this->envelopes,
                    // a master key id without a store, a scope with one
                    'alias/app-key',
                ),
            );
        }
    }

Then use the registered alias on the entity::

    // src/Entity/User.php
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class User
    {
        #[ORM\Column(type: 'app_user_email')]
        public string $email;
    }

The stored bytes are an opaque envelope (binary column). Doctrine reads
it back through the same ``Type``, decrypts it via the configured
``EnvelopeDecrypterInterface``, and feeds the plaintext to the parent
type's ``convertToPHPValue()``.

.. warning::

    The registration must happen before the first query, on every request.
    In particular, do not defer it to Doctrine ORM's ``loadClassMetadata``
    event: that event does not fire when metadata comes from the cache, which
    DoctrineBundle enables in production, so a cached field mapping would
    reference a type name missing from the registry and every read of the
    column would fail with ``UnknownColumnType``. Declare the types where the
    application declares the rest of its Doctrine configuration instead.

Migrating a column to a stored data key is a matter of keeping the same
registered name: rows written before the store keep being read through the
KMS, thanks to the fallback the bundle wires behind the store-backed
encrypter, and rows written afterwards refer to the stored key. Rewrapping
that key later moves the whole column to another master key, or another
provider, without touching a single row.

This bridge requires Doctrine DBAL >= 4.5.

.. _key-management-blind-index-doctrine:

Searching an Encrypted Column
-----------------------------

Envelope-encrypted columns are **not searchable**: the nonce is drawn afresh
every time, so the same plaintext encrypts to a different ciphertext on every
write and ``WHERE email = ?`` never matches. The answer is a
:ref:`blind index <key-management-blind-index>` in a sibling column, a keyed
digest of the value that is equal for equal values.

Register one service per question the application asks, all sharing a key of
their own, minted once with ``key-management:generate-data-key`` and kept
wrapped in the configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Component\KeyManagement\BlindIndex\Email:
                arguments:
                    $kms: '@key_management.app'
                    $wrappedKey: !service
                        class: Symfony\Component\KeyManagement\Ciphertext
                        arguments: ['%env(base64:KMS_INDEX_KEY)%', 'alias/app-key']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\KeyManagement\BlindIndex\Email;
        use Symfony\Component\KeyManagement\Ciphertext;

        return static function (ContainerConfigurator $container): void {
            $container->services()
                ->set(Email::class)
                    ->arg('$kms', service('key_management.app'))
                    ->arg('$wrappedKey', inline_service(Ciphertext::class)
                        ->args([env('base64:KMS_INDEX_KEY'), 'alias/app-key']))
            ;
        };

Then say, on the column that holds the tag, where its value comes from. A
listener shipped by ``symfony/doctrine-bridge`` then fills it on every flush,
so no write path has to remember it::

    // src/Entity/User.php
    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Bridge\Doctrine\Attribute\BlindIndexed;
    use Symfony\Component\KeyManagement\BlindIndex\Email;

    #[ORM\Entity]
    #[ORM\Index(columns: ['email_index'])]
    class User
    {
        #[ORM\Column(type: 'app_user_email')]
        public string $email = '';

        #[ORM\Column(length: 64)]
        #[BlindIndexed('email', Email::class)]
        public string $emailIndex = '';
    }

The attribute names the class of the index, and any ``BlindIndex`` service
the application registers is found by it, so nothing has to be tagged by
hand. A property can be indexed by several columns, each naming its own
projection.

Queries are unchanged: they have no entity to read the attribute on, and go
on computing the tag themselves::

    $repository->findOneBy(['emailIndex' => $index->of($email)]);

.. warning::

    The attribute covers the write path of the ORM alone. A row inserted
    through DBAL, or a bulk ``UPDATE ... SET email = ...``, never reaches the
    listener and leaves the tag as it was. That is worse than an empty tag:
    the search then returns the row that used to hold the value.

Going Further
-------------

* :doc:`/components/key-management`: full component reference, available backends
  and bridges.
* :ref:`Envelope wire format <key-management-wire-format>`: the two shipped
  byte layouts and the contract they follow.
