Testing Code That Uses a KMS
============================

Three doubles live in the ``Test\`` namespace of the component, for test
fixtures only. None of them reaches a provider.

.. include:: /key_management/_experimental.rst.inc

Encrypting Without a Provider
-----------------------------

:class:`Symfony\\Component\\KeyManagement\\Test\\InMemoryKms` performs no
cryptography: its ciphertext is the plaintext behind a marker holding the
instance name, the key id and the AAD. Decrypting under a different key id
or AAD fails, and so does feeding a plaintext back into ``decrypt()``,
which catches key-routing and AAD-binding bugs.

Each instance carries a name, random unless given, so two instances refuse
each other's ciphertexts the way two providers do, and two instances given
the same name stand for the same provider. That is what makes it possible
to test client routing, a provider migration or a composite client::

    use Symfony\Component\KeyManagement\Test\InMemoryKms;

    $aws = new InMemoryKms('aws');
    $azure = new InMemoryKms('azure');

    $azure->decrypt($aws->encrypt('app-key', 'secret'));   // throws

Storing Data Keys Without a Database
------------------------------------

:class:`Symfony\\Component\\KeyManagement\\Test\\InMemoryDataKeyStore` is
an array-backed ``RewrappableDataKeyStoreInterface`` that wraps its data
keys through any KMS client and orders its UUIDv7 references the way a
database would::

    use Symfony\Component\KeyManagement\StoredEnvelopeEncrypter;
    use Symfony\Component\KeyManagement\Test\InMemoryDataKeyStore;

    $encrypter = new StoredEnvelopeEncrypter(new InMemoryDataKeyStore());

Its ``$maxAgeSeconds`` argument accepts ``0`` to replace the key on every
call. For anything finer, it reads the time from the global clock, so
:ref:`ClockSensitiveTrait <clock_writing-tests>` can move time forward and
watch a key be replaced at its real age.

Taking a Provider Down
----------------------

:class:`Symfony\\Component\\KeyManagement\\Test\\SwitchableKms` answers a
different question: what the application does while a provider is down. It
wraps a client and throws the exception it was given, instead of answering,
while its ``$down`` property is ``true``. Its ``$calls`` and ``$keyIds``
properties record what it was asked::

    use Symfony\Component\DependencyInjection\ServiceLocator;
    use Symfony\Component\KeyManagement\CompositeKms;
    use Symfony\Component\KeyManagement\Test\InMemoryKms;
    use Symfony\Component\KeyManagement\Test\SwitchableKms;

    $aws = new SwitchableKms(new InMemoryKms('aws'));
    $kms = new CompositeKms(new ServiceLocator([
        'aws' => fn () => $aws,
        'azure' => fn () => new InMemoryKms('azure'),
    ]), ['aws' => null, 'azure' => 'backup']);

    $ciphertext = $kms->encrypt('app', 'secret');

    $aws->down = true;
    $kms->decrypt($ciphertext); // still "secret", read through "azure"
