.. index::
   single: Sessions, sessions directory

Configuring the Directory where Session Files are Saved
=======================================================

By default, Symfony stores session metadata on the filesystem. If you want to control
this path, update the ``framework.session.save_path`` configuration key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                handler_id: session.handler.native_file
                save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session handler-id="session.handler.native_file" save-path="%kernel.project_dir%/var/sessions/%kernel.environment%" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'handler_id' => 'session.handler.native_file',
                'save_path' => '%kernel.project_dir%/var/sessions/%kernel.environment%'
            ),
        ));

Storing Sessions Elsewhere (e.g. database)
------------------------------------------

Of course, you can store your session data anywhere by using the ``handler_id`` option.
See :doc:`/components/http_foundation/session_configuration` for a discussion of
session save handlers. There are also articles about storing sessions in a
:doc:`relational database </doctrine/pdo_session_storage>`
or a :doc:`NoSQL database </doctrine/mongodb_session_storage>`.
