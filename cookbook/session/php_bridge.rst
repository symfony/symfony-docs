.. index::
   single: Sessions

Bridge a legacy application with Symfony Sessions
-------------------------------------------------

.. versionadded:: 2.3
    The ability to integrate with a legacy PHP session was added in Symfony 2.3.

If you're integrating the Symfony full-stack Framework into a legacy application
that starts the session with ``session_start()``, you may still be able to
use Symfony's session management by using the PHP Bridge session.

If the application has sets it's own PHP save handler, you can specify null
for the ``handler_id``:

.. code-block:: yaml

    framework:
        session:
            storage_id: session.storage.php_bridge
            handler_id: ~

Otherwise, if the problem is simply that you cannot avoid the application
starting the session with ``session_start()``, you can still make use of
a Symfony based session save handler by specifying the save handler as in
the example below:

.. code-block:: yaml

    framework:
        session:
            storage_id: session.storage.php_bridge
            handler_id: session.handler.native_file

For more details, see :doc:`/components/http_foundation/session_php_bridge`.