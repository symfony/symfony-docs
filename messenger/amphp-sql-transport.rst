.. _messenger-amphp-sql-transport:

Using the AMPHP SQL Transport
=============================

The AMPHP SQL transport stores Messenger messages in SQLite, MySQL or
PostgreSQL while keeping database waits cooperative with the Revolt event loop.
Use it when an application running on an Amp or Revolt event loop needs a
durable queue without blocking the event loop.

.. versionadded:: 8.2

    The AMPHP SQL transport was introduced in Symfony 8.2.

Installation
------------

Install the transport package and the driver for your database:

.. code-block:: terminal

    $ composer require symfony/amp-sql-messenger fabpot/amphp-sqlite3
    $ composer require symfony/amp-sql-messenger amphp/mysql
    $ composer require symfony/amp-sql-messenger amphp/postgres

The minimum supported versions are SQLite 3.42, MySQL 8.0.1 and PostgreSQL 10.
SQLite requires PHP's SQLite3 extension. PostgreSQL requires either the pgsql
or pq extension. MariaDB can work through the MySQL driver, but Symfony does
not test it or include it in the compatibility promise.

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

For SQLite, a relative path uses three slashes, for example
``amp-sqlite:///var/messenger.db``. An absolute Unix path uses four slashes,
for example ``amp-sqlite:////srv/app/var/messenger.db``. In Symfony
configuration, ``%kernel.project_dir%`` expands to an absolute path, so the
preceding slash is required.

MySQL and PostgreSQL DSNs support percent-encoded usernames, passwords and
database names. They also support IPv6 hosts. MySQL supports Unix sockets by
using a percent-encoded socket path as the host.

Options can be added to the DSN query string or configured under the
transport's ``options`` key. DSN values take precedence over values under
``options``:

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
                            idle_timeout: 60

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
                                'idle_timeout' => 60,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

The transport supports these common options:

``queue_name`` (default: ``default``)
    The logical queue stored in the shared table. Use different queue names to
    isolate multiple transports that share one database and table.

``table_name`` (default: ``messenger_amp_messages``)
    The table used to store messages. This schema is not compatible with the
    Doctrine Messenger transport.

``redeliver_timeout`` (default: ``3600``)
    The number of seconds before an unacknowledged message is eligible for
    redelivery. Set this to a value greater than the longest expected handler
    execution time or use the worker's ``--keepalive`` option.

``auto_setup`` (default: ``true``)
    Whether the transport creates its table and indexes automatically. Setup
    only creates missing database objects; it does not migrate an existing
    schema.

``max_connections`` (default: ``10``)
    The maximum number of connections in the transport's connection pool.

``idle_timeout`` (default: ``60``)
    The maximum number of seconds that an unused pooled connection stays open.

SQLite also supports ``busy_timeout`` (default: ``5000``), the maximum time in
milliseconds to wait for a database lock. PostgreSQL supports its native
``sslmode`` option. MySQL supports ``tls_ca``, ``tls_cert`` and ``tls_key`` for
TLS certificate paths.

Database Behavior
-----------------

The transport creates the message table and indexes on first use when
``auto_setup`` is enabled. The setup operation is idempotent. Each transport
owns its connection pool and closes that pool when the transport closes.

SQLite claims messages in an ``IMMEDIATE`` transaction. MySQL and PostgreSQL
use ``READ COMMITTED`` transactions with ``FOR UPDATE SKIP LOCKED``. This
prevents concurrent workers from claiming the same message. It also means that
ordering by availability and ID is best effort rather than strict FIFO: a
worker can claim a newer message while another worker holds a lock on an older
one.

All queue timestamps come from the database clock, including delayed delivery,
claiming, redelivery and keepalive. This prevents clock differences between
application hosts from affecting queue behavior.

When transport methods are called from an Amp fiber, database waits suspend the
calling fiber. Other fibers on the Revolt event loop can continue running.
PostgreSQL notifications are not used; all databases use cooperative polling.

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
``messenger:failed:*`` commands. It also supports ``messenger:consume
--keepalive``, batch fetching and delayed messages created by Messenger's retry
system.

Delivery Semantics and Limitations
----------------------------------

The AMPHP SQL transport provides at-least-once delivery. A worker can stop after
a handler performs a side effect but before the message is acknowledged, which
causes the message to be delivered again. Message handlers must therefore be
idempotent.

The transport does not provide exactly-once delivery, cross-database
transactions, an outbox or a worker implementation. The database contains
serialized envelopes and must be treated as trusted application state.
