Envelopes and Data Keys
=======================

An envelope is what an application stores, and a data key is what encrypts
it. Both are value objects of the component, and both make promises worth
knowing about: an envelope has a frozen byte layout, and a data key tries
to leave as little trace in memory as it can.

.. include:: /key_management/_experimental.rst.inc

Reading an Envelope Back
------------------------

:class:`Symfony\\Component\\KeyManagement\\Envelope` is
:phpclass:`Stringable`, so it is stored as bytes, and
:method:`Symfony\\Component\\KeyManagement\\Envelope::fromBytes` reads it
back::

    use Symfony\Component\KeyManagement\Envelope;

    $envelope = $this->envelopeEncrypter->encrypt('app-key', $largePayload);
    file_put_contents('/var/data/payload.bin', $envelope);

    $payload = $this->envelopeDecrypter->decrypt(
        Envelope::fromBytes(file_get_contents('/var/data/payload.bin'))
    );

An envelope has the same layout no matter which backend produced it, and
any backend reaching the same master key decrypts it. Switching from a
local backend in development to AWS KMS in production therefore needs no
code change.

``$envelope->keyId`` holds the key id that the backend reported, which is
not always the one that was asked for: AWS KMS resolves an alias to a key
ARN and Azure Key Vault pins the version it used.

A self-contained envelope tells the backend which master key to decrypt its
data key with. A database writer can therefore create one under any master key
that your credentials can use. On AWS, that includes the keys of other accounts
when your IAM policy allows ``kms:Decrypt`` on every resource, so list your own
key ARNs instead. On Google Cloud, the owner of a key can let your service
account use it without any change on your side. The code that decrypts envelopes
can refuse the ones written under another key::

    $keyId = $envelope->keyId;
    if (null !== $keyId && !\in_array($keyId, $expectedKeyIds, true)) {
        throw new \RuntimeException('This payload was written under another key.');
    }

List the key ids that your backend reports, as explained above. Add the new ARN
before you move an AWS alias, and compare only the name part of an Azure
``name/version`` id. The check doesn't help with a sealed box, since anyone
holding its public key can write under the expected key id. Nor does it help
with a :ref:`composite client <key-management-composite>`: its envelopes
record the key id passed to the call, while each member reads the key id
stored in its own wrapping. Encrypted Doctrine columns don't run it either.
Authenticate the value separately when writes must be trusted. A payload
that refers to a :doc:`data key store </key_management/store>` doesn't have
this problem, since the store checks every key with a secret.

.. _key-management-wire-format:

The Wire Format
---------------

An :class:`Symfony\\Component\\KeyManagement\\EnvelopeFormat` owns the byte
framing. The leading byte of every frame is the format id, so a reader
resolves the format before parsing anything else. Two formats ship:

.. code-block:: text

    # SelfContainedFormat, id 0x01
    [0x01][2B keyIdLen][keyId][2B wrappedLen][wrappedDek][IV][tag][ciphertext]

    # StoredFormat, id 0x02
    [0x02][2B referenceLen][reference][IV][tag][ciphertext]

Both use AES-256-GCM with a 32-byte data key, a 12-byte IV and a 16-byte
tag. That key length is also the one every shipped backend can produce, so
an envelope round-trips through all of them.

The format id is authenticated. So are the key id and the wrapped data key of a
self-contained frame, and the reference of a stored frame. These fields are
prefixed to the authenticated data of the local cipher, when sealing and when
opening alike. A frame assembled from the pieces of another frame then fails its
tag check, regardless of the AAD the caller passes. That includes a frame whose data key
was wrapped again, even under the same master key. A stored frame authenticates
the reference rather than the wrapping, which is what lets the store re-wrap its
keys without touching any payload.

A leading byte no shipped format claims is refused exactly where a
malformed frame is. An encrypted Doctrine column and the decrypt command
report both the same way, so an attempt at downgrading a payload looks like
a corrupt value.

Ids are a wire contract: once a format ships, its id and its layout are
frozen. A different cipher, different lengths, a different layout or
different authenticated fields need a new format with a new id. An existing
format is never edited.

Formats are not a configuration option either. Each encrypter owns the one
matching its shape, and
:method:`Symfony\\Component\\KeyManagement\\EnvelopeFormat::fromId`
resolves only the formats shipped with the component, so a payload written
by any application can be read by any other.

Sending an Envelope Through the Serializer
------------------------------------------

When the :doc:`Serializer component </serializer>` is installed, the bundle
registers
:class:`Symfony\\Component\\KeyManagement\\Serializer\\EnvelopeNormalizer`
automatically. It encodes an ``Envelope`` to and from a base64 string, so
an envelope travels through any structured format with no per-property
handling. This is what makes an envelope usable as a field of a Messenger
message or an API resource.

Wiping Keys from Memory
-----------------------

A plaintext data key returned by
:method:`Symfony\\Component\\KeyManagement\\DataKeyGeneratorInterface::generateDataKey`
comes inside a :class:`Symfony\\Component\\KeyManagement\\DataKey` whose
plaintext is not a property at all, so no dump can print it:
``var_dump()``, ``print_r()``, ``var_export()`` and the VarDumper cloner
have nothing to reach, and ``serialize()`` throws. Access goes through
:method:`Symfony\\Component\\KeyManagement\\DataKey::use`, which hands the
bytes to a closure and releases them as soon as it returns or throws::

    $dataKey = $kms->generateDataKey('app-key', 32);

    $ciphertext = $dataKey->use(function (string $key) use ($plaintext, $iv, &$tag) {
        return openssl_encrypt(
            $plaintext, 'aes-256-gcm', $key, \OPENSSL_RAW_DATA, $iv, $tag
        );
    });

With ``ext-sodium`` available, ``sodium_memzero()`` clears the buffer
before the reference is dropped, but only while no other variable holds the
same string: PHP doesn't zero a shared string, so that the other variable
isn't blanked too. Without sodium, PHP has no way to zero a string in
place, so the reference is dropped and the engine frees the buffer later.

PHP strings are reference-counted with copy-on-write, so a closure that
returns the plaintext key, or stores it anywhere, prevents the wipe. Pass
the parameter directly to the cryptographic functions and don't keep it.

A :class:`Symfony\\Component\\KeyManagement\\DataKeyHandle` makes the
opposite trade-off, and is what a
:doc:`data key store </key_management/store>` hands out: it takes a buffer
of its own out of the ``DataKey``, so the ``DataKey`` still wipes what it
held, and keeps the plaintext usable across calls until ``release()`` or
destruction.

The envelope encrypters already handle all of this, so it only matters when
you call ``generateDataKey()`` yourself.
