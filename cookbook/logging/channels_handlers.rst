.. index::
   single: Logging

How to log Messages to different Files
======================================

.. versionadded:: 2.1
    The ability to specify channels for a specific handler was added to
    the MonologBundle for Symfony 2.1.

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

Creating your own Channel
-------------------------

You can change the channel monolog logs to one service at a time. This is done
by tagging your service with ``monolog.logger`` and specifying which channel
the service should log to. By doing this, the logger that is injected into
that service is preconfigured to use the channel you've specified.

For more information - including a full example - read ":ref:`dic_tags-monolog`"
in the Dependency Injection Tags reference section.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/logging/monolog`
