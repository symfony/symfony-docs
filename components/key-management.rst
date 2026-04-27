The KeyManagement Component
===========================

    The KeyManagement Component provides a unified abstraction over Key Management
    Systems (KMS) such as AWS KMS or HashiCorp Vault Transit. It exposes a
    small high-level API to encrypt and decrypt payloads, and to generate
    data encryption keys for envelope encryption, without ever exposing
    the master key material to the application.

.. warning::

    The KeyManagement component is :doc:`experimental </contributing/code/experimental>`
    and is not covered by Symfony's :doc:`Backward Compatibility Promise </contributing/code/bc>`.

If you're using the Symfony Framework, read the
:doc:`Symfony Framework KMS documentation </key-management>`.

.. versionadded:: 8.2

    The KeyManagement component was introduced in Symfony 8.2.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/key-management

.. include:: /components/require_autoload.rst.inc

Each KMS backend ships as its own Composer package: install only the bridge(s)
you need. The component itself includes three local backends (libsodium,
OpenSSL, sealed box) suitable for development, tests and self-hosted setups.

Two Encryption Modes
--------------------

The component offers two complementary APIs:

* **Direct mode**: encrypt small payloads (config secrets, API tokens,
  identifiers, ...) by handing the plaintext to the KMS and getting back an
  opaque :class:`Symfony\\Component\\KeyManagement\\Ciphertext`. Backed by
  :class:`Symfony\\Component\\KeyManagement\\EncrypterInterface` /
  :class:`Symfony\\Component\\KeyManagement\\DecrypterInterface`.

* **Envelope mode**: encrypt arbitrary-size payloads (files, JSON
  documents, database rows, ...). The KMS only ever sees a short data
  encryption key (DEK); the bulk plaintext is encrypted locally with that
  DEK using AES-256-GCM. Backed by
  :class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypterInterface` and the
  :class:`Symfony\\Component\\KeyManagement\\Envelope` value object.

Both modes share the same configured backend; pick the API that matches the
size and lifecycle of the payload. As a rule of thumb:

* **Direct mode** for sub-kB payloads (config secrets, API tokens, short
  identifiers). Network round-trip cost dominates; the KMS does the actual
  encryption. AWS KMS caps direct ``encrypt`` at 4 KiB; cloud bridges
  typically have similar limits.

* **Envelope mode** for anything larger (files, JSON documents, database
  rows, ...). The bulk encryption is local and scales linearly with the
  payload size. The :class:`Symfony\\Component\\KeyManagement\\Envelope` itself
  is a binary frame, suitable for direct persistence.

Envelope mode itself comes in two flavors, which differ only in where the data
key comes from and what the frame carries:

* :class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypter` mints a fresh
  data key per payload and ships it wrapped inside the envelope. One KMS
  round-trip per ``encrypt``/``decrypt`` call, and a frame that decrypts with
  nothing but the KMS.

* :class:`Symfony\\Component\\KeyManagement\\StoredEnvelopeEncrypter` takes the
  data key from a :ref:`data key store <key-management-data-key-store>` and
  ships a 16-byte reference to it. One KMS round-trip per data key and per
  process, and a master key that can be rotated by rewrapping a handful of
  rows instead of rewriting every payload.

Direct Mode
-----------

The simplest way to encrypt and decrypt with a managed key::

    use Symfony\Component\KeyManagement\KeyLoader\InMemoryKeyLoader;
    use Symfony\Component\KeyManagement\Local\SodiumKms;

    $kms = new SodiumKms(new InMemoryKeyLoader([
        'app-key' => sodium_crypto_aead_xchacha20poly1305_ietf_keygen(),
    ]));

    $ciphertext = $kms->encrypt('app-key', 'hello world');
    // returns Symfony\Component\KeyManagement\Ciphertext (Stringable)

    $plaintext = $kms->decrypt($ciphertext);
    // returns 'hello world'

The ``Ciphertext`` object is a :phpclass:`Stringable` opaque container that
bundles the bytes returned by the backend with the ``$keyId`` needed to
decrypt them. Persist it as-is (stringification yields the raw bytes).

Additional Authenticated Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``$aad`` argument is integrity-protected (the same bytes must be
supplied at decryption time) but is **not** encrypted. Use it to bind a
ciphertext to a context (tenant id, user id, document type, ...) so that a
ciphertext stolen from one row cannot be replayed in another::

    $ciphertext = $kms->encrypt('app-key', $payload, aad: 'tenant=acme');
    $plaintext = $kms->decrypt($ciphertext, aad: 'tenant=acme');

AAD is treated as opaque bytes. If you have structured data, serialize it
to a stable form (e.g. canonical JSON with sorted keys) before passing it.
Backends that cannot enforce AAD throw
:class:`Symfony\\Component\\KeyManagement\\Exception\\UnsupportedOperationException`
on a non-empty value rather than silently ignoring it.

Deterministic Encryption
~~~~~~~~~~~~~~~~~~~~~~~~

By default each call uses a fresh random nonce, so the same plaintext
encrypts to a different ciphertext every time. Setting ``$deterministic``
to ``true`` derives the nonce from the AAD and the plaintext keyed by the
master key (HMAC-SHA512 truncated for OpenSSL, keyed BLAKE2b for libsodium),
so the same ``(key, AAD, plaintext)`` triple always yields the same
ciphertext. This enables exact-match indexing on encrypted columns at the
cost of leaking equality::

    $ciphertext = $kms->encrypt('app-key', $email, deterministic: true);

The AAD takes part in that derivation, and does so unambiguously: two
encryptions differing only by their AAD would otherwise reuse a nonce under
the same key, which hands an attacker the authentication key of the AEAD.
Two ciphertexts are therefore only comparable when they were produced with
the same AAD.

Only the local symmetric backends offer it. Sealed box and every cloud
bridge (AWS KMS, Azure Key Vault, Google Cloud KMS, Vault Transit) throw
:class:`Symfony\\Component\\KeyManagement\\Exception\\UnsupportedOperationException`
when ``$deterministic`` is ``true``.

Envelope Mode
-------------

For arbitrary-size payloads, use
:class:`Symfony\\Component\\KeyManagement\\EnvelopeEncrypter`. The KMS only ever sees
the wrapped data key; the bulk plaintext is encrypted locally with
AES-256-GCM::

    use Symfony\Component\KeyManagement\Envelope;
    use Symfony\Component\KeyManagement\EnvelopeEncrypter;

    $envelopeEncrypter = new EnvelopeEncrypter($kms);

    $envelope = $envelopeEncrypter->encrypt('app-key', $largePayload);
    file_put_contents('/var/data/payload.bin', $envelope);

    $payload = $envelopeEncrypter->decrypt(
        Envelope::fromBytes(file_get_contents('/var/data/payload.bin'))
    );

The :class:`Symfony\\Component\\KeyManagement\\Envelope` value object is
self-contained here: it carries the key id, the wrapped DEK, the IV, the AEAD
tag and the ciphertext, laid out by
:class:`Symfony\\Component\\KeyManagement\\SelfContainedFormat`. It is
:phpclass:`Stringable` (yields the raw frame) and round-trips through
:method:`Symfony\\Component\\KeyManagement\\Envelope::fromBytes`, which resolves
the format from the leading byte.

The same envelope decrypts identically regardless of which KMS backend
produced it, as long as a backend with access to the same master key is
used to decrypt. This makes it safe to swap backends (e.g. local during
development, AWS KMS in production) without re-encrypting.

The ``$aad`` argument is forwarded **both** to the KMS (binding the wrapped
DEK) and to the local AEAD (binding the bulk ciphertext). The double
binding is defense in depth: the local AEAD always catches AAD mismatches
even on backends where the KMS cannot enforce them.

Envelopes are always randomized: there is no deterministic counterpart to
the ``$deterministic`` flag of the direct path. A stable output would require
a stable data key, which ``generateDataKey()`` refuses to return by contract.
When equal plaintexts must yield equal ciphertexts, encrypt through the
direct path on a backend that offers the flag, or keep a blind index (an
HMAC of the plaintext) in a sibling column.

.. _key-management-data-key-store:

Data Key Stores
---------------

A self-contained envelope pays one KMS round-trip per payload and carries a
couple hundred bytes of wrapped key in every payload, and its master key
cannot be rotated without rewriting the payloads that refer to it. A
:class:`Symfony\\Component\\KeyManagement\\DataKeyStoreInterface` inverts that
trade-off: it persists wrapped data keys, and each payload carries a
reference to one instead of carrying the key itself::

    use Symfony\Component\KeyManagement\StoredEnvelopeEncrypter;

    $encrypter = new StoredEnvelopeEncrypter($store);

    $envelope = $encrypter->encrypt('user.email', 'jane@example.com');
    $plaintext = $encrypter->decrypt($envelope);

The first argument is no longer a master key but a **scope**: whatever the
application decides may share a data key, typically a column, a tenant or a
purpose. Values encrypted under the same scope reuse the same key until it
is retired, and each retirement only affects payloads written afterwards,
since older payloads keep referring to the key they were written with. The
master key and the KMS client belong to the store, which records them next
to each data key so they can later be swapped.

The store hands out a
:class:`Symfony\\Component\\KeyManagement\\DataKeyHandle` rather than a
:class:`Symfony\\Component\\KeyManagement\\DataKey`: a store exists precisely to
unwrap once and encrypt many payloads, so the plaintext is retained until the
handle is released or destroyed, and wiped there. It lives for the lifetime
of the store, which means the lifetime of a process; implementations must not
persist it nor share it between processes.

The AAD binds the payload only, not the stored data key: that key is shared
by the whole scope, so binding it to the AAD of one payload would make it
unusable for the next.

Reading a Dataset Written Before the Store
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The two envelope shapes never mix inside one frame, but a dataset that is
being migrated holds both. Pass a decrypter for the self-contained format as
the second argument and both keep resolving::

    $encrypter = new StoredEnvelopeEncrypter($store, new EnvelopeEncrypter($kms));

New payloads are then written in the stored format while the ones written
before keep resolving through the KMS. Without that fallback, a
self-contained envelope is refused with a
:class:`Symfony\\Component\\KeyManagement\\Exception\\LogicException` rather than
silently mishandled.

Rewrapping and Rotating
~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface` is
the administration half of the contract, kept apart so that the encryption
path only ever sees ``DataKeyStoreInterface``:

* ``all(?string $client = null)``: walks the stored keys as
  :class:`Symfony\\Component\\KeyManagement\\StoredDataKey` instances, oldest
  first, optionally restricted to those wrapped by a given client;
* ``rewrap(string $reference, Ciphertext $wrapped, string $client)``: replaces
  the wrapping of a stored key, leaving its reference and its scope untouched
  so that payloads referring to it keep resolving;
* ``rotate(string $scope)``: retires the current key of a scope by creating a
  fresh one, which becomes current.

Moving an entire dataset to another master key, or to another provider,
therefore rewrites a handful of rows and not a single payload. In a Symfony
application, the ``key-management:rewrap-data-keys`` command does exactly
that; see :ref:`key-management-store-config` in the framework documentation.

The only store shipped for production use keeps its rows in a Doctrine DBAL
table (see :ref:`key-management-bridges`); backing another storage is a
matter of implementing the interface against it.

Backends
--------

The component ships three local backends. Cloud and remote KMS backends are
distributed as separate bridges (see :ref:`key-management-bridges` below).

Local Symmetric (libsodium)
~~~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\SodiumKms` uses
XChaCha20-Poly1305-IETF AEAD with a random 24-byte nonce. Each entry
returned by the configured key loader must be exactly 32 bytes. Generate
keys with::

    $key = sodium_crypto_aead_xchacha20poly1305_ietf_keygen();
    file_put_contents('/etc/kms/app-key', $key);

This backend is the recommended default for local development and tests
when the ``sodium`` extension is available (bundled with PHP since 7.2 but
sometimes disabled at compile time).

Local Symmetric (OpenSSL)
~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\OpenSslKms` uses AES-256-GCM with a
random 12-byte IV. Each entry must be exactly 32 bytes. Use this backend
when ``ext-sodium`` is unavailable or when you want to share keys with
systems that only speak AES.

Local Asymmetric (Sealed Box)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\SealedBoxKms` uses anonymous
public-key encryption (``sodium_crypto_box_seal``: Curve25519 +
XSalsa20-Poly1305). It enables a "Symfony Secrets"-style workflow in
self-hosted setups:

* deployments that load only the **public key** can encrypt new payloads
  (developers commit encrypted blobs to git) but cannot decrypt anything;
* deployments that load the full **keypair** (secret + public, the
  libsodium 64-byte convention) can decrypt at runtime.

Generate a keypair with::

    $keypair   = sodium_crypto_box_keypair();              // 64 bytes
    $publicKey = sodium_crypto_box_publickey($keypair);    // 32 bytes

Sealed-box ciphertexts cannot be decrypted by the encrypting party (no
authenticated tag from the recipient's side), so this backend does not
support AAD nor deterministic encryption.

Key Loaders
~~~~~~~~~~~

The three local backends source key material through a
:class:`Symfony\\Component\\KeyManagement\\KeyLoader\\KeyLoaderInterface`. Two
implementations ship with the component:

* :class:`Symfony\\Component\\KeyManagement\\KeyLoader\\InMemoryKeyLoader`:
  ``array<string, string>`` keyed by id. Useful for tests and for keys
  passed through environment variables.

* :class:`Symfony\\Component\\KeyManagement\\KeyLoader\\FilesystemKeyLoader`: reads
  files from a local directory. The key id is the file basename; an
  optional extension can be configured (``.bin``, ``.key``, ...).

To load keys from S3, FTP, Azure Blob, or any other Flysystem-compatible
filesystem, install ``symfony/flysystem-key-management``
(see :ref:`key-management-bridges`).

DSN Schemes
~~~~~~~~~~~

The matching factories accept a DSN, which is the format the FrameworkBundle
configuration uses internally:

==========================================  =======================================================================
Scheme                                       Description
==========================================  =======================================================================
``sodium://``                                XChaCha20 / keys inlined in the DSN
``sodium+dir:///path/to/keys``               XChaCha20 / keys read from a local directory
``openssl://``                               AES-256-GCM / keys inlined in the DSN
``openssl+dir:///path/to/keys``              AES-256-GCM / keys read from a local directory
``sodium-sealed-box://``                     Sealed box / keys inlined in the DSN
``sodium-sealed-box+dir:///path/to/keys``    Sealed box / keys read from a local directory
==========================================  =======================================================================

For the inline schemes, declare keys via the ``keys[<id>]=<base64>`` query
option. Both standard base64 and the URL-safe variant (RFC 4648 section 5, with or
without ``=`` padding) are accepted, so you can drop the ``urlencode()`` step
when crafting DSNs by hand:

.. code-block:: text

    sodium://?keys[app-key]=BASE64_ENCODED_32_BYTES&keys[other]=BASE64_ENCODED_32_BYTES

For the directory schemes, an optional ``ext`` query option restricts the
loader to a given file extension:

.. code-block:: text

    sodium+dir:///etc/kms?ext=.bin

That permissive decoding is exposed as
:class:`Symfony\\Component\\KeyManagement\\Base64UrlSafe`, which the bridges
share: Azure Key Vault speaks base64url for every value it exchanges and
Google Cloud KMS needs it to assemble the JWT it authenticates with.

.. _key-management-bridges:

Bridges
-------

AWS KMS
~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/aws-key-management

It implements both
:class:`Symfony\\Component\\KeyManagement\\EncrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface`, backed by the
lightweight `async-aws/kms`_ client. Encryption, decryption and data-key
generation never expose the master key: AWS performs them server-side::

    use AsyncAws\Core\Configuration;
    use AsyncAws\Kms\KmsClient;
    use Symfony\Component\KeyManagement\Bridge\AwsKms\AwsKms;

    $kms = new AwsKms(new KmsClient(Configuration::create([
        'region' => 'eu-west-1',
    ])));

    // Use the key ARN, key id, or alias.
    $ciphertext = $kms->encrypt('alias/app-key', 'hello world');

DSN scheme:

.. code-block:: text

    aws-kms://[<accessKey>:<secretKey>@]<host>[:<port>]?region=<region>[&session_token=<token>&scheme=http]

The host ``default`` selects the public AWS endpoint for the region; any
other host is treated as a custom endpoint (LocalStack, VPC endpoint, ...).
Leaving the credentials out lets ``async-aws`` walk the standard provider
chain (env vars, instance profile, ...). The ``scheme`` option only applies
to custom hosts and defaults to ``https``; pass ``scheme=http`` when
targeting a local sandbox such as LocalStack
(``aws-kms://localhost:4566?region=eu-west-1&scheme=http``). Combining
``scheme`` with ``host=default`` is rejected with an explicit
``InvalidArgumentException``.

AWS KMS exposes AAD as ``EncryptionContext`` (``array<string, string>``).
To stay compatible with the opaque-bytes contract of
``EncrypterInterface``, this bridge stores the AAD as a single
base64-encoded entry under a conventional key. Cross-bridge interoperability
is therefore not guaranteed when AAD is non-empty.

Azure Key Vault
~~~~~~~~~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/azure-keyvault-key-management

It implements
:class:`Symfony\\Component\\KeyManagement\\EncrypterInterface`,
:class:`Symfony\\Component\\KeyManagement\\DecrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface`, backed by the
`Azure Key Vault REST API`_ (and Managed HSM). Encryption, decryption and
data-key wrapping never expose the master key: Azure performs them
server-side::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\KeyManagement\Bridge\AzureKeyVault\AzureKeyVault;
    use Symfony\Component\KeyManagement\Bridge\AzureKeyVault\ClientCredentialsTokenProvider;

    $client = HttpClient::createForBaseUri('https://my-vault.vault.azure.net/');
    $tokens = new ClientCredentialsTokenProvider(
        $client,
        $_SERVER['AZURE_TENANT_ID'],
        $_SERVER['AZURE_CLIENT_ID'],
        $_SERVER['AZURE_CLIENT_SECRET'],
    );

    $kms = new AzureKeyVault($client, $tokens);

DSN scheme:

.. code-block:: text

    azure-keyvault://<clientId>:<clientSecret>@<vault-name>.vault.azure.net?tenant=<tenantId>[&algorithm=...&wrap_algorithm=...&api_version=...&audience=...]

The host is the full vault DNS (use ``<name>.managedhsm.azure.net`` for
Managed HSM). The audience is inferred from the host suffix
(``.managedhsm.azure.net``, ``.managedhsm.usgovcloudapi.net``,
``.managedhsm.azure.cn`` selects the Managed HSM audience; everything else
falls back to ``https://vault.azure.net/.default``). Override with
``audience=...`` for sovereign clouds (US gov / China) whose audience
differs from the commercial one. ``api_version`` defaults to ``7.4``.

The default algorithm is ``RSA-OAEP-256`` for both ``encrypt``/``decrypt``
and ``wrapKey``; ``A256GCM`` (AEAD with native AAD support) and ``A256KW``
are accepted on Managed HSM by passing them explicitly.

Decrypt is forward-compatible across algorithm rotations: AEAD ciphertexts
embed their algorithm in the blob (``ALG.iv.tag.value``), so a bridge
reconfigured from AEAD to RSA can still decrypt blobs written under the
previous AEAD setup.

Authentication uses Azure AD's ``client_credentials`` grant by default. To
plug Managed Identity, Workload Identity, or any other flow, implement
:class:`Symfony\\Component\\KeyManagement\\Bridge\\AzureKeyVault\\TokenProviderInterface`
and pass it to ``AzureKeyVault`` directly.

Google Cloud KMS
~~~~~~~~~~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/google-cloud-key-management

It implements
:class:`Symfony\\Component\\KeyManagement\\EncrypterInterface`,
:class:`Symfony\\Component\\KeyManagement\\DecrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface`, backed by the
`Google Cloud KMS REST API`_::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\KeyManagement\Bridge\GoogleCloudKms\GoogleCloudKms;
    use Symfony\Component\KeyManagement\Bridge\GoogleCloudKms\ServiceAccountTokenProvider;

    $client = HttpClient::createForBaseUri('https://cloudkms.googleapis.com/v1/');
    $tokens = ServiceAccountTokenProvider::fromJsonFile($client, '/path/to/service-account.json');

    $kms = new GoogleCloudKms($client, $tokens);

    // The key id is the full Cloud KMS resource name; append
    // /cryptoKeyVersions/<n> to pin to a version.
    $ciphertext = $kms->encrypt(
        'projects/my-project/locations/global/keyRings/app/cryptoKeys/master',
        'hello world',
    );

DSN scheme:

.. code-block:: text

    gcp-kms://default?credentials=/path/to/service-account.json

The host ``default`` selects the public Cloud KMS endpoint; any other host
is treated as a custom endpoint. The ``credentials`` option must point at
a service-account JSON key file.

Authentication uses the JWT-bearer flow by default: the bundled
:class:`Symfony\\Component\\KeyManagement\\Bridge\\GoogleCloudKms\\ServiceAccountTokenProvider`
signs an assertion with the service-account RSA private key (RS256) and
exchanges it for an access token. For Application Default Credentials, the
GCE/GKE/Cloud Run metadata server, Workload Identity Federation, or any
other flow, implement
:class:`Symfony\\Component\\KeyManagement\\Bridge\\GoogleCloudKms\\TokenProviderInterface`.

Cloud KMS does not expose a ``GenerateDataKey`` primitive. The bridge mirrors
the Azure pattern: a fresh DEK is drawn locally with ``random_bytes()`` and
wrapped via the regular ``:encrypt`` endpoint.

HashiCorp Vault Transit
~~~~~~~~~~~~~~~~~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/vault-key-management

It implements both
:class:`Symfony\\Component\\KeyManagement\\EncrypterInterface` and
:class:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface`, backed by the
`Vault Transit secret engine`_::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\KeyManagement\Bridge\Vault\TransitKms;

    $kms = new TransitKms(
        HttpClient::createForBaseUri('https://vault.example.com:8200/v1/'),
        $_SERVER['VAULT_TOKEN'],
    );

    $ciphertext = $kms->encrypt('app-key', 'hello world');
    // Ciphertext blob looks like "vault:v1:..."

DSN scheme:

.. code-block:: text

    vault-transit://<token>@<host>[:<port>][/<path>][?mount=<mount>&namespace=<ns>&scheme=http]

Default mount point is ``transit``. The ``namespace`` option maps to
Vault's ``X-Vault-Namespace`` header (Vault Enterprise multi-tenancy). The
``scheme`` option defaults to ``https``; pass ``scheme=http`` when running
Vault locally in dev mode.

The ``$aad`` argument is forwarded as Vault's ``context`` parameter (HKDF
context, base64-encoded). It works for keys created with ``derived=true``
and is rejected on plain symmetric keys. Vault Transit does not support
deterministic encryption per call; the ``convergent_encryption`` feature is
set at key creation, not negotiated per request.

Flysystem
~~~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/flysystem-key-management

It exposes a
:class:`Symfony\\Component\\KeyManagement\\Bridge\\Flysystem\\FlysystemKeyLoader`
that lets the local backends source their key material through any
`league/flysystem`_ reader (S3, FTP, SFTP, Azure Blob, Google Cloud
Storage, ...). It also registers three DSN schemes (``sodium+fly://``,
``openssl+fly://`` and ``sodium-sealed-box+fly://``) that the
FrameworkBundle picks up automatically.

Doctrine DBAL
~~~~~~~~~~~~~

Install the bridge:

.. code-block:: terminal

    $ composer require symfony/doctrine-dbal-key-management

It provides two things. The first is a
:class:`Symfony\\Component\\KeyManagement\\Bridge\\DoctrineDbal\\EncryptedType`
that decorates any Doctrine DBAL ``Type`` with column-level envelope
encryption. See :ref:`key-management-doctrine` in the framework documentation.

The second is a
:class:`Symfony\\Component\\KeyManagement\\Bridge\\DoctrineDbal\\DataKeyStore`,
the production implementation of
:class:`Symfony\\Component\\KeyManagement\\RewrappableDataKeyStoreInterface`. It
holds the wrapped data keys in a table of five columns and nothing more::

    use Symfony\Component\KeyManagement\Bridge\DoctrineDbal\DataKeyStore;
    use Symfony\Component\KeyManagement\StoredEnvelopeEncrypter;

    $store = new DataKeyStore($connection, $clients, 'aws', 'alias/app-key');
    $store->createTable();

    $encrypter = new StoredEnvelopeEncrypter($store);

``$clients`` is a PSR-11 container of KMS clients indexed by name, each one a
``DataKeyGeneratorInterface``. Every row records which of them wrapped it,
which is what makes a provider migration possible while the application keeps
running.

==================  =================  ==============================================================
Column               Type               Role
==================  =================  ==============================================================
``id``               ``BINARY(16)``     A UUIDv7, primary key, and the reference recorded by payloads
``scope``            ``VARCHAR(191)``   The unit a data key is shared over
``key_material``     ``BLOB``           The wrapped data key
``master_key_id``    ``VARCHAR(255)``   The master key that wrapped it
``client``           ``VARCHAR(64)``    The configured KMS client able to unwrap it
==================  =================  ==============================================================

There is no timestamp column on purpose: a UUIDv7 carries its creation
instant and sorts chronologically, so the newest row of a scope is its
current key and the retirement age is read back from the reference itself.

A scope is resolved once and then held, unwrapped, for as long as the store
lives, which is what spares the round trips. That also means the store holds
plaintext key material in memory: ``forget()`` drops it, and the Symfony
wiring calls it between two units of work through the ``kernel.reset`` tag.

This bridge requires Doctrine DBAL >= 4.5 (4.3 lifted the ``final``
constructor on ``Type``, which the encrypted type needs, and 4.5 brought the
schema editor the data key store uses).

.. _key-management-wire-format:

The Envelope Wire Format
------------------------

The :class:`Symfony\\Component\\KeyManagement\\Envelope` byte framing is owned by
an :class:`Symfony\\Component\\KeyManagement\\EnvelopeFormat`, and the leading
byte of every frame is its id, so a reader resolves the format before parsing
anything else. Two formats ship:

.. code-block:: text

    # SelfContainedFormat, id 0x01
    [0x01][2B keyIdLen][keyId][2B wrappedLen][wrappedDek][IV][tag][ciphertext]

    # StoredFormat, id 0x02
    [0x02][2B referenceLen][reference][IV][tag][ciphertext]

Both map to AES-256-GCM with a 32-byte data key, a 12-byte IV and a 16-byte
tag. That key length also matches the cross-bridge data-key intersection
(see :class:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface`), so
envelopes round-trip through every shipped backend.

Ids are a wire contract: once a format ships, its id and its layout are
frozen. A change of cipher, of lengths or of layout is a new format with a
new id rather than an edit to an existing one.

Formats are not a configuration point either. Each encrypter owns the one
matching its shape (``EnvelopeEncrypter`` writes ``SelfContainedFormat``,
``StoredEnvelopeEncrypter`` writes ``StoredFormat``), and
:method:`Symfony\\Component\\KeyManagement\\EnvelopeFormat::fromId` resolves
nothing but the formats shipped with the component, so a payload written
anywhere reads back anywhere. A new layout is therefore added to the
component, with its own id, rather than plugged in by an application.

Serializer Integration
----------------------

When the :doc:`Serializer component </serializer>` is installed,
the FrameworkBundle registers
:class:`Symfony\\Component\\KeyManagement\\Serializer\\EnvelopeNormalizer`
automatically. It encodes an ``Envelope`` to (and from) a base64-encoded
string so it travels through any structured format (JSON, XML, YAML, ...)
without needing per-property normalization. Useful when an envelope is a
field of a Messenger message, an API resource, or any class going through
the serializer.

Memory Wiping
-------------

Plaintext data keys returned by
:method:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface::generateDataKey`
are wrapped in a :class:`Symfony\\Component\\KeyManagement\\DataKey` instance whose
plaintext is **not** exposed as a public field. Access is mediated by
:method:`Symfony\\Component\\KeyManagement\\DataKey::use`, which passes the bytes to
a caller-provided closure and releases the reference as soon as the closure
returns or throws::

    $dataKey = $kms->generateDataKey('app-key', 32);

    $ciphertext = $dataKey->use(function (string $dek): string {
        return openssl_encrypt($plaintext, 'aes-256-gcm', $dek, \OPENSSL_RAW_DATA, $iv, $tag);
    });

When the ``sodium`` extension is available, ``sodium_memzero()`` clears the
underlying PHP zval before the reference is dropped. Without sodium, PHP
offers no in-place zeroing primitive, so the property is just nulled and
the engine releases the buffer.

PHP strings are reference-counted with copy-on-write, so a consumer that
returns the plaintext, or keeps it anywhere, leaves the wipe with nothing to
wipe. Pass the parameter straight to the crypto primitives and let it go.

A :class:`Symfony\\Component\\KeyManagement\\DataKeyHandle` is the opposite
trade-off, and the one a data key store hands out: it takes a buffer of its
own out of the ``DataKey`` (so the ``DataKey`` still wipes what it held) and
keeps the plaintext usable across calls until ``release()`` or destruction.

This is what the envelope encrypters already do internally; you only have to
think about it if you call ``generateDataKey()`` yourself.

Testing
-------

Two in-memory doubles live in the ``Test\`` namespace, for test fixtures
only:

* :class:`Symfony\\Component\\KeyManagement\\Test\\InMemoryKms`: a no-crypto
  ``EncrypterInterface``/``DecrypterInterface``/``DataKeyGeneratorInterface``
  whose ciphertext blob is the plaintext behind a marker embedding the key id
  and the AAD. Decrypting under a different key id or AAD fails, which catches
  key-routing and AAD-binding bugs; feeding a plaintext back into ``decrypt()``
  fails too.

* :class:`Symfony\\Component\\KeyManagement\\Test\\InMemoryDataKeyStore`: an
  array-backed ``RewrappableDataKeyStoreInterface`` that wraps its data keys
  through a real KMS client and orders its UUIDv7 references the way a
  database would. Its ``$maxAgeSeconds`` argument accepts ``0`` to rotate on
  every call, which is how retirement is exercised in a test::

      use Symfony\Component\KeyManagement\StoredEnvelopeEncrypter;
      use Symfony\Component\KeyManagement\Test\InMemoryDataKeyStore;

      $encrypter = new StoredEnvelopeEncrypter(new InMemoryDataKeyStore());

.. _async-aws/kms: https://async-aws.com/
.. _Azure Key Vault REST API: https://learn.microsoft.com/rest/api/keyvault/
.. _Google Cloud KMS REST API: https://cloud.google.com/kms/docs/reference/rest
.. _Vault Transit secret engine: https://developer.hashicorp.com/vault/docs/secrets/transit
.. _league/flysystem: https://flysystem.thephpleague.com/
