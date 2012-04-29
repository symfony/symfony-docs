.. index::
   single: Logging

How to log messages to different files
======================================

Symfony standard edition contains a bunch of channels for logging: ``doctrine``,
``event``, ``security`` and ``request``. Each channel corresponds to a logger
service (``monolog.logger.XXX``) in the container and is injected to the
concerned service.

By default, Symfony2 logs every messages in a single file.

Switching a channel to a different handler
------------------------------------------

Now, suppose you want to log the ``doctrine`` channel to a different file.

To do so, just create a new handler and configure it like this:

.. configuration-block::

    .. code-block:: yaml

        monolog:
            handlers:
                main:
                    type: stream
                    path: /var/log/symfony.log
                    channels: !doctrine
                doctrine:
                    type: stream
                    path: /var/log/doctrine.log
                    channels: doctrine

    .. code-block:: xml

        <monolog:config>
            <monolog:handlers>
                <monolog:handler name="main" type="stream" path="/var/log/symfony.log">
                    <monolog:channels>
                        <type>exclusive</type>
                        <channel>doctrine</channel>
                    </monolog:channels>
                </monolog:handler>

                <monolog:handler name="doctrine" type="stream" path="/var/log/doctrine.log" />
                    <monolog:channels>
                        <type>inclusive</type>
                        <channel>doctrine</channel>
                    </monolog:channels>
                </monolog:handler>
            </monolog:handlers>
        </monolog:config>

Yaml specification
------------------

You can specify the configuration by many forms:

.. code-block:: yaml

    channels: ~    # Include all the channels

    channels: foo  # Include only channel "foo"
    channels: !foo # Include all channels, except "foo"

    channels: [foo, bar]   # Include only channels "foo" and "bar"
    channels: [!foo, !bar] # Include all channels, except "foo" and "bar"

    channels:
        type:     inclusive # Include only those listed below
        elements: [ foo, bar ]
    channels:
        type:     exclusive # Include all, except those listed below
        elements: [ foo, bar ]


Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/logging/monolog`
