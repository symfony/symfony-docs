.. index::
    single: Configuration reference; Swift Mailer

SwiftmailerBundle Configuration ("swiftmailer")
===============================================

This reference document is a work in progress. It should be accurate, but
all options are not yet fully covered. For a full list of the default configuration
options, see `Full Default Configuration`_

The ``swiftmailer`` key configures Symfony's integration with Swift Mailer,
which is responsible for creating and delivering email messages.

The following section lists all options that are available to configure a
mailer. It is also possible to configure several mailers (see `Using Multiple Mailers`_).

Configuration
-------------

* `transport`_
* `username`_
* `password`_
* `host`_
* `port`_
* `encryption`_
* `auth_mode`_
* `spool`_
    * `type`_
    * `path`_
* `sender_address`_
* `antiflood`_
    * `threshold`_
    * `sleep`_
* `delivery_address`_
* `disable_delivery`_
* `logging`_

transport
~~~~~~~~~

**type**: ``string`` **default**: ``smtp``

The exact transport method to use to deliver emails. Valid values are:

* smtp
* gmail (see :doc:`/cookbook/email/gmail`)
* mail
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

host
~~~~

**type**: ``string`` **default**: ``localhost``

The host to connect to when using ``smtp`` as the transport.

port
~~~~

**type**: ``string`` **default**: 25 or 465 (depending on `encryption`_)

The port when using ``smtp`` as the transport. This defaults to 465 if encryption
is ``ssl`` and 25 otherwise.

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

For details on email spooling, see :doc:`/cookbook/email/spool`.

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

If set, all messages will be delivered with this address as the "return path"
address, which is where bounced messages should go. This is handled internally
by Swift Mailer's ``Swift_Plugins_ImpersonatePlugin`` class.

antiflood
~~~~~~~~~

threshold
.........

**type**: ``string`` **default**: ``99``

Used with ``Swift_Plugins_AntiFloodPlugin``. This is the number of emails
to send before restarting the transport.

sleep
.....

**type**: ``string`` **default**: ``0``

Used with ``Swift_Plugins_AntiFloodPlugin``. This is the number of seconds
to sleep for during a transport restart.

delivery_address
~~~~~~~~~~~~~~~~

**type**: ``string``

If set, all email messages will be sent to this address instead of being sent
to their actual recipients. This is often useful when developing. For example,
by setting this in the ``config_dev.yml`` file, you can guarantee that all
emails sent during development go to a single account.

This uses ``Swift_Plugins_RedirectingPlugin``. Original recipients are available
on the ``X-Swift-To``, ``X-Swift-Cc`` and ``X-Swift-Bcc`` headers.

disable_delivery
~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

If true, the ``transport`` will automatically be set to ``null``, and no
emails will actually be delivered.

logging
~~~~~~~

**type**: ``Boolean`` **default**: ``%kernel.debug%``

If true, Symfony's data collector will be activated for Swift Mailer and the
information will be available in the profiler.

Full default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        swiftmailer:
            transport:            smtp
            username:             ~
            password:             ~
            host:                 localhost
            port:                 false
            encryption:           ~
            auth_mode:            ~
            spool:
                type:                 file
                path:                 "%kernel.cache_dir%/swiftmailer/spool"
            sender_address:       ~
            antiflood:
                threshold:            99
                sleep:                0
            delivery_address:     ~
            disable_delivery:     ~
            logging:              "%kernel.debug%"

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:swiftmailer="http://symfony.com/schema/dic/swiftmailer"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd">

            <swiftmailer:config
                transport="smtp"
                username=""
                password=""
                host="localhost"
                port="false"
                encryption=""
                auth_mode=""
                sender_address=""
                delivery_address=""
                disable_delivery=""
                logging="%kernel.debug%"
                >
                <swiftmailer:spool
                    path="%kernel.cache_dir%/swiftmailer/spool"
                    type="file" />

                <swiftmailer:antiflood
                    sleep="0"
                    threshold="99" />
            </swiftmailer:config>
        </container>

Using multiple Mailers
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
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/swiftmailer
                http://symfony.com/schema/dic/swiftmailer/swiftmailer-1.0.xsd"
        >
            <swiftmailer:config default-mailer="second_mailer">
                <swiftmailer:mailer name="first_mailer"/>
                <swiftmailer:mailer name="second_mailer"/>
            </swiftmailer:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('swiftmailer', array(
            'default_mailer' => 'second_mailer',
            'mailers' => array(
                'first_mailer' => array(
                    // ...
                ),
                'second_mailer' => array(
                    // ...
                ),
            ),
        ));

Each mailer is registered as a service::

    // ...

    // returns the first mailer
    $container->get('swiftmailer.mailer.first_mailer');

    // also returns the second mailer since it is the default mailer
    $container->get('swiftmailer.mailer');

    // returns the second mailer
    $container->get('swiftmailer.mailer.second_mailer');
