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

An envelope decrypts the same way whatever backend produced it, as long as
the backend decrypting it reaches the same master key. Swapping a local
backend in development for AWS KMS in production therefore needs no
re-encryption.

The key id an envelope carries is the one the backend reported, which is
not always the one that was asked for. AWS KMS resolves an alias to a key
ARN and Azure Key Vault pins the version it used, both of which are
recorded so that the envelope stays readable after the alias moves on. A
reader checking ``$envelope->keyId`` to refuse a payload copied from
another column or another tenant is reading that resolved value.

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

The format id is authenticated. It is prefixed to the authenticated data of
the local cipher, when sealing and when opening alike, so a frame assembled
from the pieces of the other format fails its tag check whatever AAD the
caller passes. Both layouts otherwise agree on their lengths, which would
make such a frame parse cleanly.

A leading byte no shipped format claims is refused exactly where a
malformed frame is, and with the same error, so a reader cannot tell an
attempt at downgrading a payload from a corrupt column.

Ids are a wire contract: once a format ships, its id and its layout are
frozen. A different cipher, different lengths or a different layout is a
new format with a new id, never an edit to an existing one. Formats are not
a configuration point either. Each encrypter owns the one matching its
shape, and
:method:`Symfony\\Component\\KeyManagement\\EnvelopeFormat::fromId`
resolves nothing but the formats shipped with the component, so a payload
written anywhere reads back anywhere.

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

    $ciphertext = $dataKey->use(function (string $dek) use ($plaintext, $iv, &$tag) {
        return openssl_encrypt(
            $plaintext, 'aes-256-gcm', $dek, \OPENSSL_RAW_DATA, $iv, $tag
        );
    });

With ``ext-sodium`` available, ``sodium_memzero()`` clears the buffer
before the reference is dropped, but only while nothing else holds it: PHP
declines to zero a string another variable shares rather than blank it
under that holder. Without sodium there is no in-place zeroing primitive at
all, so the holder is dropped and the engine frees the buffer when it gets
to it.

PHP strings are reference-counted with copy-on-write, so a consumer that
returns the plaintext, or keeps it anywhere, leaves the wipe with nothing
to wipe. Pass the parameter straight to the cryptographic primitives and
let it go.

A :class:`Symfony\\Component\\KeyManagement\\DataKeyHandle` makes the
opposite trade-off, and is what a
:doc:`data key store </key_management/store>` hands out: it takes a buffer
of its own out of the ``DataKey``, so the ``DataKey`` still wipes what it
held, and keeps the plaintext usable across calls until ``release()`` or
destruction.

The envelope encrypters already do all of this. It only matters when
``generateDataKey()`` is called directly.
