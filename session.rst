Sessions
========

The Symfony HttpFoundation component has a very powerful and flexible session
subsystem which is designed to provide session management that you can use to
store information about the user between requests through a clear
object-oriented interface using a variety of session storage drivers.

Symfony sessions are designed to replace the usage of the ``$_SESSION`` super
global and native PHP functions related to manipulating the session like
``session_start()``, ``session_regenerate_id()``, ``session_id()``,
``session_name()``, and ``session_destroy()``.

.. note::

    Sessions are only started if you read or write from it.

Installation
------------

You need to install the HttpFoundation component to handle sessions:

.. code-block:: terminal

    $ composer require symfony/http-foundation

.. _session-intro:

Basic Usage
-----------

The session is available through the ``Request`` object and the ``RequestStack``
service. Symfony injects the ``request_stack`` service in services and controllers
if you type-hint an argument with :class:`Symfony\\Component\\HttpFoundation\\RequestStack`::

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Component\HttpFoundation\RequestStack;

        class SomeService
        {
            public function __construct(
                private RequestStack $requestStack,
            ) {
                // Accessing the session in the constructor is *NOT* recommended, since
                // it might not be accessible yet or lead to unwanted side-effects
                // $this->session = $requestStack->getSession();
            }

            public function someMethod()
            {
                $session = $this->requestStack->getSession();

                // ...
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\HttpFoundation\Session\Session;

        $session = new Session();
        $session->start();

From a Symfony controller, you can also type-hint an argument with
:class:`Symfony\\Component\\HttpFoundation\\Request`::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    public function index(Request $request): Response
    {
        $session = $request->getSession();

        // ...
    }

Session Attributes
------------------

PHP's session management requires the use of the ``$_SESSION`` super-global.
However, this interferes with code testability and encapsulation in an OOP
paradigm. To help overcome this, Symfony uses *session bags* linked to the
session to encapsulate a specific dataset of **attributes**.

This approach mitigates namespace pollution within the ``$_SESSION``
super-global because each bag stores all its data under a unique namespace.
This allows Symfony to peacefully co-exist with other applications or libraries
that might use the ``$_SESSION`` super-global and all data remains completely
compatible with Symfony's session management.

A session bag is a PHP object that acts like an array::

    // stores an attribute for reuse during a later user request
    $session->set('attribute-name', 'attribute-value');

    // gets an attribute by name
    $foo = $session->get('foo');

    // the second argument is the value returned when the attribute doesn't exist
    $filters = $session->get('filters', []);

Stored attributes remain in the session for the remainder of that user's session.
By default, session attributes are key-value pairs managed with the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag`
class.

.. tip::

    Sessions are automatically started whenever you read, write or even check
    for the existence of data in the session. This may hurt your application
    performance because all users will receive a session cookie. In order to
    prevent starting sessions for anonymous users, you must *completely* avoid
    accessing the session.

.. _flash-messages:

Flash Messages
--------------

You can store special messages, called "flash" messages, on the user's session.
By design, flash messages are meant to be used exactly once: they vanish from
the session automatically as soon as you retrieve them. This feature makes
"flash" messages particularly great for storing user notifications.

For example, imagine you're processing a :doc:`form </forms>` submission::

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\Response;
        // ...

        public function update(Request $request): Response
        {
            // ...

            if ($form->isSubmitted() && $form->isValid()) {
                // do some sort of processing

                $this->addFlash(
                    'notice',
                    'Your changes were saved!'
                );
                // $this->addFlash() is equivalent to $request->getSession()->getFlashBag()->add()

                return $this->redirectToRoute(/* ... */);
            }

            return $this->render(/* ... */);
        }

    .. code-block:: php-standalone

        use Symfony\Component\HttpFoundation\Session\Session;

        $session = new Session();
        $session->start();

        // retrieve the flash messages bag
        $flashes = $session->getFlashBag();

        // add flash messages
        $flashes->add(
            'warning',
            'Your config file is writable, it should be set read-only'
        );
        $flashes->add('error', 'Failed to update name');
        $flashes->add('error', 'Another error');

After processing the request, the controller sets a flash message in the session
and then redirects. The message key (``notice`` in this example) can be anything:
you'll use this key to retrieve the message.

In the template of the next page (or even better, in your base layout template),
read any flash messages from the session using the ``flashes()`` method provided
by the :ref:`Twig global app variable <twig-app-variable>`:

.. configuration-block::

    .. code-block:: html+twig

        {# templates/base.html.twig #}

        {# read and display just one flash message type #}
        {% for message in app.flashes('notice') %}
            <div class="flash-notice">
                {{ message }}
            </div>
        {% endfor %}

        {# read and display several types of flash messages #}
        {% for label, messages in app.flashes(['success', 'warning']) %}
            {% for message in messages %}
                <div class="flash-{{ label }}">
                    {{ message }}
                </div>
            {% endfor %}
        {% endfor %}

        {# read and display all flash messages #}
        {% for label, messages in app.flashes %}
            {% for message in messages %}
                <div class="flash-{{ label }}">
                    {{ message }}
                </div>
            {% endfor %}
        {% endfor %}

    .. code-block:: php-standalone

        // display warnings
        foreach ($session->getFlashBag()->get('warning', []) as $message) {
            echo '<div class="flash-warning">'.$message.'</div>';
        }

        // display errors
        foreach ($session->getFlashBag()->get('error', []) as $message) {
            echo '<div class="flash-error">'.$message.'</div>';
        }

        // display all flashes at once
        foreach ($session->getFlashBag()->all() as $type => $messages) {
            foreach ($messages as $message) {
                echo '<div class="flash-'.$type.'">'.$message.'</div>';
            }
        }

It's common to use ``notice``, ``warning`` and ``error`` as the keys of the
different types of flash messages, but you can use any key that fits your
needs.

.. tip::

    You can use the
    :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::peek`
    method instead to retrieve the message while keeping it in the bag.

Configuration
-------------

In the Symfony framework, sessions are enabled by default. Session storage and
other configuration can be controlled under the :ref:`framework.session
configuration <config-framework-session>` in
``config/packages/framework.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # Enables session support. Note that the session will ONLY be started if you read or write from it.
            # Remove or comment this section to explicitly disable session support.
            session:
                # ID of the service used for session storage
                # NULL means that Symfony uses PHP default session mechanism
                handler_id: null
                # improves the security of the cookies used for sessions
                cookie_secure: auto
                cookie_samesite: lax
                storage_factory_id: session.storage.factory.native

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!--
                    Enables session support. Note that the session will ONLY be started if you read or write from it.
                    Remove or comment this section to explicitly disable session support.
                    handler-id: ID of the service used for session storage
                                NULL means that Symfony uses PHP default session mechanism
                    cookie-secure and cookie-samesite: improves the security of the cookies used for sessions
                -->
                <framework:session handler-id="null"
                                   cookie-secure="auto"
                                   cookie-samesite="lax"
                                   storage_factory_id="session.storage.factory.native"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Cookie;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                // Enables session support. Note that the session will ONLY be started if you read or write from it.
                // Remove or comment this section to explicitly disable session support.
                ->enabled(true)
                // ID of the service used for session storage
                // NULL means that Symfony uses PHP default session mechanism
                ->handlerId(null)
                // improves the security of the cookies used for sessions
                ->cookieSecure('auto')
                ->cookieSamesite(Cookie::SAMESITE_LAX)
                ->storageFactoryId('session.storage.factory.native')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HttpFoundation\Cookie;
        use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
        use Symfony\Component\HttpFoundation\Session\Session;
        use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

        $storage = new NativeSessionStorage([
            'cookie_secure' => 'auto',
            'cookie_samesite' => Cookie::SAMESITE_LAX,
        ]);
        $session = new Session($storage);

Setting the ``handler_id`` config option to ``null`` means that Symfony will
use the native PHP session mechanism. The session metadata files will be stored
outside of the Symfony application, in a directory controlled by PHP. Although
this usually simplifies things, some session expiration related options may not
work as expected if other applications that write to the same directory have
short max lifetime settings.

If you prefer, you can use the ``session.handler.native_file`` service as
``handler_id`` to let Symfony manage the sessions itself. Another useful option
is ``save_path``, which defines the directory where Symfony will store the
session metadata files:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: 'session.handler.native_file'
                save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session enabled="true"
                                   handler-id="session.handler.native_file"
                                   save-path="%kernel.project_dir%/var/sessions/%kernel.environment%"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                // ...
                ->handlerId('session.handler.native_file')
                ->savePath('%kernel.project_dir%/var/sessions/%kernel.environment%')
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HttpFoundation\Cookie;
        use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
        use Symfony\Component\HttpFoundation\Session\Session;
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
        use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

        $handler = new NativeFileSessionHandler('/var/sessions');
        $storage = new NativeSessionStorage([], $handler);
        $session = new Session($storage);

Check out the Symfony config reference to learn more about the other available
:ref:`Session configuration options <config-framework-session>`.

.. caution::

    Symfony sessions are incompatible with ``php.ini`` directive
    ``session.auto_start = 1`` This directive should be turned off in
    ``php.ini``, in the web server directives or in ``.htaccess``.

Session Idle Time/Keep Alive
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are often circumstances where you may want to protect, or minimize
unauthorized use of a session when a user steps away from their terminal while
logged in by destroying the session after a certain period of idle time. For
example, it is common for banking applications to log the user out after just
5 to 10 minutes of inactivity. Setting the cookie lifetime here is not
appropriate because that can be manipulated by the client, so we must do the expiry
on the server side. The easiest way is to implement this via :ref:`session garbage collection <session-garbage-collection>`
which runs reasonably frequently. The ``cookie_lifetime`` would be set to a
relatively high value, and the garbage collection ``gc_maxlifetime`` would be set
to destroy sessions at whatever the desired idle period is.

The other option is specifically check if a session has expired after the
session is started. The session can be destroyed as required. This method of
processing can allow the expiry of sessions to be integrated into the user
experience, for example, by displaying a message.

Symfony records some metadata about each session to give you fine control over
the security settings::

    $session->getMetadataBag()->getCreated();
    $session->getMetadataBag()->getLastUsed();

Both methods return a Unix timestamp (relative to the server).

This metadata can be used to explicitly expire a session on access::

    $session->start();
    if (time() - $session->getMetadataBag()->getLastUsed() > $maxIdleTime) {
        $session->invalidate();
        throw new SessionExpired(); // redirect to expired session page
    }

It is also possible to tell what the ``cookie_lifetime`` was set to for a
particular cookie by reading the ``getLifetime()`` method::

    $session->getMetadataBag()->getLifetime();

The expiry time of the cookie can be determined by adding the created
timestamp and the lifetime.

.. _session-garbage-collection:

Configuring Garbage Collection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a session opens, PHP will call the ``gc`` handler randomly according to the
probability set by ``session.gc_probability`` / ``session.gc_divisor``. For
example if these were set to ``5/100`` respectively, it would mean a probability
of 5%. Similarly, ``3/4`` would mean a 3 in 4 chance of being called, i.e. 75%.

If the garbage collection handler is invoked, PHP will pass the value stored in
the ``php.ini`` directive ``session.gc_maxlifetime``. The meaning in this context is
that any stored session that was saved more than ``gc_maxlifetime`` ago should be
deleted. This allows one to expire records based on idle time.

However, some operating systems (e.g. Debian) do their own session handling and set
the ``session.gc_probability`` variable to ``0`` to stop PHP doing garbage
collection. That's why Symfony now overwrites this value to ``1``.

If you wish to use the original value set in your ``php.ini``, add the following
configuration:

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        session:
            # ...
            gc_probability: null

You can configure these settings by passing ``gc_probability``, ``gc_divisor``
and ``gc_maxlifetime`` in an array to the constructor of
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
or to the :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage::setOptions`
method.

.. _session-database:

Store Sessions in a Database
----------------------------

Symfony stores sessions in files by default. If your application is served by
multiple servers, you'll need to use a database instead to make sessions work
across different servers.

Symfony can store sessions in all kinds of databases (relational, NoSQL and
key-value) but recommends key-value databases like Redis to get best
performance.

Store Sessions in a key-value Database (Redis)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This section assumes that you have a fully-working Redis server and have also
installed and configured the `phpredis extension`_.

You have two different options to use Redis to store sessions:

The first PHP-based option is to configure Redis session handler directly
in the server ``php.ini`` file:

.. code-block:: ini

    ; php.ini
    session.save_handler = redis
    session.save_path = "tcp://192.168.0.178:6379?auth=REDIS_PASSWORD"

The second option is to configure Redis sessions in Symfony. First, define
a Symfony service for the connection to the Redis server:

.. configuration-block::

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    # you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                    # which define the prefix to use for the keys to avoid collision on the Redis server
                    # and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                    # - { 'prefix': 'my_prefix', 'ttl': 600 }

            Redis:
                # you can also use \RedisArray, \RedisCluster or \Predis\Client classes
                class: Redis
                calls:
                    - connect:
                        - '%env(REDIS_HOST)%'
                        - '%env(int:REDIS_PORT)%'

                    # uncomment the following if your Redis server requires a password
                    # - auth:
                    #     - '%env(REDIS_PASSWORD)%'

                    # uncomment the following if your Redis server requires a user and a password (when user is not default)
                    # - auth:
                    #     - ['%env(REDIS_USER)%','%env(REDIS_PASSWORD)%']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- you can also use \RedisArray, \RedisCluster or \Predis\Client classes -->
                <service id="Redis" class="Redis">
                    <call method="connect">
                        <argument>%env(REDIS_HOST)%</argument>
                        <argument>%env(int:REDIS_PORT)%</argument>
                    </call>

                    <!-- uncomment the following if your Redis server requires a password:
                    <call method="auth">
                        <argument>%env(REDIS_PASSWORD)%</argument>
                    </call> -->

                    <!-- uncomment the following if your Redis server requires a user and a password (when user is not default):
                    <call method="auth">
                        <argument>%env(REDIS_USER)%</argument>
                        <argument>%env(REDIS_PASSWORD)%</argument>
                    </call> -->
                </service>

                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                    <argument type="service" id="Redis"/>
                    <!-- you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                         which define the prefix to use for the keys to avoid collision on the Redis server
                         and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                    <argument type="collection">
                        <argument key="prefix">my_prefix</argument>
                        <argument key="ttl">600</argument>
                    </argument> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $container
            // you can also use \RedisArray, \RedisCluster or \Predis\Client classes
            ->register('Redis', \Redis::class)
            ->addMethodCall('connect', ['%env(REDIS_HOST)%', '%env(int:REDIS_PORT)%'])
            // uncomment the following if your Redis server requires a password:
            // ->addMethodCall('auth', ['%env(REDIS_PASSWORD)%'])
            // uncomment the following if your Redis server requires a user and a password (when user is not default):
            // ->addMethodCall('auth', ['%env(REDIS_USER)%', '%env(REDIS_PASSWORD)%'])

            ->register(RedisSessionHandler::class)
            ->addArgument(
                new Reference('Redis'),
                // you can optionally pass an array of options. The only options are 'prefix' and 'ttl',
                // which define the prefix to use for the keys to avoid collision on the Redis server
                // and the expiration time for any given entry (in seconds), defaults are 'sf_s' and null:
                // ['prefix' => 'my_prefix', 'ttl' => 600],
            )
        ;

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            session:
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:session handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(RedisSessionHandler::class)
            ;
        };

Symfony will now use your Redis server to read and write the session data. The
main drawback of this solution is that Redis does not perform session locking,
so you can face *race conditions* when accessing sessions. For example, you may
see an *"Invalid CSRF token"* error because two requests were made in parallel
and only the first one stored the CSRF token in the session.

.. seealso::

    If you use Memcached instead of Redis, follow a similar approach but
    replace ``RedisSessionHandler`` by
    :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MemcachedSessionHandler`.

.. _session-database-pdo:

Store Sessions in a Relational Database (MariaDB, MySQL, PostgreSQL)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony includes a
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler`
to store sessions in relational databases like MariaDB, MySQL and PostgreSQL.
To use it, first register a new handler service with your database credentials:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - '%env(DATABASE_URL)%'

                    # you can also use PDO configuration, but requires passing two arguments
                    # - 'mysql:dbname=mydatabase; host=myhost; port=myport'
                    # - { db_username: myuser, db_password: mypassword }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler">
                    <argument>%env(DATABASE_URL)%</argument>

                    <!-- you can also use PDO configuration, but requires passing two arguments: -->
                    <!-- <argument>mysql:dbname=mydatabase; host=myhost; port=myport</argument>
                        <argument type="collection">
                            <argument key="db_username">myuser</argument>
                            <argument key="db_password">mypassword</argument>
                        </argument> -->
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(PdoSessionHandler::class)
                ->args([
                    env('DATABASE_URL'),
                    // you can also use PDO configuration, but requires passing two arguments:
                    // 'mysql:dbname=mydatabase; host=myhost; port=myport',
                    // ['db_username' => 'myuser', 'db_password' => 'mypassword'],
                ])
            ;
        };

.. tip::

    When using MySQL as the database, the DSN defined in ``DATABASE_URL`` can
    contain the ``charset`` and ``unix_socket`` options as query string parameters.

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:session
                    handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(PdoSessionHandler::class)
            ;
        };

Configuring the Session Table and Column Names
..............................................

The table used to store sessions is called ``sessions`` by default and defines
certain column names. You can configure these values with the second argument
passed to the ``PdoSessionHandler`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler:
                arguments:
                    - '%env(DATABASE_URL)%'
                    - { db_table: 'customer_session', db_id_col: 'guid' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler">
                    <argument>%env(DATABASE_URL)%</argument>
                    <argument type="collection">
                        <argument key="db_table">customer_session</argument>
                        <argument key="db_id_col">guid</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(PdoSessionHandler::class)
                ->args([
                    env('DATABASE_URL'),
                    ['db_table' => 'customer_session', 'db_id_col' => 'guid'],
                ])
            ;
        };

These are parameters that you can configure:

``db_table`` (default ``sessions``):
    The name of the session table in your database;

``db_username``: (default: ``''``)
    The username used to connect when using the PDO configuration (when using
    the connection based on the ``DATABASE_URL`` env var, it overrides the
    username defined in the env var).

``db_password``: (default: ``''``)
    The password used to connect when using the PDO configuration (when using
    the connection based on the ``DATABASE_URL`` env var, it overrides the
    password defined in the env var).

``db_id_col`` (default ``sess_id``):
    The name of the column where to store the session ID (column type: ``VARCHAR(128)``);

``db_data_col`` (default ``sess_data``):
    The name of the column where to store the session data (column type: ``BLOB``);

``db_time_col`` (default ``sess_time``):
    The name of the column where to store the session creation timestamp (column type: ``INTEGER``);

``db_lifetime_col`` (default ``sess_lifetime``):
    The name of the column where to store the session lifetime (column type: ``INTEGER``);

``db_connection_options`` (default: ``[]``)
    An array of driver-specific connection options;

``lock_mode`` (default: ``LOCK_TRANSACTIONAL``)
    The strategy for locking the database to avoid *race conditions*. Possible
    values are ``LOCK_NONE`` (no locking), ``LOCK_ADVISORY`` (application-level
    locking) and ``LOCK_TRANSACTIONAL`` (row-level locking).

Preparing the Database to Store Sessions
........................................

Before storing sessions in the database, you must create the table that stores
the information. The session handler provides a method called
:method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\PdoSessionHandler::createTable`
to set up this table for you according to the database engine used::

    try {
        $sessionHandlerService->createTable();
    } catch (\PDOException $exception) {
        // the table could not be created for some reason
    }

If you prefer to set up the table yourself, it's recommended to generate an
empty database migration with the following command:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:generate

Then, find the appropriate SQL for your database below, add it to the migration
file and run the migration with the following command:

.. code-block:: terminal

    $ php bin/console doctrine:migrations:migrate

.. _mysql:

MariaDB/MySQL
+++++++++++++

.. code-block:: sql

    CREATE TABLE `sessions` (
        `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
        `sess_data` BLOB NOT NULL,
        `sess_lifetime` INTEGER UNSIGNED NOT NULL,
        `sess_time` INTEGER UNSIGNED NOT NULL,
        INDEX `sessions_sess_lifetime_idx` (`sess_lifetime`)
    ) COLLATE utf8mb4_bin, ENGINE = InnoDB;

.. note::

    A ``BLOB`` column type (which is the one used by default by ``createTable()``)
    stores up to 64 kb. If the user session data exceeds this, an exception may
    be thrown or their session will be silently reset. Consider using a ``MEDIUMBLOB``
    if you need more space.

PostgreSQL
++++++++++

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data BYTEA NOT NULL,
        sess_lifetime INTEGER NOT NULL,
        sess_time INTEGER NOT NULL
    );
    CREATE INDEX sessions_sess_lifetime_idx ON sessions (sess_lifetime);

Microsoft SQL Server
++++++++++++++++++++

.. code-block:: sql

    CREATE TABLE sessions (
        sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
        sess_data NVARCHAR(MAX) NOT NULL,
        sess_lifetime INTEGER NOT NULL,
        sess_time INTEGER NOT NULL,
        INDEX sessions_sess_lifetime_idx (sess_lifetime)
    );

.. _session-database-mongodb:

Store Sessions in a NoSQL Database (MongoDB)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony includes a
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MongoDbSessionHandler`
to store sessions in the MongoDB NoSQL database. First, make sure to have a
working MongoDB connection in your Symfony application as explained in the
`DoctrineMongoDBBundle configuration`_ article.

Then, register a new handler service for ``MongoDbSessionHandler`` and pass it
the MongoDB connection as argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler:
                arguments:
                    - '@doctrine_mongodb.odm.default_connection'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler">
                    <argument type="service">doctrine_mongodb.odm.default_connection</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(MongoDbSessionHandler::class)
                ->args([
                    service('doctrine_mongodb.odm.default_connection'),
                ])
            ;
        };

Next, use the :ref:`handler_id <config-framework-session-handler-id>`
configuration option to tell Symfony to use this service as the session handler:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- ... -->
                <framework:session
                    handler-id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(MongoDbSessionHandler::class)
            ;
        };

.. note::

    MongoDB ODM 1.x only works with the legacy driver, which is no longer
    supported by the Symfony session class. Install the ``alcaeus/mongo-php-adapter``
    package to retrieve the underlying ``\MongoDB\Client`` object or upgrade to
    MongoDB ODM 2.0.

That's all! Symfony will now use your MongoDB server to read and write the
session data. You do not need to do anything to initialize your session
collection. However, you may want to add an index to improve garbage collection
performance. Run this from the `MongoDB shell`_:

.. code-block:: javascript

    use session_db
    db.session.createIndex( { "expires_at": 1 }, { expireAfterSeconds: 0 } )

Configuring the Session Field Names
...................................

The collection used to store sessions defines certain field names. You can
configure these values with the second argument passed to the
``MongoDbSessionHandler`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler:
                arguments:
                    - '@doctrine_mongodb.odm.default_connection'
                    - { id_field: '_guid', 'expiry_field': 'eol' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler">
                    <argument type="service">doctrine_mongodb.odm.default_connection</argument>
                    <argument type="collection">
                        <argument key="id_field">_guid</argument>
                        <argument key="expiry_field">eol</argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

        return static function (ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(MongoDbSessionHandler::class)
                ->args([
                    service('doctrine_mongodb.odm.default_connection'),
                    ['id_field' => '_guid', 'expiry_field' => 'eol'],
                ])
            ;
        };

These are parameters that you can configure:

``id_field`` (default ``_id``):
    The name of the field where to store the session ID;

``data_field`` (default ``data``):
    The name of the field where to store the session data;

``time_field`` (default ``time``):
    The name of the field where to store the session creation timestamp;

``expiry_field`` (default ``expires_at``):
    The name of the field where to store the session lifetime.

Migrating Between Session Handlers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application changes the way sessions are stored, use the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\MigratingSessionHandler`
to migrate between old and new save handlers without losing session data.

This is the recommended migration workflow:

#. Switch to the migrating handler, with your new handler as the write-only one.
   The old handler behaves as usual and sessions get written to the new one::

       $sessionStorage = new MigratingSessionHandler($oldSessionStorage, $newSessionStorage);

#. After your session gc period, verify that the data in the new handler is correct.
#. Update the migrating handler to use the old handler as the write-only one, so
   the sessions will now be read from the new handler. This step allows easier rollbacks::

       $sessionStorage = new MigratingSessionHandler($newSessionStorage, $oldSessionStorage);

#. After verifying that the sessions in your application are working, switch
   from the migrating handler to the new handler.

.. _session-configure-ttl:

Configuring the Session TTL
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony by default will use PHP's ini setting ``session.gc_maxlifetime`` as
session lifetime. When you store sessions in a database, you can also
configure your own TTL in the framework configuration or even at runtime.

.. note::

    Changing the ini setting is not possible once the session is started so
    if you want to use a different TTL depending on which user is logged
    in, you must do it at runtime using the callback method below.

Configure the TTL
.................

You need to pass the TTL in the options array of the session handler you are using:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    - { 'ttl': 600 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                <argument type="service" id="Redis"/>
                <argument type="collection">
                    <argument key="ttl">600</argument>
                </argument>
            </service>
        </services>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $services
            ->set(RedisSessionHandler::class)
            ->args([
                service('Redis'),
                ['ttl' => 600],
            ]);

Configure the TTL Dynamically at Runtime
........................................

If you would like to have a different TTL for different users or sessions
for whatever reason, this is also possible by passing a callback as the TTL
value. The callback will be called right before the session is written and
has to return an integer which will be used as TTL.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    - { 'ttl': !closure '@my.ttl.handler' }

            my.ttl.handler:
                class: Some\InvokableClass # some class with an __invoke() method
                arguments:
                    # Inject whatever dependencies you need to be able to resolve a TTL for the current session
                    - '@security'

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                <argument type="service" id="Redis"/>
                <argument type="collection">
                    <argument key="ttl" type="closure" id="my.ttl.handler"/>
                </argument>
            </service>
            <!-- some class with an __invoke() method -->
            <service id="my.ttl.handler" class="Some\InvokableClass">
                <!-- Inject whatever dependencies you need to be able to resolve a TTL for the current session -->
                <argument type="service" id="security"/>
            </service>
        </services>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $services
            ->set(RedisSessionHandler::class)
            ->args([
                service('Redis'),
                ['ttl' => closure(service('my.ttl.handler'))],
            ]);

        $services
            // some class with an __invoke() method
            ->set('my.ttl.handler', 'Some\InvokableClass')
            // Inject whatever dependencies you need to be able to resolve a TTL for the current session
            ->args([service('security')]);

.. _locale-sticky-session:

Making the Locale "Sticky" during a User's Session
--------------------------------------------------

Symfony stores the locale setting in the Request, which means that this setting
is not automatically saved ("sticky") across requests. But, you *can* store the
locale in the session, so that it's used on subsequent requests.

Creating a LocaleSubscriber
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create a :ref:`new event subscriber <events-subscriber>`. Typically,
``_locale`` is used as a routing parameter to signify the locale, though you
can determine the correct locale however you want::

    // src/EventSubscriber/LocaleSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\RequestEvent;
    use Symfony\Component\HttpKernel\KernelEvents;

    class LocaleSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private string $defaultLocale = 'en',
        ) {
        }

        public function onKernelRequest(RequestEvent $event)
        {
            $request = $event->getRequest();
            if (!$request->hasPreviousSession()) {
                return;
            }

            // try to see if the locale has been set as a _locale routing parameter
            if ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->set('_locale', $locale);
            } else {
                // if no explicit locale has been set on this request, use one from the session
                $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                // must be registered before (i.e. with a higher priority than) the default Locale listener
                KernelEvents::REQUEST => [['onKernelRequest', 20]],
            ];
        }
    }

If you're using the :ref:`default services.yaml configuration
<service-container-services-load-example>`, you're done! Symfony will
automatically know about the event subscriber and call the ``onKernelRequest``
method on each request.

To see it working, either set the ``_locale`` key on the session manually (e.g.
via some "Change Locale" route & controller), or create a route with the
:ref:`_locale default <translation-locale-url>`.

.. sidebar:: Explicitly Configure the Subscriber

    You can also explicitly configure it, in order to pass in the
    :ref:`default_locale <config-framework-default_locale>`:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                App\EventSubscriber\LocaleSubscriber:
                    arguments: ['%kernel.default_locale%']
                    # uncomment the next line if you are not using autoconfigure
                    # tags: [kernel.event_subscriber]

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <service id="App\EventSubscriber\LocaleSubscriber">
                        <argument>%kernel.default_locale%</argument>

                        <!-- uncomment the next line if you are not using autoconfigure -->
                        <!-- <tag name="kernel.event_subscriber"/> -->
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use App\EventSubscriber\LocaleSubscriber;

            $container->register(LocaleSubscriber::class)
                ->addArgument('%kernel.default_locale%')
                // uncomment the next line if you are not using autoconfigure
                // ->addTag('kernel.event_subscriber')
            ;

Now celebrate by changing the user's locale and seeing that it's sticky
throughout the request.

Remember, to get the user's locale, always use the :method:`Request::getLocale
<Symfony\\Component\\HttpFoundation\\Request::getLocale>` method::

    // from a controller...
    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request)
    {
        $locale = $request->getLocale();
    }

Setting the Locale Based on the User's Preferences
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You might want to improve this technique even further and define the locale
based on the user entity of the logged in user. However, since the
``LocaleSubscriber`` is called before the ``FirewallListener``, which is
responsible for handling authentication and setting the user token on the
``TokenStorage``, you have no access to the user which is logged in.

Suppose you have a ``locale`` property on your ``User`` entity and want to use
this as the locale for the given user. To accomplish this, you can hook into
the login process and update the user's session with this locale value before
they are redirected to their first page.

To do this, you need an event subscriber on the ``security.interactive_login``
event::

    // src/EventSubscriber/UserLocaleSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
    use Symfony\Component\Security\Http\SecurityEvents;

    /**
     * Stores the locale of the user in the session after the
     * login. This can be used by the LocaleSubscriber afterwards.
     */
    class UserLocaleSubscriber implements EventSubscriberInterface
    {
        public function __construct(
            private RequestStack $requestStack,
        ) {
        }

        public function onInteractiveLogin(InteractiveLoginEvent $event)
        {
            $user = $event->getAuthenticationToken()->getUser();

            if (null !== $user->getLocale()) {
                $this->requestStack->getSession()->set('_locale', $user->getLocale());
            }
        }

        public static function getSubscribedEvents()
        {
            return [
                SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            ];
        }
    }

.. caution::

    In order to update the language immediately after a user has changed their
    language preferences, you also need to update the session when you change
    the ``User`` entity.

Session Proxies
---------------

The session proxy mechanism has a variety of uses and this article demonstrates
two common ones. Rather than using the regular session handler, you can create
a custom save handler by defining a class that extends the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Proxy\\SessionHandlerProxy`
class.

Then, define the class as a :ref:`service
<service-container-creating-service>`. If you're using the :ref:`default
services.yaml configuration <service-container-services-load-example>`, that
happens automatically.

Finally, use the ``framework.session.handler_id`` configuration option to tell
Symfony to use your session handler instead of the default one:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: App\Session\CustomSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:session handler-id="App\Session\CustomSessionHandler"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use App\Session\CustomSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(CustomSessionHandler::class)
            ;
        };

Keep reading the next sections to learn how to use the session handlers in
practice to solve two common use cases: encrypt session information and define
read-only guest sessions.

Encryption of Session Data
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to encrypt the session data, you can use the proxy to encrypt and
decrypt the session as required. The following example uses the `php-encryption`_
library, but you can adapt it to any other library that you may be using::

    // src/Session/EncryptedSessionProxy.php
    namespace App\Session;

    use Defuse\Crypto\Crypto;
    use Defuse\Crypto\Key;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class EncryptedSessionProxy extends SessionHandlerProxy
    {
        private $key;

        public function __construct(\SessionHandlerInterface $handler, Key $key)
        {
            $this->key = $key;

            parent::__construct($handler);
        }

        public function read($id)
        {
            $data = parent::read($id);

            return Crypto::decrypt($data, $this->key);
        }

        public function write($id, $data)
        {
            $data = Crypto::encrypt($data, $this->key);

            return parent::write($id, $data);
        }
    }

Read-only Guest Sessions
~~~~~~~~~~~~~~~~~~~~~~~~

There are some applications where a session is required for guest users, but
where there is no particular need to persist the session. In this case you can
intercept the session before it is written::

    // src/Session/ReadOnlySessionProxy.php
    namespace App\Session;

    use App\Entity\User;
    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class ReadOnlySessionProxy extends SessionHandlerProxy
    {
        private $security;

        public function __construct(\SessionHandlerInterface $handler, Security $security)
        {
            $this->security = $security;

            parent::__construct($handler);
        }

        public function write($id, $data)
        {
            if ($this->getUser() && $this->getUser()->isGuest()) {
                return;
            }

            return parent::write($id, $data);
        }

        private function getUser()
        {
            $user = $this->security->getUser();
            if (is_object($user)) {
                return $user;
            }
        }
    }

.. _session-avoid-start:

Integrating with Legacy Applications
------------------------------------

If you're integrating the Symfony full-stack Framework into a legacy
application that starts the session with ``session_start()``, you may still be
able to use Symfony's session management by using the PHP Bridge session.

If the application has its own PHP save handler, you can specify ``null``
for the ``handler_id``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                storage_factory_id: session.storage.factory.php_bridge
                handler_id: ~

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:session storage-factory-id="session.storage.factory.php_bridge"
                    handler-id="null"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                ->storageFactoryId('session.storage.factory.php_bridge')
                ->handlerId(null)
            ;
        };

    .. code-block:: php-standalone

        use Symfony\Component\HttpFoundation\Session\Session;
        use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

        // legacy application configures session
        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', '/tmp');
        session_start();

        // Get Symfony to interface with this existing session
        $session = new Session(new PhpBridgeSessionStorage());

        // symfony will now interface with the existing PHP session
        $session->start();

Otherwise, if the problem is that you cannot avoid the application
starting the session with ``session_start()``, you can still make use of
a Symfony based session save handler by specifying the save handler as in
the example below:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                storage_factory_id: session.storage.factory.php_bridge
                handler_id: session.handler.native_file

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:session storage-id="session.storage.php_bridge"
                    handler-id="session.storage.native_file"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                ->storageFactoryId('session.storage.factory.php_bridge')
                ->handlerId('session.storage.native_file')
            ;
        };

.. note::

    If the legacy application requires its own session save handler, do not
    override this. Instead set ``handler_id: ~``. Note that a save handler
    cannot be changed once the session has been started. If the application
    starts the session before Symfony is initialized, the save handler will
    have already been set. In this case, you will need ``handler_id: ~``.
    Only override the save handler if you are sure the legacy application
    can use the Symfony save handler without side effects and that the session
    has not been started before Symfony is initialized.

.. _`phpredis extension`: https://github.com/phpredis/phpredis
.. _`DoctrineMongoDBBundle configuration`: https://symfony.com/doc/master/bundles/DoctrineMongoDBBundle/config.html
.. _`MongoDB shell`: https://docs.mongodb.com/manual/mongo/
.. _`php-encryption`: https://github.com/defuse/php-encryption
