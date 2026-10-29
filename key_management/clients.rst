Configuring the KMS Clients
===========================

A client is one way of reaching one provider. Most applications declare a
single one, as :doc:`/key_management` shows. This page covers the DSN each
provider takes, declaring several clients, and wrapping data under several
providers at once.

.. include:: /key_management/_experimental.rst.inc

.. _key-management-dsn-schemes:

DSN Schemes
-----------

Each backend has its own scheme. The package providing it must be
installed, or the DSN is reported as an unsupported scheme:

==============================  ==========================================  ======================================
Scheme                          Package                                     Backend
==============================  ==========================================  ======================================
``sodium://``                   ``symfony/key-management``                  Local XChaCha20-Poly1305 (inline keys)
``sodium+dir://``               ``symfony/key-management``                  Local XChaCha20-Poly1305 (key dir)
``openssl://``                  ``symfony/key-management``                  Local AES-256-GCM (inline keys)
``openssl+dir://``              ``symfony/key-management``                  Local AES-256-GCM (key dir)
``sodium-sealed-box://``        ``symfony/key-management``                  Local sealed box (inline keys)
``sodium-sealed-box+dir://``    ``symfony/key-management``                  Local sealed box (key dir)
``aws-kms://``                  ``symfony/aws-key-management``              AWS Key Management Service
``azure-keyvault://``           ``symfony/azure-keyvault-key-management``   Azure Key Vault / Managed HSM
``gcp-kms://``                  ``symfony/google-cloud-key-management``     Google Cloud KMS
``hashicorp-vault-transit://``  ``symfony/hashicorp-vault-key-management``  HashiCorp Vault Transit
``scaleway-kms://``             ``symfony/scaleway-key-management``         Scaleway Key Manager
``kmip://``                     ``symfony/kmip-key-management``             Any KMIP 1.4 or 2.0 server
``sodium+fly://``               ``symfony/flysystem-key-management``        XChaCha20, keys read through Flysystem
``openssl+fly://``              ``symfony/flysystem-key-management``        AES-256-GCM, keys read through Flysystem
``sodium-sealed-box+fly://``    ``symfony/flysystem-key-management``        Sealed box, keys read through Flysystem
``service://<id>``              ``symfony/key-management``                  A client the application built itself
==============================  ==========================================  ======================================

:doc:`/key_management/backends` documents the options each one takes.
Bridges reject any query option they do not know, so a typo is an error
rather than a setting silently ignored. The local schemes don't check
their options, though.

Percent-encode the credentials and the option values of a DSN when they
contain characters that have a meaning in a URL, such as the ``/`` that
AWS secret keys often contain.

``service://<id>`` is the odd one out. It names a service you registered
yourself, so a backend Symfony does not ship is declared like any other client:
it gets the tag the encrypt, decrypt and data key commands look clients up by,
an envelope encrypter of its own and the profiler decoration, none of which a
plain alias would give it. Because the service has to be known while the
container is built, this scheme is the only one that cannot come from an
environment variable.

The AWS, Azure, Google Cloud, Vault and Scaleway clients send their requests
through the ``http_client`` service of the application, with the timeouts of
that service. Without one, they use an HTTP client with a 5-second idle timeout
and a 10-second maximum duration. Their ``timeout`` and ``max_duration``
options, in seconds, override the timeouts of either client. Requests never
follow redirects, since a redirect to another host would send the credentials
and the payload again.

Local backends need their PHP extension at runtime: ``ext-openssl`` for the
``openssl`` schemes, ``ext-sodium`` for the ``sodium`` and sealed-box ones.
Envelope encryption itself always needs ``ext-openssl``, since the local
step is AES-256-GCM. The Flysystem bridge also needs ``ext-fileinfo``,
which ``league/mime-type-detection`` uses.

Multiple Clients
----------------

Declare several clients when different parts of
the system have different providers, regions or rotation schedules. One of
them is then the default, which is the one autowiring injects:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            default_client: app
            clients:
                app: '%env(KMS_APP_DSN)%'
                pii: '%env(KMS_PII_DSN)%'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'default_client' => 'app',
                'clients' => [
                    'app' => env('KMS_APP_DSN'),
                    'pii' => env('KMS_PII_DSN'),
                ],
            ],
        ]);

With a single client, ``default_client`` is inferred and can be left out.
With several clients and no ``default_client``, autowiring needs the
:ref:`name of a client <key-management-choosing-client>`.

.. _key-management-composite:

Wrapping Under Several Providers
--------------------------------

Everything above comes down to one master key. A provider that is
unreachable stops every decryption, and one that is lost permanently takes
every payload with it.

A client declared by its ``members`` instead of a DSN is a *composite*
client, which prevents both. It encrypts everything under each member and
reads it back through the first member that answers. Each member names a
client declared alongside it and the master key it wraps under; use
``null`` to reuse the key id passed to the call:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            default_client: app
            clients:
                aws: '%env(AWS_KMS_DSN)%'
                azure: '%env(AZURE_KEY_VAULT_DSN)%'
                app:
                    members:
                        # the first member creates the data keys and is read first
                        aws: ~
                        # a key name, optionally followed by /<version>
                        azure: 'app-key'

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'default_client' => 'app',
                'clients' => [
                    'aws' => env('AWS_KMS_DSN'),
                    'azure' => env('AZURE_KEY_VAULT_DSN'),
                    'app' => [
                        'members' => [
                            // creates the data keys and is read first
                            'aws' => null,
                            // a key name, optionally followed by /<version>
                            'azure' => 'app-key',
                        ],
                    ],
                ],
            ],
        ]);

A composite client is a client like any other: same service ids, same
autowiring aliases, traced by the profiler, and it can be the default one.
A member must itself be a declared client, which includes one you
registered yourself through a ``service://`` DSN, and it cannot be
composite in turn.

Writing goes through every member, and a member that cannot wrap fails the
whole call: a ciphertext missing one wrapping would be less redundant than
the configuration claims. Reading needs only one member to answer. Each
member that fails is skipped and logged on the ``key_management`` channel,
since nothing else tells the caller.

The authenticated data of a direct call goes to every member, so a single member
that cannot authenticate data, such as a sealed box, makes every such call fail
and rules out ``bind_wrapping_context``. Only the first member creates the data
keys, and it also wraps the ones that ``key-management:rewrap-data-keys`` moves.
The other members wrap them through their encryption operation and read them
back through their decryption one, which their credentials must allow:
``kms:Encrypt`` and ``kms:Decrypt`` on AWS, or ``encrypt`` and ``decrypt``
under the ``algorithm`` option on Azure Key Vault, rather than ``wrapKey`` and
``unwrapKey`` under ``wrap_algorithm``.

Since one member is enough to read a ciphertext, anyone who can encrypt for
one member can write a ciphertext that the composite reads. With a sealed
box member, its public key is enough.

Because it sits at the client level, a composite client covers every path
without a change of format: direct ciphertexts, both envelope shapes,
stored data keys and blind index keys.

Adopting a Composite Client
~~~~~~~~~~~~~~~~~~~~~~~~~~~

A ciphertext that is not a composite frame is one a member wrote before
joining the composite client. The composite hands it to each member in
turn, so an application adopting a composite client keeps reading what it
wrote before. Those ciphertexts still carry a single wrapping, though, so
re-encrypt them once the composite is deployed if they must be redundant
too.

Stored data keys are moved with one command. Each row records the client
that wrapped it, and the store only uses its ``client`` and its
:ref:`allowed_clients <key-management-store-config>`. When the composite
client becomes the ``client`` of the store, list the former client in
``allowed_clients``, so that the rows it wrapped stay readable, then wrap
every row under the current member list:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys

Remove the former client from ``allowed_clients`` once no row refers to it
anymore.

Retiring a Member
~~~~~~~~~~~~~~~~~

Each frame records the name of the member behind each wrapping, and a read
only goes through the members the configuration lists. Renaming a member
therefore hides its wrappings, and removing one from ``members`` makes the
frames it alone could read unreadable. To stop writing through a member
while still reading what it wrapped, move its name to ``retired``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/key_management.yaml
        key_management:
            default_client: app
            clients:
                aws: '%env(AWS_KMS_DSN)%'
                azure: '%env(AZURE_KEY_VAULT_DSN)%'
                gcp: '%env(GCP_KMS_DSN)%'
                app:
                    members:
                        aws: ~
                        azure: 'app-key'
                    # read, never written to; kept until nothing needs it
                    retired:
                        - gcp

    .. code-block:: php

        // config/packages/key_management.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'key_management' => [
                'default_client' => 'app',
                'clients' => [
                    'aws' => env('AWS_KMS_DSN'),
                    'azure' => env('AZURE_KEY_VAULT_DSN'),
                    'gcp' => env('GCP_KMS_DSN'),
                    'app' => [
                        'members' => [
                            'aws' => null,
                            'azure' => 'app-key',
                        ],
                        // read, never written to; kept until nothing needs it
                        'retired' => ['gcp'],
                    ],
                ],
            ],
        ]);

A retired member is still a full path to the plaintext, so remove it as
soon as no frame depends on it alone. Re-wrap the stored data keys first,
then remove it.

To recover from losing a provider permanently, remove it from ``members``
(a composite client can have a single member) and re-wrap the stored data
keys.
