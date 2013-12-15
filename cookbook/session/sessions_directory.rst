.. index::
   single: Sessions, sessions directory

Configuring the Directory where Sessions Files are Saved
========================================================

By default, Symfony stores the session data in files in the cache
directory `"%kernel.cach_dir%/sessions"`. This means that when you clear
the cache, any current sessions will also be deleted.

.. note::

    If the ``session`` configuration key is set to ``~``, Symfony will use the
    global PHP ini values for ``session.save_handler``and associated
    ``session.save_path`` from ``php.ini``.

.. note::

    While the Symfony Full Stack Framework defaults to using the
    ``session.handler.native_file``, the Symfony Standard Edition is
    configured to use PHP's global session settings by default and therefor
    sessions will be stored according to the `session.save_path` location
    and will not be deleted when clearing the cache.

Using a different directory to save session data is one method to ensure
that your current sessions aren't lost when you clear Symfony's cache.

.. tip::

    Using a different session save handler is an excellent (yet more complex)
    method of session management available within Symfony. See
    :doc:`/components/http_foundation/session_configuration` for a
    discussion of session save handlers. There is also an entry in the cookbook
    about storing sessions in the :doc:`database</cookbook/configuration/pdo_session_storage>`.

To change the directory in which Symfony saves session data, you only need
change the framework configuration.  In this example, you will change the
session directory to ``app/sessions``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                handler_id: session.handler.native_file
                save_path: "%kernel.root_dir%/sessions"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="session.handler.native_file" />
            <framework:session save-path="%kernel.root_dir%/sessions" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'handler-id' => "session.handler.native_file"),
                'save-path' => "%kernel.root_dir%/sessions"),
            ),
        ));
        
