How to Use MongoDbSessionHandler to Store Sessions in a MongoDB Database
========================================================================

The default Symfony session storage writes the session information to files.
Some medium to large websites use a NoSQL database called MongoDB to store the
session values instead of files, because databases are easier to use and scale
in a multi-webserver environment.

Symfony has a built-in solution for NoSQL database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`.
To use this, you will need to:

A) Register a ``MongoDbSessionHandler`` service;

B) Configure this under ``framework.session.handler_id`` configuration.

To see how to configure a similar handler, see :doc:`/doctrine/pdo_session_storage`.

Setting Up the MongoDB Collection
---------------------------------

You do not need to do anything to initialize your session collection. However, you
may want to add an index to improve garbage collection performance. From the
`MongoDB shell`_:

.. code-block:: javascript

    use session_db
    db.session.ensureIndex( { "expires_at": 1 }, { expireAfterSeconds: 0 } )

.. _MongoDB shell: https://docs.mongodb.com/manual/mongo/
