How to Use MongoDbSessionHandler to Store Sessions in a MongoDB Database
========================================================================

The default Symfony session storage writes the session information to files.
Some medium to large websites use a NoSQL database called MongoDB to store the
session values instead of files, because databases are easier to use and scale
in a multi-webserver environment.

Symfony has a built-in solution for NoSQL database session storage called
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`.
MongoDB is an open-source document database that provides high performance,
high availability and automatic scaling. This article assumes that you have
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

        services:
            # ...
            mongo_client:
                class: MongoClient
                # if using a username and password
                arguments: ['mongodb://%mongodb_username%:%mongodb_password%@%mongodb_host%:27017']
                # if not using a username and password
                arguments: ['mongodb://%mongodb_host%:27017']
            session.handler.mongo:
                class: Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler
                arguments: ['@mongo_client', '%mongo.session.options%']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->

                <!-- cookie-lifetime and gc-maxlifetime are optional and set to
                     30 days in this example -->
                <framework:session handler-id="session.handler.mongo"
                    cookie-lifetime="2592000"
                    gc-maxlifetime="2592000"
                />
            </framework:config>

            <services>
                <service id="mongo_client" class="MongoClient">
                    <!-- if using a username and password -->
                    <argument>mongodb://%mongodb_username%:%mongodb_password%@%mongodb_host%:27017</argument>

                    <!-- if not using a username and password -->
                    <argument>mongodb://%mongodb_host%:27017</argument>
                </service>

                <service id="session.handler.mongo" class="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler">
                    <argument type="service">mongo_client</argument>
                    <argument>%mongo.session.options%</argument>
                </service>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        $container->loadFromExtension('framework', array(
            'session' => array(
                // ...
                'handler_id'      => 'session.handler.mongo',
                'cookie_lifetime' => 2592000, // optional, it is set to 30 days here
                'gc_maxlifetime'  => 2592000, // optional, it is set to 30 days here
            ),
        ));

        $container->setDefinition('mongo_client', new Definition('MongoClient', array(
            // if using a username and password
            array('mongodb://%mongodb_username%:%mongodb_password%@%mongodb_host%:27017'),
            // if not using a username and password
            array('mongodb://%mongodb_host%:27017'),
        )));

        $container->setDefinition('session.handler.mongo', new Definition(
            'Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler',
            array(new Reference('mongo_client'), '%mongo.session.options%')
        ));

The parameters used above should be defined somewhere in your application, often in your main
parameters configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/parameters.yml
        parameters:
            # ...
            mongo.session.options:
                database: session_db # your MongoDB database name
                collection: session  # your MongoDB collection name
            mongodb_host: 1.2.3.4 # your MongoDB server's IP
            mongodb_username: my_username
            mongodb_password: my_password

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <parameters>
                <parameter key="mongo.session.options" type="collection">
                    <!-- your MongoDB database name -->
                    <parameter key="database">session_db</parameter>
                    <!-- your MongoDB collection name -->
                    <parameter key="collection">session</parameter>
                </parameter>
                <!-- your MongoDB server's IP -->
                <parameter key="mongodb_host">1.2.3.4</parameter>
                <parameter key="mongodb_username">my_username</parameter>
                <parameter key="mongodb_password">my_password</parameter>
            </parameters>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('mongo.session.options', array(
            'database'   => 'session_db', // your MongoDB database name
            'collection' => 'session',  // your MongoDB collection name
        ));
        $container->setParameter('mongodb_host', '1.2.3.4'); // your MongoDB server's IP
        $container->setParameter('mongodb_username', 'my_username');
        $container->setParameter('mongodb_password', 'my_password');

Setting Up the MongoDB Collection
---------------------------------

Because MongoDB uses dynamic collection schemas, you do not need to do anything to initialize your
session collection. However, you may want to add an index to improve garbage collection performance.
From the `MongoDB shell`_:

.. code-block:: javascript

    use session_db
    db.session.ensureIndex( { "expires_at": 1 }, { expireAfterSeconds: 0 } )

.. _installed and configured a MongoDB server: http://docs.mongodb.org/manual/installation/
.. _MongoDB shell: http://docs.mongodb.org/v2.2/tutorial/getting-started-with-the-mongo-shell/
