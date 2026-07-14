Using the AMPHP SQL Transport
=============================

The AMPHP SQL transport stores Messenger messages in SQLite, MySQL or PostgreSQL
databases using the non-blocking drivers of the `AMPHP`_ ecosystem. Use it when
your application runs on the `Revolt event loop`_ and needs a durable queue that
doesn't block that loop.

.. versionadded:: 8.2

    The AMPHP SQL transport was introduced in Symfony 8.2.

Installation
------------

Install the transport package and the driver for your database:

.. code-block:: terminal

    # SQLite
    $ composer require symfony/amp-sql-messenger fabpot/amphp-sqlite3

    # MySQL
    $ composer require symfony/amp-sql-messenger amphp/mysql

    # PostgreSQL
    $ composer require symfony/amp-sql-messenger amphp/postgres

The minimum supported versions are SQLite 3.42, MySQL 8.0.1 and PostgreSQL 10.
SQLite requires the PHP ``sqlite3`` extension and PostgreSQL requires either the
``pgsql`` or the ``pq`` extension. MariaDB may work through the MySQL driver,
but Symfony doesn't test or officially support it.

Configuration
-------------

The DSN scheme selects the database driver:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=amp-sqlite:///var/messenger.db
    # MESSENGER_TRANSPORT_DSN=amp-mysql://user:password@127.0.0.1/app
    # MESSENGER_TRANSPORT_DSN=amp-postgres://user:password@127.0.0.1/app

Use the DSN in your Messenger configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'

    .. code-block:: php

        // config/packages/messenger.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'messenger' => [
                    'transports' => [
                        'async' => [
                            'dsn' => env('MESSENGER_TRANSPORT_DSN'),
                        ],
                    ],
                ],
            ],
        ]);

For SQLite, a relative path uses three slashes (e.g.
``amp-sqlite:///var/messenger.db``) and an absolute Unix path uses four slashes
(e.g. ``amp-sqlite:////srv/app/var/messenger.db``). In Symfony configuration
files, ``%kernel.project_dir%`` expands to an absolute path, so the DSN needs
three slashes before it:
``amp-sqlite:///%kernel.project_dir%/var/messenger.db``.

MySQL and PostgreSQL DSNs support IPv6 hosts and percent-encoded usernames,
passwords and database names. To connect to MySQL through a Unix socket, use the
percent-encoded socket path as the host.

You can add options to the DSN query string or define them under the transport's
``options`` key. DSN values take precedence over values under ``options``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            queue_name: async
                            redeliver_timeout: 7200
                            max_connections: 20

    .. code-block:: php

        // config/packages/messenger.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'messenger' => [
                    'transports' => [
                        'async' => [
                            'dsn' => env('MESSENGER_TRANSPORT_DSN'),
                            'options' => [
                                'queue_name' => 'async',
                                'redeliver_timeout' => 7200,
                                'max_connections' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The transport supports these common options:

``queue_name`` (default: ``default``)
    The logical queue stored in the table. Use different queue names to isolate
    multiple transports that share the same database and table.

``table_name`` (default: ``messenger_amp_messages``)
    The table used to store messages. Its schema isn't compatible with the
    Doctrine transport, so don't share a table between both transports.

``redeliver_timeout`` (default: ``3600``)
    The number of seconds before an unacknowledged message becomes eligible for
    redelivery. Set this to a value greater than the longest expected handler
    execution time or use the ``--keepalive`` option of the worker.

``auto_setup`` (default: ``true``)
    Whether the transport creates its table and indexes on first use. Setup only
    creates missing database objects and doesn't migrate an existing schema.

``max_connections`` (default: ``10``)
    The maximum number of connections in the connection pool. Each transport has
    its own pool, so this limit applies per transport.

``idle_timeout`` (default: ``60``)
    The maximum number of seconds that an unused pooled connection stays open.

SQLite also supports ``busy_timeout`` (default: ``5000``), the maximum time in
milliseconds to wait for a database lock. PostgreSQL supports its native
``sslmode`` option. MySQL supports ``tls_ca``, ``tls_cert`` and ``tls_key`` to
define the paths of the TLS certificate files.

Database Behavior
-----------------

SQLite claims messages in an ``IMMEDIATE`` transaction. MySQL and PostgreSQL use
``READ COMMITTED`` transactions with ``FOR UPDATE SKIP LOCKED``. Both approaches
prevent concurrent workers from claiming the same message. However, with MySQL
and PostgreSQL the message order is not strictly FIFO: a worker can claim a
newer message while another worker holds a lock on an older one.

All queue timestamps come from the database clock, including delayed delivery,
claiming, redelivery and keepalive. This prevents clock differences between
application hosts from affecting queue behavior.

When you call transport methods from an Amp fiber, database waits suspend the
calling fiber and other fibers on the event loop continue running. The transport
doesn't use PostgreSQL notifications; all databases use cooperative polling.

Using Multiple Queues and a Failure Transport
---------------------------------------------

Multiple transports can share the same database and table by using different
``queue_name`` values:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                failure_transport: failed
                transports:
                    async:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            queue_name: async
                    failed:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            queue_name: failed

    .. code-block:: php

        // config/packages/messenger.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'messenger' => [
                    'failure_transport' => 'failed',
                    'transports' => [
                        'async' => [
                            'dsn' => env('MESSENGER_TRANSPORT_DSN'),
                            'options' => ['queue_name' => 'async'],
                        ],
                        'failed' => [
                            'dsn' => env('MESSENGER_TRANSPORT_DSN'),
                            'options' => ['queue_name' => 'failed'],
                        ],
                    ],
                ],
            ],
        ]);

The transport supports the standard ``messenger:stats`` and
``messenger:failed:*`` commands, the ``--keepalive`` and ``--fetch-size``
options of the ``messenger:consume`` command and delayed messages, including the
ones created by the Messenger retry system.

Delivery Semantics and Limitations
----------------------------------

The AMPHP SQL transport provides at-least-once delivery. A worker can stop after
a handler performs a side effect but before it acknowledges the message, which
causes the transport to deliver the message again. Therefore, your message
handlers must be :ref:`idempotent <messenger-handler-idempotency>`.

The transport doesn't provide cross-database transactions or an outbox, and it
doesn't include its own worker implementation. Because the database stores
serialized envelopes, treat it as trusted application state and restrict write
access to it.

.. _`AMPHP`: https://amphp.org/
.. _`Revolt event loop`: https://revolt.run/
