.. index::
   single: Console; Enabling logging

How to Enable Logging in Console Commands
=========================================

In Symfony versions prior to 3.3, the Console component didn't provide any
logging capabilities out of the box and you had to implement your own exception
listener for the console.

Starting from Symfony 3.3, the Console component provides automatic error and
exception logging.

When an exception occurs during the execution of a command, you'll see a message
like the following in your log file:

.. code-block:: terminal

   [2017-02-15 09:34:42] app.ERROR: Exception thrown while running command:
   "cache:clear -vvv". Message: "An error occured!" {"exception":"[object]
   (RuntimeException(code: 0): An error occured! at vendor/symfony/symfony/
   src/Symfony/Bundle/FrameworkBundle/Command/CacheClearCommand.php:61)",
   "command":"cache:clear -vvv","message":"An error occured!"} []

In addition to logging exceptions, the new subscriber also listens to the console.terminate event to add a log message whenever a command doesn't finish with the 0 exit status.



You can of course also access and use the :doc:`logger </logging>` service to
log messages.
