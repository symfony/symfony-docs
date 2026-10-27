Encrypting Data with a KMS
==========================

A Key Management System (KMS) is a service that holds encryption keys and
uses them on your behalf. Your application sends it data to encrypt or
decrypt and gets the result back; it never sees the key itself. AWS KMS,
Azure Key Vault, Google Cloud KMS, HashiCorp Vault, Scaleway Key Manager
and any KMIP server all work this way, and so do the local backends
Symfony ships for development.

Symfony integrates them through the ``key_management`` configuration. With
it you can:

* encrypt and decrypt values from your own services;
* encrypt a Doctrine column, so the database holds ciphertext only;
* search such a column by exact value, through a
  :doc:`blind index </key_management/searching>`;
* move your whole dataset to another key, or another provider, without
  rewriting a single row.

.. include:: /key_management/_experimental.rst.inc

.. versionadded:: 8.2

    KMS integration was introduced in Symfony 8.2.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/key-management

Then install the bridge for the provider holding your keys. Each one is a
separate package, so you only install what you use:

.. code-block:: terminal

    $ composer require symfony/aws-key-management
    $ composer require symfony/azure-keyvault-key-management
    $ composer require symfony/google-cloud-key-management
    $ composer require symfony/hashicorp-vault-key-management
    $ composer require symfony/scaleway-key-management
    $ composer require symfony/kmip-key-management

Three more packages cover related needs:

.. code-block:: terminal

    # encrypted Doctrine DBAL types, and the table holding the data keys
    $ composer require symfony/doctrine-dbal-key-management

    # the Encrypted and BlindIndexed attributes, and that table in the ORM schema
    $ composer require symfony/doctrine-orm-key-management

    # read local keys from S3, FTP, Azure Blob, ... through Flysystem
    $ composer require symfony/flysystem-key-management

The component itself ships three local backends that keep the key on disk
or in an environment variable. They need no provider, which makes them
convenient for development, tests and self-hosted setups. See
:doc:`/key_management/backends` for all of them.

.. _key-management-how-it-works:

How It Works
------------

A few terms come up everywhere below.

The **master key** is the key the KMS holds. It never leaves the provider.
You name it in the configuration (``alias/app-key`` on AWS, a resource name
on Google Cloud and so on) and the provider does the rest.

A **data key** is a short key, 32 bytes, that your application uses to
encrypt one payload locally. The KMS creates it, hands you two copies, one
in clear text to encrypt with and one encrypted under the master key, and
forgets it. You store the encrypted copy next to the data. To read the data
back, you ask the KMS to decrypt that copy, then decrypt the data with it.

Encrypting a payload under a data key, and the data key under the master
key, is called **envelope encryption**. It is what Symfony does by default,
for two reasons:

* the KMS only ever sees 32 bytes, so the payload can be any size, while a
  direct call to a provider is capped: 4 KiB on AWS KMS, and a whole KMIP
  message must fit in 16 MiB;
* the bulk encryption happens in PHP, so it costs no network call.

The result is an **envelope**: a single binary blob holding the encrypted
data key, the nonce, the authentication tag and the ciphertext. You store
it as is. Nothing else is needed to read it back but access to the master
key.

One data key per payload means one KMS call per payload. Configuring a
:doc:`data key store </key_management/store>` changes that: data keys are
kept in a database table, many payloads share one, and each payload carries
a 16-byte reference instead of the key. The KMS is then called once per key
and per process rather than once per value, and changing the master key is
a matter of re-encrypting a few rows.

Configuration
-------------

The component ships its own bundle, ``KeyManagementBundle``, which the
FrameworkBundle registers as soon as the package is installed. Declare a
client by giving it a DSN:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            clients:
                app: '%env(KMS_DSN)%'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'clients' => [
                    'app' => env('KMS_DSN'),
                ],
            ],
        ]);

That is the whole configuration most applications need. The DSN says which
provider to talk to and how to authenticate; keep it in an environment
variable or in the :ref:`secrets vault <configuration-secrets>`, as the
example does, since it usually holds a credential:

.. code-block:: env

    # .env (production: AWS KMS in Ireland)
    KMS_DSN=aws-kms://default?region=eu-west-1

    # .env.local (development: local keys in a directory)
    KMS_DSN=sodium+dir:///etc/kms?ext=.bin

As a shortcut, a single DSN can be the value of ``key_management`` itself.
It then declares one client named ``default``:

.. code-block:: yaml

    # config/packages/key_management.yaml
    key_management: '%env(KMS_DSN)%'

See :doc:`/key_management/clients` for the DSN of each provider, for
declaring several clients, and for wrapping data under several providers at
once.

Encrypting from a Service
-------------------------

Type-hint the interface you need and autowiring injects the default
client::

    use Symfony\Component\KeyManagement\Ciphertext;
    use Symfony\Component\KeyManagement\DecrypterInterface;
    use Symfony\Component\KeyManagement\EncrypterInterface;

    final class TokenVault
    {
        public function __construct(
            private EncrypterInterface $encrypter,
            private DecrypterInterface $decrypter,
        ) {
        }

        public function store(string $token): array
        {
            $ciphertext = $this->encrypter->encrypt('app-key', $token);

            // keep both: decrypt() needs the key id, not only the bytes
            return [$ciphertext->blob, $ciphertext->keyId];
        }

        public function read(string $blob, string $keyId): string
        {
            return $this->decrypter->decrypt(new Ciphertext($blob, $keyId));
        }
    }

Encrypting and decrypting are two interfaces rather than one so that a
service that only writes, or only reads, can say so in its signature.

``encrypt()`` returns a
:class:`Symfony\\Component\\KeyManagement\\Ciphertext`, a
:phpclass:`Stringable` container holding the bytes the provider returned
together with the key id needed to read them back. Store both, since
casting it to a string yields the bytes alone. That key id is the one the
provider reported, which is not always the one that was asked for, since
AWS KMS resolves an alias to a key ARN and Azure Key Vault pins the version
it used. Recording the resolved id keeps the ciphertext readable after the
alias moves on.

Those two interfaces talk to the provider directly, which caps the payload
size. For anything that is not a short string, inject the envelope
interfaces instead. They take and return an
:class:`Symfony\\Component\\KeyManagement\\Envelope`, which stringifies to
the bytes you store::

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

Binding a Ciphertext to Its Context
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The third argument of ``encrypt()``, and the second of ``decrypt()``, is
the Additional Authenticated Data (AAD): bytes that are authenticated but
not encrypted, and that must be supplied again, identical, to decrypt::

    $aad = 'user.email:tenant=acme';

    $ciphertext = $this->encrypter->encrypt('app-key', $payload, $aad);
    $plaintext = $this->decrypter->decrypt($ciphertext, $aad);

Put in the AAD everything the value must belong to, here the column and the
tenant. A ciphertext copied to another column or another tenant then fails to
decrypt, rather than quietly succeeding. Compute the AAD from what you are
reading, such as the row or the request, and never from the ciphertext itself.

AAD is opaque bytes. Structured context has to be serialized to a stable
form first, such as JSON with sorted keys, or two equal contexts will not
produce equal bytes. A backend that cannot enforce AAD throws
:class:`Symfony\\Component\\KeyManagement\\Exception\\UnsupportedOperationException`
on a non-empty value when you call it directly, rather than ignoring it.

In envelope mode, the local cipher authenticates the AAD and the KMS never
sees it. A mismatch is therefore refused on every backend, including the
ones that cannot authenticate data.

For the same reason, a self-contained envelope cannot rely on a KMS policy
conditioned on the AAD, such as the ``kms:EncryptionContext`` condition of AWS
KMS. A :doc:`data key store </key_management/store>` with
:ref:`bind_wrapping_context <key-management-store-binding>` does pass a context
to the KMS, but it is the reference and the scope of the stored key, not your
AAD.

.. _key-management-choosing-client:

Choosing a Client
~~~~~~~~~~~~~~~~~

To inject a client other than the default one, name it with the
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

Each client is also aliased to an argument named after it: ``$piiKms`` for
the client itself and ``$piiEnvelopeEncrypter`` for its envelope encrypter,
which autowiring honors without the attribute.

When a store is configured, the envelope interfaces resolve to the
store-backed encrypter. ``#[Target('stored')]`` and the argument name
``$storedEnvelopeEncrypter`` both name it explicitly. The store itself is
autowired through
:class:`Symfony\\Component\\KeyManagement\\DataKeyStoreInterface`, or
through
:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface`
for the administrative half: listing the keys, re-wrapping them and
replacing them.

Service IDs
~~~~~~~~~~~

Beyond autowiring, the bundle registers service ids you can reference by
hand in a YAML or PHP service definition:

``key_management.<name>``
    A configured client. Implements ``EncrypterInterface`` and
    ``DecrypterInterface``, plus ``DataKeyGeneratorInterface`` when the
    backend can create data keys. Tagged ``key_management.client``.

``key_management.envelope_encrypter.<name>``
    The envelope encrypter of that client, which writes payloads carrying
    their own data key.

``key_management.store``
    The data key store, registered only when ``store`` is configured.

``key_management.stored_envelope_encrypter``
    The encrypter built on that store, which writes payloads carrying a
    reference instead.

``key_management.doctrine_dbal.encrypted_types``
    Contains the encrypted Doctrine types. A DoctrineBundle connection registers
    these types when the connection is created, so they can wrap the types of
    ``doctrine.dbal.types``. Without DoctrineBundle, the types are registered
    when the kernel boots. Registered only when some types are declared, in the
    configuration or through an ``#[Encrypted]`` column with an explicit
    ``key``.

``key_management.factory``
    Turns a DSN into a backend. Used by the bundle itself.

Four tags are meant for services of your own. ``key_management.client`` adds a
client to the ones that the profiler and the encrypt, decrypt and data key
commands know about, under the name its ``key`` attribute gives, the service
id by default. ``key_management.factory``, which autoconfiguration adds to
every :class:`Symfony\\Component\\KeyManagement\\Factory\\KmsFactoryInterface`,
makes a DSN scheme of your own available. ``key_management.flysystem`` is
covered in :ref:`key-management-flysystem` and ``key_management.blind_index``
in :doc:`/key_management/searching`. The data key store doesn't use tagged
clients. It only trusts the ``client`` and ``allowed_clients`` options of its
configuration.

Profiling
---------

When the profiler is enabled, every client, every envelope encrypter and
the store are traced to record what a request asked of the KMS. No
plaintext, ciphertext, data key or AAD is recorded. The **Key Management**
panel adds the operations up by call site, by key or scope and by service:
how many there were, how many reached the KMS, the bytes in and out and the
time they took.

This is where you confirm that a store is doing its job: it shows one
round trip for many encrypted values rather than one per value.

Learn More
----------

.. toctree::
    :maxdepth: 1

    key_management/clients
    key_management/backends
    key_management/store
    key_management/doctrine
    key_management/searching
    key_management/commands
    key_management/envelopes
    key_management/testing
