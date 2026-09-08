MongoDB Cache Adapter
=====================

.. versionadded:: 8.2

    The ``MongoDbAdapter`` and ``MongoDbTagAwareAdapter`` were introduced in Symfony 8.2.

.. seealso::

    This article explains how to create and configure the MongoDB adapter itself.
    In Symfony applications, you can use the ``cache.adapter.mongodb`` and
    ``cache.adapter.mongodb_tag_aware`` services by
    :ref:`configuring the cache <cache-configuration-with-frameworkbundle>`.

This adapter stores each cache item as a document of a MongoDB collection. Items
are shared by every server of the deployment, and the database expires them on
its own once a TTL index is created.

.. warning::

    **Requirements:** The `MongoDB PHP extension`_ must be installed and active,
    and the `mongodb/mongodb`_ library must be installed in your application by
    running ``composer require mongodb/mongodb``.

.. note::

    This adapter implements :class:`Symfony\\Component\\Cache\\PruneableInterface`,
    allowing for manual :ref:`pruning of expired cache entries <component-cache-cache-pool-prune>`
    by calling the ``prune()`` method. Pruning is rarely needed here, because the
    TTL index created by the ``setup()`` method lets the server remove expired
    items by itself.

The :class:`Symfony\\Component\\Cache\\Adapter\\MongoDbAdapter` expects a
``MongoDB\Collection``, ``MongoDB\Database``, ``MongoDB\Client`` or a
`MongoDB Connection String`_ as its first parameter. You can pass a namespace,
a default cache lifetime and an options array as the other optional arguments::

    use Symfony\Component\Cache\Adapter\MongoDbAdapter;

    $cache = new MongoDbAdapter(

        // a MongoDB collection, database or client, or a connection string
        $collectionOrConnectionString,

        // the string prefixed to the keys of the items stored in this cache
        $namespace = '',

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the collection is emptied or its documents are otherwise deleted)
        $defaultLifetime = 0,

        // an array of options for configuring the collection and the driver
        $options = []
    );

Whichever form is used, the client only connects on the first cache operation, so
an unreachable server does not slow down requests that never touch the cache.

Configure the Connection
------------------------

Both adapters read their settings from the connection string, so a single
environment variable is enough to configure a cache pool::

    use Symfony\Component\Cache\Adapter\MongoDbAdapter;

    // stores the items in the "cache_items" collection of the "app" database
    $cache = new MongoDbAdapter('mongodb://localhost');

    // stores the items in the "cache.sessions" collection of the "bookstore" database
    $cache = new MongoDbAdapter('mongodb://localhost/bookstore?collection_name=cache.sessions');

    // MongoDB Atlas and any other deployment resolved through DNS seed lists
    $cache = new MongoDbAdapter('mongodb+srv://user:password@cluster.example.com/bookstore');

The connection string is read as follows:

* the database name comes from the path, and defaults to ``app``;
* the collection name comes from the ``collection_name`` query parameter, and
  defaults to ``cache_items``;
* the key prefix comes from the ``namespace`` query parameter, unless a namespace
  is given as the second constructor argument, which takes precedence;
* every other query parameter is left in the connection string and passed to the
  driver, so any `MongoDB Connection String`_ option can be used.

The collection does not have to exist beforehand: MongoDB creates it on the first
write. See `Creating the Indexes`_ for the indexes it should carry.

When the connection is managed elsewhere in your application, pass the object
itself instead of a connection string. This is the only way to reuse a client
across several pools, or to share it with the
:ref:`MongoDbStore lock <lock-store-mongodb>`::

    use MongoDB\Client;
    use Symfony\Component\Cache\Adapter\MongoDbAdapter;

    $client = new Client('mongodb://localhost');

    // the database and collection are then read from the options
    $cache = new MongoDbAdapter($client, '', 0, [
        'database_name' => 'bookstore',
        'collection_name' => 'cache_items',
    ]);

    // or pass the collection directly
    $cache = new MongoDbAdapter($client->getCollection('bookstore', 'cache_items'));

Configure the Options
---------------------

The fourth argument of the constructor takes the following options:

``database_name`` (type: ``string``, default: ``app``)
    The name of the database holding the collection. It is also read from the
    path of the connection string.

``collection_name`` (type: ``string``, default: ``cache_items``)
    The name of the collection storing the cache items. It is also read from the
    ``collection_name`` query parameter of the connection string.

``driverOptions`` (type: ``array``, default: ``[]``)
    The driver options passed to `MongoDBClient::__construct`_. Only used when a
    connection string is given, since an injected client already carries its own.

Any other option is forwarded to the collection, typically a ``readPreference``,
``readConcern`` or ``writeConcern`` given as a ``MongoDB\Driver\*`` object. The
same settings can be expressed as strings in the connection string, which is
usually more convenient::

    use MongoDB\Driver\ReadPreference;
    use Symfony\Component\Cache\Adapter\MongoDbAdapter;

    // as a connection string option
    $cache = new MongoDbAdapter('mongodb://localhost/bookstore?readPreference=secondaryPreferred');

    // as a collection option, on a client built elsewhere
    $cache = new MongoDbAdapter($client, '', 0, [
        'database_name' => 'bookstore',
        'readPreference' => new ReadPreference(ReadPreference::SECONDARY_PREFERRED),
    ]);

Tuning the Connection for a Cache
---------------------------------

The driver defaults are meant for general purpose applications, where waiting is
better than failing. A cache is the opposite: it is a best-effort dependency, and
an unavailable server should be detected in milliseconds rather than seconds, so
that the application can fall back to computing the value. The adapter turns the
driver errors into cache misses and logs them, so a database outage degrades the
response time instead of breaking the request. Only ``prune()`` lets the
exception bubble up, since it is a maintenance operation.

.. code-block:: text

    mongodb://localhost/bookstore?appName=service-cache&serverSelectionTimeoutMS=1000&connectTimeoutMS=1000&socketTimeoutMS=1000

These values are a starting point, not universal defaults: measure the actual
latency between the application and the database before lowering them further.

``appName``
    Identifies the connections in the server logs, in the ``$currentOp`` output
    and in the Atlas monitoring, which makes it possible to tell the cache
    traffic apart from the rest of the application.

``serverSelectionTimeoutMS``
    How long the driver may spend looking for a suitable server, 30 seconds by
    default. In practice the PHP driver usually fails sooner, because it enables
    ``serverSelectionTryOnce`` by default: it runs a single scan and gives up
    instead of retrying until the timeout expires. Set
    ``serverSelectionTryOnce=false`` to favour resilience during a failover over
    response time.

``connectTimeoutMS``
    How long a single connection attempt may last, 10 seconds by default. It also
    caps the socket timeout of the ``hello`` commands used to monitor the servers.
    Set it above the worst case observed on the path, TLS handshake and
    authentication included, which makes values below one second risky on Atlas
    or across regions. It should not exceed ``serverSelectionTimeoutMS``.

``socketTimeoutMS``
    How long a read or a write may last on an established socket, 5 minutes by
    default in the PHP driver. It only applies to the operations of the
    application, monitoring uses ``connectTimeoutMS`` instead. Keep it above the
    slowest legitimate cache operation, otherwise queries are aborted while the
    server is answering.

Consistency Trade-Offs
----------------------

On a replica set, the driver decides which node serves the reads and how many
nodes must acknowledge the writes. The defaults, reads on the primary and writes
acknowledged by a majority of the nodes, are the right starting point: the cache
reads its own writes, and an acknowledged item survives a failover.

Faster Writes
~~~~~~~~~~~~~

Waiting for a majority of the nodes is what a cache can most easily give up,
since a lost item is only recomputed:

.. code-block:: text

    mongodb://localhost/bookstore?w=1&journal=false

``w=1`` acknowledges the write as soon as the primary has it in memory, without
waiting for the other nodes or for the journal. Errors are still reported, so a
failing write does not go unnoticed, but an item can be lost if the primary
crashes, and it can be rolled back if the primary steps down.

Faster still is ``w=0``, where nothing is acknowledged at all. The adapter can no
longer detect a failure, so ``save()`` returns ``true`` even when the item was
never stored.

Reading from the Secondaries
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

``readPreference=secondaryPreferred`` spreads the reads across the whole replica
set instead of hitting the primary only. It is tempting for a cache, but it is
not recommended by default, because replication is asynchronous: an item that was
just saved may read as a miss, and an item that was just invalidated may still be
served for as long as the replication lag. A cache that is read right after being
written, which is the common case, gets little out of it and pays for the
inconsistency.

Consider it only when the primary is measurably saturated by the cache reads, and
only together with ``w=majority``:

.. code-block:: text

    mongodb://localhost/bookstore?readPreference=secondaryPreferred&w=majority

The two settings belong together. With a weaker write concern the item may not
have reached any secondary at all, and its deletion may even be rolled back, so
reading elsewhere than on the primary turns a bounded lag into an unbounded one.

The other read preferences are ``primaryPreferred``, which falls back to the
secondaries only when there is no primary, for example during an election, and
``nearest``, which favours latency in a multi-region deployment.

Creating the Indexes
--------------------

The adapters query the collection by expiration date, and by tag for the tag
aware one. Both queries need an index, otherwise every read scans the whole
collection. The ``setup()`` method creates them::

    use Symfony\Component\Cache\Adapter\MongoDbAdapter;

    $cache = new MongoDbAdapter('mongodb://localhost/bookstore');
    $cache->setup();

It creates a `TTL index`_ on the ``expires_at`` field, so the server deletes the
expired items by itself, usually within a minute. The index is partial, so that
items stored without a lifetime are not indexed at all.

:class:`Symfony\\Component\\Cache\\Adapter\\MongoDbTagAwareAdapter` creates a
second partial index, on the ``tags`` field, which turns the invalidation of a
tag into a single indexed delete instead of a collection scan.

Run ``setup()`` when deploying the application rather than on every request.
Running it again is harmless: creating an index that already exists does nothing,
and the cached items are left untouched.

When the database user of the application is not allowed to create indexes, the
equivalent commands can be run with ``mongosh``:

.. code-block:: javascript

    db.cache_items.createIndex(
        { expires_at: 1 },
        { expireAfterSeconds: 0, partialFilterExpression: { expires_at: { $exists: true } } }
    )

    // only for MongoDbTagAwareAdapter
    db.cache_items.createIndex(
        { tags: 1 },
        { partialFilterExpression: { tags: { $exists: true } } }
    )

.. _mongodb-tag-aware-adapter:

Working with Tags
-----------------

In order to use tag-based invalidation, you can wrap your adapter in
:class:`Symfony\\Component\\Cache\\Adapter\\TagAwareAdapter`. However, when
MongoDB is used as backend, it's often more interesting to use the dedicated
:class:`Symfony\\Component\\Cache\\Adapter\\MongoDbTagAwareAdapter`. It stores
the tags of an item in the document itself, so invalidating a tag is a single
delete query and no second round trip is needed to read the tags::

    use Symfony\Component\Cache\Adapter\MongoDbTagAwareAdapter;

    $cache = new MongoDbTagAwareAdapter('mongodb://localhost/bookstore');
    $cache->setup();

.. _`MongoDB PHP extension`: https://www.php.net/book.mongodb
.. _`mongodb/mongodb`: https://packagist.org/packages/mongodb/mongodb
.. _`MongoDB Connection String`: https://www.mongodb.com/docs/manual/reference/connection-string/
.. _`TTL index`: https://www.mongodb.com/docs/manual/tutorial/expire-data/
.. _`MongoDBClient::__construct`: https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBClient__construct/
