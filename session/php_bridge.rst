.. index::
   single: Sessions

Bridge a legacy Application with Symfony Sessions
=================================================

If you're integrating the Symfony full-stack Framework into a legacy application
that starts the session with ``session_start()``, you may still be able to
use Symfony's session management by using the PHP Bridge session.

If the application has its own PHP save handler, you can specify null
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

For more details, see :doc:`/components/http_foundation/session_php_bridge`.
