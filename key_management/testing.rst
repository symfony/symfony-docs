Testing Code That Uses a KMS
============================

Three doubles live in the ``Test\`` namespace of the component, for test
fixtures only. None of them needs a provider.

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
an array-backed ``RewrappableDataKeyStoreInterface`` that can wrap its data
keys through any KMS client and orders its UUIDv7 references the way a
database would::

    use Symfony\Component\KeyManagement\StoredEnvelopeEncrypter;
    use Symfony\Component\KeyManagement\Test\InMemoryDataKeyStore;

    $encrypter = new StoredEnvelopeEncrypter(new InMemoryDataKeyStore());

Its ``$maxAgeSeconds`` argument is ``null`` by default, so a key is never
replaced, and ``0`` replaces it on every call. To check that a key is
replaced at its real age, also pass a clock, its next argument: a
:class:`Symfony\\Component\\Clock\\MockClock` that the test moves forward,
or a :class:`Symfony\\Component\\Clock\\Clock`, which follows the global
clock of :ref:`ClockSensitiveTrait <clock_writing-tests>`. Without one, the
store reads the time of the system.

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
