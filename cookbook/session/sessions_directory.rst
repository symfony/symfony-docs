.. index::
   single: Sessions, sessions directory

Configuring Sessions Directory
==============================

By default, Symfony stores the session data in the cache directory. This
means that when you clear the cache, any current sessions will also be
deleted.

Using a different directory to save session data is one method of retaining
current sessions during a clearing of the cache.

.. tip::

    Using a different session save handler is an excellent (yet more complex)
    method of session management available within Symfony. See
    :doc:`/components/http_foundation/session_configuration.rst` for a
    discussion of session save handlers.

To change the directory in which Symfony saves session data, you only need
change the framework configuration.  In this example, we are changing the
session directory to 'app/sessions':

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        framework:
            session:
                save_path: %kernel.root_dir%/sessions

    .. code-block:: xml

        <!-- app/config/config.xml -->

        <framework:config>
            <framework:session save-path="%kernel.root_dir%/sessions" />
        </framework:config>

    .. code-block:: php

        // app/config/config.php

        $container->loadFromExtension('framework', array(
            'session' => array('save-path' => "%kernel.root_dir%/sessions"),
        ));

