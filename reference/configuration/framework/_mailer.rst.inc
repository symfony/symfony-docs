mailer
~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _mailer-dsn:

dsn
...

**type**: ``string`` **default**: ``null``

The DSN used by the mailer. When several DSN may be used, use
``transports`` option (see below) instead.

envelope
........

recipients
""""""""""

**type**: ``array``

The "envelope recipient" which is used as the value of ``RCPT TO`` during the
`SMTP session`_. This value overrides any other recipient set in the code.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                dsn: 'smtp://localhost:25'
                envelope:
                    recipients: ['admin@symfony.com', 'lead@symfony.com']

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:mailer dsn="smtp://localhost:25">
                    <framework:envelope>
                        <framework:recipient>admin@symfony.com</framework:recipient>
                        <framework:recipient>lead@symfony.com</framework:recipient>
                    </framework:envelope>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container): void {
            $container->extension('framework', [
                'mailer' => [
                    'dsn' => 'smtp://localhost:25',
                    'envelope' => [
                        'recipients' => [
                            'admin@symfony.com',
                            'lead@symfony.com',
                        ],
                    ],
                ],
            ]);
        };

sender
""""""

**type**: ``string``

The "envelope sender" which is used as the value of ``MAIL FROM`` during the
`SMTP session`_. This value overrides any other sender set in the code.

allowed_recipients
""""""""""""""""""

**type**: ``array``

A list of regular expressions that allow recipients to still receive their
original emails when the ``recipients`` option is defined. These messages
will also be sent to the address(es) defined in ``recipients``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/mailer.yaml
        framework:
            mailer:
                envelope:
                    recipients: ['youremail@example.com']
                    allowed_recipients:
                        - 'internal@example.com'
                        - 'internal-.*@example.(com|fr)'

    .. code-block:: xml

        <!-- config/packages/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
            <framework:config>
                <framework:mailer>
                    <framework:envelope>
                        <framework:recipient>youremail@example.com</framework:recipient>
                        <framework:allowed-recipient>internal@example.com</framework:allowed-recipient>
                        <framework:allowed-recipient>internal-.*@example.(com|fr)</framework:allowed-recipient>
                    </framework:envelope>
                </framework:mailer>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/mailer.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->mailer()
                ->envelope()
                    ->recipients(['youremail@example.com'])
                    ->allowedRecipients([
                        'internal@example.com',
                        'internal-.*@example.(com|fr)',
                    ])
            ;
        };

.. _mailer-headers:

headers
.......

**type**: ``array``

Headers to add to emails. The key (``name`` attribute in xml format) is the
header name and value the header value.

.. seealso::

    For more information, see :ref:`Configuring Emails Globally <mailer-configure-email-globally>`

message_bus
...........

**type**: ``string`` **default**: ``null`` or default bus if Messenger component is installed

Service identifier of the message bus to use when using the
:doc:`Messenger component </messenger>` (e.g. ``messenger.default_bus``).

transports
..........

**type**: ``array``

A :ref:`list of DSN <multiple-email-transports>` that can be used by the
mailer. A transport name is the key and the dsn is the value.

dkim_signer
...........

Configures a global DKIM signer that automatically signs all outgoing messages.

.. versionadded:: 7.3

    The ``dkim_signer`` option was introduced in Symfony 7.3.

key
"""

**type**: ``string`` **default**: ``''``

The DKIM signing key. This can be the key content or a path to the key file
in PEM format (prefixed with ``file://``).

domain
""""""

**type**: ``string`` **default**: ``''``

The signing domain name.

select
""""""

**type**: ``string`` **default**: ``''``

The DKIM selector (a string used to point to a specific DKIM public key record
in your DNS).

passphrase
""""""""""

**type**: ``string`` **default**: ``''``

The passphrase for the private key.

options
"""""""

**type**: ``array``

Additional DKIM signing options (e.g. algorithm, headers to ignore).

.. seealso::

    For more information, see :ref:`Signing and Encrypting Messages <signing-and-encrypting-messages>`.

smime_signer
............

Configures a global S/MIME signer that automatically signs all outgoing messages.

.. versionadded:: 7.3

    The ``smime_signer`` option was introduced in Symfony 7.3.

key
"""

**type**: ``string`` **default**: ``''``

The path to the S/MIME private key in PEM format.

certificate
"""""""""""

**type**: ``string`` **default**: ``''``

The path to the S/MIME certificate in PEM format.

passphrase
""""""""""

**type**: ``string`` **default**: ``null``

The passphrase for the private key.

extra_certificates
""""""""""""""""""

**type**: ``string`` **default**: ``null``

The path to a file containing extra certificates (e.g. intermediate
certificates) to include in the signature.

sign_options
""""""""""""

**type**: ``integer`` **default**: ``null``

Bitwise operator options for :phpfunction:`openssl_pkcs7_sign`.

smime_encrypter
...............

Configures a global S/MIME encrypter that automatically encrypts all outgoing
messages.

.. versionadded:: 7.3

    The ``smime_encrypter`` option was introduced in Symfony 7.3.

repository
""""""""""

**type**: ``string`` **default**: ``''``

The service ID of a class implementing
:class:`Symfony\\Component\\Mailer\\EventListener\\SmimeCertificateRepositoryInterface`.
This service is used to find the certificate path for each email recipient.

cipher
""""""

**type**: ``integer`` **default**: ``null``

The `OpenSSL cipher`_ algorithm constant used for encryption.

.. _`SMTP session`: https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol#SMTP_transport_example
.. _`OpenSSL cipher`: https://www.php.net/manual/en/openssl.ciphers.php
