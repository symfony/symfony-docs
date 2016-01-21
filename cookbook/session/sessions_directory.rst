.. index::
   single: Sessions, sessions directory

Configuring the Directory where Session Files are Saved
=======================================================

By default, the Symfony Standard Edition uses the global ``php.ini`` values
for ``session.save_handler`` and ``session.save_path`` to determine where
to store session data. This is because of the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # handler_id set to null will use default session handler from php.ini
                handler_id: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <!-- handler-id set to null will use default session handler from php.ini -->
                <framework:session handler-id="null" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                // handler_id set to null will use default session handler from php.ini
                'handler_id' => null,
            ),
        ));

With this configuration, changing *where* your session metadata is stored
is entirely up to your ``php.ini`` configuration.

However, if you have the following configuration, Symfony will store the session
data in files in the cache directory ``%kernel.cache_dir%/sessions``. This
means that when you clear the cache, any current sessions will also be deleted:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:session />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(),
        ));

Using a different directory to save session data is one method to ensure
that your current sessions aren't lost when you clear Symfony's cache.

.. tip::

    Using a different session save handler is an excellent (yet more complex)
    method of session management available within Symfony. See
    :doc:`/components/http_foundation/session_configuration` for a
    discussion of session save handlers. There are also entries in the cookbook
    about storing sessions in a :doc:`relational database </cookbook/doctrine/pdo_session_storage>`
    or a :doc:`NoSQL database </cookbook/doctrine/mongodb_session_storage>`.

To change the directory in which Symfony saves session data, you only need
change the framework configuration. In this example, you will change the
session directory to ``app/sessions``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                handler_id: session.handler.native_file
                save_path: '%kernel.root_dir%/sessions'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:session handler-id="session.handler.native_file"
                    save-path="%kernel.root_dir%/sessions"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'handler_id' => 'session.handler.native_file',
                'save_path'  => '%kernel.root_dir%/sessions',
            ),
        ));

