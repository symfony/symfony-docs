How to Use MongoDbSessionHandler to Store Sessions in a MongoDB Database
========================================================================

The default Symfony session storage writes the session information to files.
Some medium to large websites use a NoSQL database called MongoDB to store the
session values instead of files, because databases are easier to use and scale
in a multi-webserver environment.

Symfony has a built-in solution for NoSQL database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`.
MongoDB is an open-source document database that provides high performance,
high availability, and automatic scaling. This article assumes that you have
already `installed and configured a MongoDB server`_. To use it, you just
need to change/add some parameters in the main configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id:  session.handler.mongo
                cookie_lifetime: 2592000 # optional, it is set to 30 days here
                gc_maxlifetime: 2592000 # optional, it is set to 30 days here

        parameters:
            # ...
            mongo.session.options:
                database: session_db # your MongoDB database name
                collection: session  # your MongoDB collection name
            mongodb_host: 1.2.3.4 # your MongoDB server's IP
            mongodb_username: my_username
            mongodb_password: my_password

        services:
            # ...
            mongo_client:
                class: MongoClient
                # if using a username and password
                arguments: [mongodb://%mongodb_username%:%mongodb_password%@%mongodb_host%:27017]
                # if not using a username and password
                arguments: [mongodb://%mongodb_host%:27017]
            session.handler.mongo:
                class: Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler
                arguments: [@mongo_client, %mongo.session.options%]

Setting Up the MongoDB Collection
---------------------------------
Because MongoDB uses dynamic collection schemas, you do not need to do anything to initialize your
session collection.

.. _installed and configured a MongoDB server: http://docs.mongodb.org/manual/installation/