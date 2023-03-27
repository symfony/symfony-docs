.. _couchbase-collection-adapter:

Couchbase Collection Cache Adapter
==================================

.. versionadded:: 5.4

    The Couchbase Collection adapter was introduced in Symfony 5.4.

This adapter stores the values in-memory using one (or more) `Couchbase server`_
instances. Unlike the :ref:`APCu adapter <apcu-adapter>`, and similarly to the
:ref:`Memcached adapter <memcached-adapter>`, it is not limited to the current server's
shared memory; you can store contents independent of your PHP environment.
The ability to utilize a cluster of servers to provide redundancy and/or fail-over
is also available.

.. caution::

    **Requirements:** The `Couchbase PHP extension`_ as well as a `Couchbase server`_
    must be installed, active, and running to use this adapter. Version ``3.0`` or
    greater of the `Couchbase PHP extension`_ is required for this adapter.

This adapter expects a `Couchbase Collection`_ instance to be passed as the first
parameter. A namespace and default cache lifetime can optionally be passed as
the second and third parameters::

    use Symfony\Component\Cache\Adapter\CouchbaseCollectionAdapter;

    $cache = new CouchbaseCollectionAdapter(
        // the client object that sets options and adds the server instance(s)
        $client,

        // a string prefixed to the keys of the items stored in this cache
        $namespace,

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely
        $defaultLifetime
    );


Configure the Connection
------------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\CouchbaseCollectionAdapter::createConnection`
helper method allows creating and configuring a `Couchbase Collection`_ class instance using a
`Data Source Name (DSN)`_ or an array of DSNs::

    use Symfony\Component\Cache\Adapter\CouchbaseCollectionAdapter;

    // pass a single DSN string to register a single server with the client
    $client = CouchbaseCollectionAdapter::createConnection(
        'couchbase://localhost'
        // the DSN can include config options (pass them as a query string):
        // 'couchbase://localhost:11210?operationTimeout=10'
        // 'couchbase://localhost:11210?operationTimeout=10&configTimout=20'
    );

    // pass an array of DSN strings to register multiple servers with the client
    $client = CouchbaseCollectionAdapter::createConnection([
        'couchbase://10.0.0.100',
        'couchbase://10.0.0.101',
        'couchbase://10.0.0.102',
        // etc...
    ]);

    // a single DSN can define multiple servers using the following syntax:
    // host[hostname-or-IP:port] (where port is optional). Sockets must include a trailing ':'
    $client = CouchbaseCollectionAdapter::createConnection(
        'couchbase:?host[localhost]&host[localhost:12345]'
    );


Configure the Options
---------------------

The :method:`Symfony\\Component\\Cache\\Adapter\\CouchbaseCollectionAdapter::createConnection`
helper method also accepts an array of options as its second argument. The
expected format is an associative array of ``key => value`` pairs representing
option names and their respective values::

    use Symfony\Component\Cache\Adapter\CouchbaseCollectionAdapter;

    $client = CouchbaseCollectionAdapter::createConnection(
        // a DSN string or an array of DSN strings
        [],

        // associative array of configuration options
        [
            'username' => 'xxxxxx',
            'password' => 'yyyyyy',
            'configTimeout' => '100',
        ]
    );

Available Options
~~~~~~~~~~~~~~~~~

``username`` (type: ``string``)
    Username for connection ``CouchbaseCluster``.

``password`` (type: ``string``)
    Password of connection ``CouchbaseCluster``.

``operationTimeout`` (type: ``int``, default: ``2500000``)
    The operation timeout (in microseconds) is the maximum amount of time the library will
    wait for an operation to receive a response before invoking its callback with a failure status.

``configTimeout`` (type: ``int``, default: ``5000000``)
    How long (in microseconds) the client will wait to obtain the initial configuration.

``configNodeTimeout`` (type: ``int``, default: ``2000000``)
    Per-node configuration timeout (in microseconds).

``viewTimeout`` (type: ``int``, default: ``75000000``)
    The I/O timeout (in microseconds) for HTTP requests to Couchbase Views API.

``httpTimeout`` (type: ``int``, default: ``75000000``)
    The I/O timeout (in microseconds) for HTTP queries (management API).

``configDelay`` (type: ``int``, default: ``10000``)
    Config refresh throttling
    Modify the amount of time (in microseconds) before the configuration error threshold will forcefully be set to its maximum number forcing a configuration refresh.

``htconfigIdleTimeout`` (type: ``int``, default: ``4294967295``)
    Idling/Persistence for HTTP bootstrap (in microseconds).

``durabilityInterval`` (type: ``int``, default: ``100000``)
    The time (in microseconds) the client will wait between repeated probes to a given server.

``durabilityTimeout`` (type: ``int``, default: ``5000000``)
    The time (in microseconds) the client will spend sending repeated probes to a given key's vBucket masters and replicas before they are deemed not to have satisfied the durability requirements.

.. tip::

    Reference the `Couchbase Collection`_ extension's `predefined constants`_ documentation
    for additional information about the available options.

.. _`Couchbase PHP extension`: https://docs.couchbase.com/sdk-api/couchbase-php-client/namespaces/couchbase.html
.. _`predefined constants`: https://docs.couchbase.com/sdk-api/couchbase-php-client/classes/Couchbase-Bucket.html
.. _`Couchbase server`: https://couchbase.com/
.. _`Couchbase Collection`: https://docs.couchbase.com/sdk-api/couchbase-php-client/classes/Couchbase-Collection.html
.. _`Data Source Name (DSN)`: https://en.wikipedia.org/wiki/Data_source_name
