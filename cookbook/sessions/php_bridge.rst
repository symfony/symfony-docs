.. index::
   single: Sessions

Bridge a legacy application with Symfony Sessions
--------------------------------------------------------

.. versionadded:: 2.3
Added ability to integrate with a legacy PHP session

You may take advantage of the PHP Bridge Session when integrating
a legacy application into the Symfony Full Stack Framework when it
may not be possible to avoid the legacy application starting the
session with ``session_start()``

If the application has sets it's own PHP save handler, you can
specify null for the ``handler_id``:

.. code-block:: yml

    framework:
        session:
            storage_id: session.storage.php_bridge
            handler_id: ~

Otherwise, if the problem is simply that you cannot avoid the application
starting the session with ``session_start()`` but you can still make use of
a Symfony based session save handler, you can specify the save handle
as in the example below:

.. code-block:: yml

    framework:
        session:
            storage_id: session.storage.php_bridge
            handler_id: session.handler.native_file

