KMS Backends
============

Each backend is reached through the DSN of a client, as
:doc:`/key_management/clients` explains. This page documents what each one
takes and what sets it apart. The classes are shown too, since a client
wired by hand and declared with a ``service://`` DSN builds them itself.

.. include:: /key_management/_experimental.rst.inc

Local Backends
--------------

Three backends keep the key material where the application can reach it,
which is what makes them suitable for development, tests and self-hosted
setups, and what sets them apart from a real KMS.

libsodium
~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\SodiumKms` uses
XChaCha20-Poly1305-IETF with a random 24-byte nonce. Every key the loader
returns must be exactly 32 bytes::

    $key = sodium_crypto_aead_xchacha20poly1305_ietf_keygen();
    file_put_contents('/etc/kms/app-key', $key);

This is the recommended local backend when ``ext-sodium`` is available. It
ships with PHP but is sometimes disabled at compile time.

OpenSSL
~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\OpenSslKms` uses
AES-256-GCM with a random 12-byte nonce, and the same 32-byte keys. Use it
when ``ext-sodium`` is unavailable, or to share keys with systems that only
speak AES.

Because its nonces are random, NIST SP 800-38D limits a key to 2^32
encryptions, the data keys it wraps included. Prefer libsodium for a key
that encrypts more values than that.

Sealed Box
~~~~~~~~~~

:class:`Symfony\\Component\\KeyManagement\\Local\\SealedBoxKms` uses
anonymous public-key encryption (``sodium_crypto_box_seal``: Curve25519
with XSalsa20-Poly1305). It gives a workflow close to
:ref:`Symfony's secrets vault <configuration-secrets>` in a self-hosted
setup:

* a deployment loading only the **public key** can encrypt new payloads,
  so developers can commit encrypted blobs, but can decrypt nothing;
* a deployment loading the full **keypair**, secret and public in the
  64-byte libsodium convention, can decrypt at runtime.

Generate a keypair with::

    $keypair = sodium_crypto_box_keypair();              // 64 bytes
    $publicKey = sodium_crypto_box_publickey($keypair);  // 32 bytes

A sealed box cannot be opened by whoever sealed it, since the sender
authenticates nothing. This backend therefore doesn't support AAD. It
doesn't authenticate the writer either. Anyone holding the public key can
create a ciphertext that the backend decrypts.

Key Loaders
~~~~~~~~~~~

The three local backends read their key material through a
:class:`Symfony\\Component\\KeyManagement\\KeyLoader\\KeyLoaderInterface`.
Two implementations ship:

:class:`Symfony\\Component\\KeyManagement\\KeyLoader\\InMemoryKeyLoader`
    An ``array<string, string>`` indexed by key id. For tests, and for keys
    passed through environment variables.

:class:`Symfony\\Component\\KeyManagement\\KeyLoader\\FilesystemKeyLoader`
    Reads files from a local directory. The key id is the file basename; an
    extension can be configured (``.bin``, ``.key``, ...).

To read keys from S3, FTP, Azure Blob or anything else Flysystem supports,
see :ref:`key-management-flysystem`.

.. _key-management-local-dsn:

DSN Options
~~~~~~~~~~~

The inline schemes take the keys as ``keys[<id>]=<base64>`` query options:

.. code-block:: text

    sodium://?keys[app-key]=BASE64_ENCODED_32_BYTES&keys[other]=BASE64_ENCODED_32_BYTES

Both standard base64 and the URL-safe variant of RFC 4648 section 5 are
accepted, with or without padding, so a DSN written by hand needs no
``urlencode()`` step. That permissive decoding is available on its own as
:class:`Symfony\\Component\\KeyManagement\\Base64UrlSafe`.

The directory schemes take an optional ``ext`` option restricting the
loader to one file extension:

.. code-block:: text

    sodium+dir:///etc/kms?ext=.bin

.. _key-management-bridges:

AWS KMS
-------

Backed by the lightweight `async-aws/kms`_ client. Encryption, decryption
and data key creation all happen inside AWS; the master key never reaches
the application::

    use AsyncAws\Core\Configuration;
    use AsyncAws\Kms\KmsClient;
    use Symfony\Component\KeyManagement\Bridge\AwsKms\AwsKms;

    $kms = new AwsKms(new KmsClient(Configuration::create([
        'region' => 'eu-west-1',
    ])));

    // a key ARN, a key id or an alias
    $ciphertext = $kms->encrypt('alias/app-key', 'hello world');

DSN scheme:

.. code-block:: text

    aws-kms://[<accessKey>:<secretKey>@]<host>[:<port>]?region=<region>[&session_token=<token>&scheme=http&timeout=<seconds>&max_duration=<seconds>]

The host ``default`` selects the public AWS endpoint for the region; any
other host is a custom endpoint, such as LocalStack or a VPC endpoint.
Leaving the credentials out lets ``async-aws`` walk its usual provider
chain: environment variables, instance profile and so on. The ``scheme``
option applies to custom hosts only and defaults to ``https``; pass
``scheme=http`` for a local sandbox
(``aws-kms://localhost:4566?region=eu-west-1&scheme=http``). Combining it
with the ``default`` host is refused.

Every ciphertext records the key ARN that AWS reported rather than the id
it was given. An alias moved to a new key, which is how a rotation is
usually performed by hand, therefore leaves older ciphertexts readable:
AWS rejects a ``Decrypt`` naming a key other than the one that encrypted
the blob.

AWS exposes AAD as its ``EncryptionContext``, an ``array<string, string>``.
To keep the opaque-bytes contract of ``EncrypterInterface``, this bridge
stores the AAD as one base64-encoded entry under a conventional key. A
non-empty AAD is therefore not interchangeable with another provider.

Azure Key Vault
---------------

Backed by the `Azure Key Vault REST API`_, Managed HSM included::

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

    azure-keyvault://<clientId>:<clientSecret>@<vault-name>.vault.azure.net?tenant=<tenantId>[&algorithm=...&wrap_algorithm=...&api_version=...&audience=...&timeout=<seconds>&max_duration=<seconds>]

The host is the full vault DNS name; use ``<name>.managedhsm.azure.net``
for Managed HSM. The audience is inferred from that suffix, and
``audience=...`` overrides it for the sovereign clouds whose audience
differs from the commercial one. ``api_version`` defaults to ``7.4``.

The default algorithm is ``RSA-OAEP-256`` for both encryption and key
wrapping. ``A256GCM``, which supports AAD natively, and ``A256KW`` are
accepted on Managed HSM when named explicitly. Decryption survives an
algorithm change, because an AEAD blob carries its algorithm
(``ALG.iv.tag.value``): a bridge reconfigured from AEAD to RSA still reads
what it wrote.

A key id is either ``<name>`` or ``<name>/<version>``, and both segments
are validated against what Azure accepts, so a malformed id such as
``../secrets`` is refused rather than normalized into another vault path.

Azure uses the latest version of a key when a request names none, which
once meant that a rotation broke every ciphertext written before it. Every
ciphertext and every wrapped data key now records a versioned key id: the
version Azure reports for a versionless write, or the one the caller
pinned. A response naming another key, or another version, fails the write.

Authentication uses the ``client_credentials`` grant by default. For
Managed Identity, Workload Identity or any other flow, implement
:class:`Symfony\\Component\\KeyManagement\\Bridge\\AzureKeyVault\\TokenProviderInterface`
and pass it in. A provider is expected to cache its token, which is why the
interface also has ``invalidateToken()``; see
:ref:`key-management-token-refresh`.

Google Cloud KMS
----------------

Backed by the `Google Cloud KMS REST API`_::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\KeyManagement\Bridge\GoogleCloudKms\GoogleCloudKms;
    use Symfony\Component\KeyManagement\Bridge\GoogleCloudKms\ServiceAccountTokenProvider;

    $client = HttpClient::createForBaseUri('https://cloudkms.googleapis.com/v1/');
    $tokens = ServiceAccountTokenProvider::fromJsonFile(
        $client,
        '/path/to/service-account.json',
    );

    $kms = new GoogleCloudKms($client, $tokens);

    // the key id is the full resource name; append
    // /cryptoKeyVersions/<n> to pin a version
    $ciphertext = $kms->encrypt(
        'projects/my-project/locations/global/keyRings/app/cryptoKeys/master',
        'hello world',
    );

DSN scheme:

.. code-block:: text

    gcp-kms://default?credentials=/path/to/service-account.json[&timeout=<seconds>&max_duration=<seconds>]

The host ``default`` selects the public endpoint; any other host is a
custom one. ``credentials`` points at a service-account JSON key file.

Authentication uses the JWT-bearer flow by default: the bundled
:class:`Symfony\\Component\\KeyManagement\\Bridge\\GoogleCloudKms\\ServiceAccountTokenProvider`
signs an assertion with the service-account RSA key and exchanges it for an
access token. For Application Default Credentials, the metadata server of
GCE, GKE or Cloud Run, or Workload Identity Federation, implement
:class:`Symfony\\Component\\KeyManagement\\Bridge\\GoogleCloudKms\\TokenProviderInterface`.

Cloud KMS has no ``GenerateDataKey`` primitive. The bridge does what the
Azure one does: it draws a data key locally with ``random_bytes()`` and
wraps it through the regular encryption endpoint.

.. _key-management-token-refresh:

Refreshing a Rejected Token
---------------------------

A cached token that is revoked, or rejected for any other reason, would
keep failing every call until it expires. The Azure and Google bridges
therefore call ``invalidateToken()`` when the provider answers ``401``, ask
for a token again, and retry once if the new token differs. A ``403`` is
reported as it is: both vendors document it as a permission or firewall
denial, which another token does not change.

A provider that caches nothing leaves the method empty. One with a cache
drops the entry only while it still holds the rejected token, so a token
another caller has already refreshed survives::

    public function invalidateToken(#[\SensitiveParameter] string $token): void
    {
        if ($this->cachedToken === $token) {
            $this->cachedToken = null;
        }
    }

The method only evicts a cached value. It must not revoke the token
remotely nor log it, and a cache shared between processes does not need to
compare and delete atomically: the worst case is one extra token request.

HashiCorp Vault Transit
-----------------------

Backed by the `Vault Transit secret engine`_::

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\KeyManagement\Bridge\HashiCorpVault\TransitKms;

    $kms = new TransitKms(
        HttpClient::createForBaseUri('https://vault.example.com:8200/v1/'),
        $_SERVER['VAULT_TOKEN'],
    );

    $ciphertext = $kms->encrypt('app-key', 'hello world');
    // the blob looks like "vault:v1:..."

DSN scheme:

.. code-block:: text

    hashicorp-vault-transit://<token>@<host>[:<port>][/<path>][?mount=<mount>&namespace=<ns>&scheme=http&timeout=<seconds>&max_duration=<seconds>]

The mount point defaults to ``transit``. ``namespace`` maps to Vault's
``X-Vault-Namespace`` header, for Vault Enterprise. ``scheme`` defaults to
``https``; pass ``scheme=http`` for a local dev server.

Every request carries a ``context``, Vault's HKDF input. It is the AAD
prefixed with a fixed label of the bridge, so that arbitrary bytes, an
empty AAD included, always give a distinct and non-empty context, which
is what a key created with ``derived=true`` requires. Such a key is
therefore usable when no AAD is passed.

A non-empty AAD is refused on a plain symmetric key, because Vault
ignores a context on a non-derived key rather than rejecting it, which
would let a ciphertext bound to one tenant decrypt under another. The
bridge therefore reads the key configuration once per key whenever the
AAD is non-empty, and throws
:class:`Symfony\\Component\\KeyManagement\\Exception\\UnsupportedOperationException`
when the key cannot enforce it. A key created with ``derived=true`` is also
what a data key store needs to turn
:ref:`bind_wrapping_context <key-management-store-binding>` on.

That read is a capability of its own, so a least-privilege token limited to
the encryption endpoints stops working as soon as an AAD reaches the bridge,
which happens with direct calls or with ``bind_wrapping_context`` on, not
with an envelope encrypter alone:

.. code-block:: text

    path "transit/encrypt/*"           { capabilities = ["update"] }
    path "transit/decrypt/*"           { capabilities = ["update"] }
    path "transit/datakey/plaintext/*" { capabilities = ["update"] }
    # only needed with AAD, to tell derived keys from non-derived ones
    path "transit/keys/*"              { capabilities = ["read"] }

With ``convergent_encryption=true``, equal plaintexts encrypted with the
same AAD give equal ciphertexts, and each AAD value is a convergence scope
of its own.

KMIP
----

Backed by any server speaking `KMIP`_, the OASIS key management protocol,
over TLS with client-certificate authentication. The server holds the
master key and performs the encryption::

    use Symfony\Component\KeyManagement\Bridge\Kmip\KmipKmsFactory;
    use Symfony\Component\KeyManagement\Dsn;

    $kms = (new KmipKmsFactory())->create(Dsn::fromString(
        'kmip://kmip.example.com?cert=/run/secrets/client.crt'
        .'&key=/run/secrets/client.key&version=2.0&ca=/run/secrets/ca.crt'
    ));

    // the key id is the KMIP Unique Identifier of an active symmetric key
    $ciphertext = $kms->encrypt($keyId, 'hello world');

DSN scheme:

.. code-block:: text

    kmip://[<user>:<password>@]<host>[:<port>]?cert=<path>&key=<path>&version=<version>[&ca=<path>][&peer_name=<name>][&passphrase=<passphrase>][&iv_length=12][&timeout=10]

``cert``, ``key`` and ``version`` are required: the client certificate and
its private key, and the KMIP protocol version to speak (``1.0`` to
``2.1``). ``ca`` and ``peer_name`` pin the server certificate,
``passphrase`` unlocks an encrypted private key, and ``timeout`` is the
connection timeout in seconds.

Provision an active symmetric key for AES-GCM, with Encrypt and Decrypt in
its Cryptographic Usage Mask, and use its Unique Identifier as the key id.

A whole KMIP message must fit in 16 MiB, headers included, so direct
encryption is capped well below that. Envelope encryption is not, since
only the data key crosses the wire. Each operation opens its own TLS
connection, which a :doc:`data key store </key_management/store>` makes far
less frequent.

.. _key-management-flysystem:

Flysystem
---------

Provides a
:class:`Symfony\\Component\\KeyManagement\\Bridge\\Flysystem\\FlysystemKeyLoader`
that lets the local backends read their key material through any
`league/flysystem`_ adapter: S3, FTP, SFTP, Azure Blob, Google Cloud
Storage and so on. It also registers three DSN schemes,
``sodium+fly://``, ``openssl+fly://`` and ``sodium-sealed-box+fly://``,
which ``KeyManagementBundle`` picks up automatically.

.. warning::

    An object store is a place to keep key material, not a key manager.
    Even behind ACLs, audit logs and server-side access control, the
    application still fetches the key material over the network and holds
    it in memory, which a real KMS never allows: AWS KMS, Azure Key Vault,
    Google Cloud KMS and Vault Transit perform the cryptographic
    operations themselves and never hand out the master key. Use this
    bridge for self-hosted and development setups, not as a replacement for
    one of those providers.

The loader keeps each key in memory once it has read it, so a remote store
is read once per key rather than once per operation. A failed read is not
kept, so the next lookup tries again. ``reset()`` drops everything; a
loader registered as a service does that between requests through the
``kernel.reset`` tag, and a client built from a DSN asks for the same with
``reset=1``:

.. code-block:: text

    sodium+fly://keys.storage/keys?ext=.key&reset=1

Without that option the keys stay in memory across requests. The loader
cannot be serialized, and a clone of one starts empty while the original
keeps what it read.

.. _async-aws/kms: https://async-aws.com/
.. _Azure Key Vault REST API: https://learn.microsoft.com/rest/api/keyvault/
.. _Google Cloud KMS REST API: https://cloud.google.com/kms/docs/reference/rest
.. _Vault Transit secret engine: https://developer.hashicorp.com/vault/docs/secrets/transit
.. _KMIP: https://www.oasis-open.org/committees/kmip/
.. _league/flysystem: https://flysystem.thephpleague.com/
