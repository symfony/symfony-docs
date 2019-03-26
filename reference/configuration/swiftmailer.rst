.. index::
    single: Configuration reference; Swift Mailer

Mailer Configuration Reference (SwiftmailerBundle)
==================================================

The SwiftmailerBundle integrates the Swiftmailer library in Symfony applications
to :doc:`send emails </email>`. All these options are configured under the
``swiftmailer`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference swiftmailer

    # displays the actual config values used by your application
    $ php bin/console debug:config swiftmailer

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/swiftmailer``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd``

Configuration
-------------

.. class:: list-config-options list-config-options--complex

* `antiflood`_

  * `sleep`_
  * `threshold`_

* `auth_mode`_
* `command`_
* `delivery_addresses`_
* `delivery_whitelist`_
* `disable_delivery`_
* `encryption`_
* `host`_
* `local_domain`_
* `logging`_
* `password`_
* `port`_
* `sender_address`_
* `source_ip`_
* `spool`_

  * `path`_
  * `type`_

* `timeout`_
* `transport`_
* `url`_
* `username`_

url
~~~

**type**: ``string``

The entire SwiftMailer configuration using a DSN-like URL format.

Example: ``smtp://user:pass@host:port/?timeout=60&encryption=ssl&auth_mode=login&...``

transport
~~~~~~~~~

**type**: ``string`` **default**: ``smtp``

The exact transport method to use to deliver emails. Valid values are:

* smtp
* gmail (see :ref:`email-using-gmail`)
* mail (deprecated in SwiftMailer since version 5.4.5)
* sendmail
* null (same as setting `disable_delivery`_ to ``true``)

username
~~~~~~~~

**type**: ``string``

The username when using ``smtp`` as the transport.

password
~~~~~~~~

**type**: ``string``

The password when using ``smtp`` as the transport.

command
~~~~~~~~

**type**: ``string`` **default**: ``/usr/sbin/sendmail -bs``

Command to be executed by ``sendmail`` transport.

host
~~~~

**type**: ``string`` **default**: ``localhost``

The host to connect to when using ``smtp`` as the transport.

port
~~~~

**type**: ``string`` **default**: 25 or 465 (depending on `encryption`_)

The port when using ``smtp`` as the transport. This defaults to 465 if encryption
is ``ssl`` and 25 otherwise.

timeout
~~~~~~~

**type**: ``integer``

The timeout in seconds when using ``smtp`` as the transport.

source_ip
~~~~~~~~~

**type**: ``string``

The source IP address when using ``smtp`` as the transport.

local_domain
~~~~~~~~~~~~

**type**: ``string``

.. versionadded:: 2.4.0

    The ``local_domain`` option was introduced in SwiftMailerBundle 2.4.0.

The domain name to use in ``HELO`` command.

encryption
~~~~~~~~~~

**type**: ``string``

The encryption mode to use when using ``smtp`` as the transport. Valid values
are ``tls``, ``ssl``, or ``null`` (indicating no encryption).

auth_mode
~~~~~~~~~

**type**: ``string``

The authentication mode to use when using ``smtp`` as the transport. Valid
values are ``plain``, ``login``, ``cram-md5``, or ``null``.

spool
~~~~~

For details on email spooling, see :doc:`/email/spool`.

type
....

**type**: ``string`` **default**: ``file``

The method used to store spooled messages. Valid values are ``memory`` and
``file``. A custom spool should be possible by creating a service called
``swiftmailer.spool.myspool`` and setting this value to ``myspool``.

path
....

**type**: ``string`` **default**: ``%kernel.cache_dir%/swiftmailer/spool``

When using the ``file`` spool, this is the path where the spooled messages
will be stored.

sender_address
~~~~~~~~~~~~~~

**type**: ``string``

If set, all messages will be delivered with this address as the "return
path" address, which is where bounced messages should go. This is handled
internally by Swift Mailer's ``Swift_Plugins_ImpersonatePlugin`` class.

antiflood
~~~~~~~~~

threshold
.........

**type**: ``integer`` **default**: ``99``

Used with ``Swift_Plugins_AntiFloodPlugin``. This is the number of emails
to send before restarting the transport.

sleep
.....

**type**: ``integer`` **default**: ``0``

Used with ``Swift_Plugins_AntiFloodPlugin``. This is the number of seconds
to sleep for during a transport restart.

.. _delivery-address:

delivery_addresses
~~~~~~~~~~~~~~~~~~

**type**: ``array``

.. note::

    In previous versions, this option was called ``delivery_address``.

If set, all email messages will be sent to these addresses instead of being sent
to their actual recipients. This is often useful when developing. For example,
by setting this in the ``config/packages/dev/swiftmailer.yaml`` file, you can
guarantee that all emails sent during development go to one or more some
specific accounts.

This uses ``Swift_Plugins_RedirectingPlugin``. Original recipients are available
on the ``X-Swift-To``, ``X-Swift-Cc`` and ``X-Swift-Bcc`` headers.

delivery_whitelist
~~~~~~~~~~~~~~~~~~

**type**: ``array``

Used in combination with ``delivery_address`` or ``delivery_addresses``. If set, emails matching any
of these patterns will be delivered like normal, as well as being sent to
``delivery_address`` or ``delivery_addresses``. For details, see the
:ref:`How to Work with Emails during Development <sending-to-a-specified-address-but-with-exceptions>`
article.

disable_delivery
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If true, the ``transport`` will automatically be set to ``null`` and no
emails will actually be delivered.

logging
~~~~~~~

**type**: ``boolean`` **default**: ``%kernel.debug%``

If true, Symfony's data collector will be activated for Swift Mailer and
the information will be available in the profiler.

.. tip::

    The following options can be set via environment variables: ``url``,
    ``transport``, ``username``, ``password``, ``host``, ``port``, ``timeout``,
    ``source_ip``, ``local_domain``, ``encryption``, ``auth_mode``. For details,
    see: :doc:`/configuration/environment_variables`.

Using Multiple Mailers
----------------------

You can configure multiple mailers by grouping them under the ``mailers``
key (the default mailer is identified by the ``default_mailer`` option):

.. configuration-block::

    .. code-block:: yaml

        swiftmailer:
            default_mailer: second_mailer
            mailers:
                first_mailer:
                    # ...
                second_mailer:
                    # ...

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                https://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config default-mailer="second_mailer">
                <swiftmailer:mailer name="first_mailer"/>
                <swiftmailer:mailer name="second_mailer"/>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('swiftmailer', [
            'default_mailer' => 'second_mailer',
            'mailers' => [
                'first_mailer' => [
                    // ...
                ],
                'second_mailer' => [
                    // ...
                ],
            ],
        ]);

Each mailer is registered automatically as a service with these IDs::

    // ...

    // returns the first mailer
    $container->get('swiftmailer.mailer.first_mailer');

    // also returns the second mailer since it is the default mailer
    $container->get('swiftmailer.mailer');

    // returns the second mailer
    $container->get('swiftmailer.mailer.second_mailer');

.. caution::

    When configuring multiple mailers, options must be placed under the
    appropriate mailer key of the configuration instead of directly under the
    ``swiftmailer`` key.

When using :ref:`autowiring <services-autowire>` only the default mailer is
injected when type-hinting some argument with the ``\Swift_Mailer`` class. If
you need to inject a different mailer in some service, use any of these
alternatives based on the :ref:`service binding <services-binding>` feature:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                bind:
                    # this injects the second mailer when type-hinting constructor arguments with \Swift_Mailer
                    \Swift_Mailer: '@swiftmailer.mailer.second_mailer'
                    # this injects the second mailer when a service constructor argument is called $specialMailer
                    $specialMailer: '@swiftmailer.mailer.second_mailer'

            App\Some\Service:
                # this injects the second mailer only for this argument of this service
                $differentMailer: '@swiftmailer.mailer.second_mailer'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false">
                    <!-- this injects the second mailer when type-hinting constructor arguments with \Swift_Mailer -->
                    <bind key="\Swift_Mailer">@swiftmailer.mailer.second_mailer</bind>
                    <!-- this injects the second mailer when a service constructor argument is called $specialMailer -->
                    <bind key="$specialMailer">@swiftmailer.mailer.second_mailer</bind>
                </defaults>

                <service id="App\Some\Service">
                    <!-- this injects the second mailer only for this argument of this service -->
                    <argument key="$differentMailer">@swiftmailer.mailer.second_mailer</argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Some\Service;
        use Symfony\Component\DependencyInjection\Reference;
        use Psr\Log\LoggerInterface;

        $container->register(Service::class)
            ->setPublic(true)
            ->setBindings([
                // this injects the second mailer when this service type-hints constructor arguments with \Swift_Mailer
                \Swift_Mailer => '@swiftmailer.mailer.second_mailer',
                // this injects the second mailer when this service has a constructor argument called $specialMailer
                '$specialMailer' => '@swiftmailer.mailer.second_mailer',
            ])
        ;
