.. index::
   single: Sessions

Bridge a legacy application with Symfony Sessions
=================================================

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

.. note::

    If the legacy application requires its own session save-handler, do not
    override this. Instead set ``handler_id: ~``. Note that a save handler
    cannot be changed once the session has been started. If the application
    starts the session before Symfony is initialized, the save-handler will
    have already been  set. In this case, you will need ``handler_id: ~``.
    Only override the save-handler if you are sure the legacy application
    can use the Symfony save-handler without side effects and that the session
    has not been started before Symfony is initialized.

For more details, see :doc:`/components/http_foundation/session_php_bridge`.
