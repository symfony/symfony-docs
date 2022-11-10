.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Testing with Sessions
=====================

Symfony is designed from the ground up with code-testability in mind. In order
to test your code which utilizes sessions, we provide two separate mock storage
mechanisms for both unit testing and functional testing.

Testing code using real sessions is tricky because PHP's workflow state is global
and it is not possible to have multiple concurrent sessions in the same PHP
process.

The mock storage engines simulate the PHP session workflow without actually
starting one allowing you to test your code without complications. You may also
run multiple instances in the same PHP process.

The mock storage drivers do not read or write the system globals
``session_id()`` or ``session_name()``. Methods are provided to simulate this if
required:

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageInterface::getId`: Gets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageInterface::setId`: Sets the
  session ID.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageInterface::getName`: Gets the
  session name.

* :method:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\SessionStorageInterface::setName`: Sets the
  session name.

Unit Testing
------------

For unit testing where it is not necessary to persist the session, you should
swap out the default storage engine with
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockArraySessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

    $session = new Session(new MockArraySessionStorage());

Functional Testing
------------------

For functional testing where you may need to persist session data across
separate PHP processes, change the storage engine to
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockFileSessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

    $session = new Session(new MockFileSessionStorage());

Using Symonfy Framework
-----------------------

If you are using Symfony framework and persisting sessions with
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`
the following exception will be thrown when running a test where a user logs in:

    RuntimeException: Failed to start the session because headers have already been sent by "vendor/phpunit/phpunit/src/Util/Printer.php" at line 104.

In :class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage`'s
``start()`` function, there is a check to see if headers have already been sent,
which in a normal browser request they would not have been.

However, PHPUnit sends output to the console when the test run is starts, and PHP understands
this as sending headers, and so the ``RuntimeException`` is thrown.

In order to tell Symfony to use
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockFileSessionStorage`
in tests, the following changes are needed::

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        when@test:
            framework:
                test: true
                session:
                    # Don't use normal PHP sessions for tests, otherwise PHP freaks out when creating a session with a
                    # "Headers already sent" error, because PHPUnit sent a response to the console.
                    storage_factory_id: "mock_file_session_storage"

        # config/packages/services.yaml
        when@test:
            services:
                mock_file_session_storage:
                    class: Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory
