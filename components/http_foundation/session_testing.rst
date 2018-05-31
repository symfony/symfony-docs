.. index::
   single: HTTP
   single: HttpFoundation, Sessions

Testing with Sessions
=====================

Symfony is designed from the ground up with code-testability in mind. In order
to make your code which utilizes session easily testable we provide two separate
mock storage mechanisms for both unit testing and functional testing.

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
simply swap out the default storage engine with
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockArraySessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
    use Symfony\Component\HttpFoundation\Session\Session;

    $session = new Session(new MockArraySessionStorage());

Functional Testing
------------------

For functional testing where you may need to persist session data across
separate PHP processes, simply change the storage engine to
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockFileSessionStorage`::

    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

    $session = new Session(new MockFileSessionStorage());

Disabling the Mock Session
--------------------------

When using the HttpFoundation component in a Symfony application, you can
disable the mock session and use the normal PHP session defined in ``php.ini``
commenting or removing these lines from the testing configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        framework:
            session:
                storage_id: session.storage.mock_file

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:session storage-id="session.storage.mock_file" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config_test.php
        $container->loadFromExtension('framework', array(
            'session' => array(
                'storage_id' => 'session.storage.mock_file',
            ),
        ));

