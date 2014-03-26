.. index::
   single: Logging

How to log Messages to different Files
======================================

The Symfony Standard Edition contains a bunch of channels for logging: ``doctrine``,
``event``, ``security`` and ``request``. Each channel corresponds to a logger
service (``monolog.logger.XXX``) in the container and is injected to the
concerned service. The purpose of channels is to be able to organize different
types of log messages.

By default, Symfony2 logs every messages into a single file (regardless of
the channel).

Switching a Channel to a different Handler
------------------------------------------

Now, suppose you want to log the ``doctrine`` channel to a different file.

To do so, just create a new handler and configure it like this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                main:
                    type:     stream
                    path:     /var/log/symfony.log
                    channels: ["!doctrine"]
                doctrine:
                    type:     stream
                    path:     /var/log/doctrine.log
                    channels: [doctrine]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd"
        >
            <monolog:config>
                <monolog:handler name="main" type="stream" path="/var/log/symfony.log">
                    <monolog:channels>
                        <monolog:channel>!doctrine</monolog:channel>
                    </monolog:channels>
                </monolog:handler>

                <monolog:handler name="doctrine" type="stream" path="/var/log/doctrine.log">
                    <monolog:channels>
                        <monolog:channel>doctrine</monolog:channel>
                    </monolog:channels>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main'     => array(
                    'type'     => 'stream',
                    'path'     => '/var/log/symfony.log',
                    'channels' => array(
                        '!doctrine',
                    ),
                ),
                'doctrine' => array(
                    'type'     => 'stream',
                    'path'     => '/var/log/doctrine.log',
                    'channels' => array(
                        'doctrine',
                    ),
                ),
            ),
        ));

YAML specification
------------------

You can specify the configuration by many forms:

.. code-block:: yaml

    channels: ~    # Include all the channels

    channels: foo  # Include only channel "foo"
    channels: "!foo" # Include all channels, except "foo"

    channels: [foo, bar]   # Include only channels "foo" and "bar"
    channels: ["!foo", "!bar"] # Include all channels, except "foo" and "bar"

Creating your own Channel
-------------------------

You can change the channel monolog logs to one service at a time. This is done
either via the :ref:`configuration <cookbook-monolog-channels-config>` below
or by tagging your service with :ref:`monolog.logger<dic_tags-monolog>` and
specifying which channel the service should log to. With the tag, the logger
that is injected into that service is preconfigured to use the channel you've
specified.

.. _cookbook-monolog-channels-config:

Configure Additional Channels without Tagged Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.4
    This feature was introduced to the MonologBundle 2.4, which was first
    packaged with Symfony 2.4.

With MonologBundle 2.4 you can configure additional channels without the
need to tag your services:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            channels: ["foo", "bar"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd"
        >
            <monolog:config>
                <monolog:channel>foo</monolog:channel>
                <monolog:channel>bar</monolog:channel>
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'channels' => array(
                'foo',
                'bar',
            ),
        ));

With this, you can now send log messages to the ``foo`` channel by using
the automatically registered logger service ``monolog.logger.foo``.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/logging/monolog`
