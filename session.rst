Sessions
========

Symfony provides a session object and several utilities that you can use to
store information about the user between requests.

Configuration
-------------

Sessions are provided by the `HttpFoundation component`_, which is included in
all Symfony applications, no matter how you installed it. Before using the
sessions, check their default configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # enables the support of sessions in the app
                enabled: true
                # ID of the service used for session storage.
                # NULL means that Symfony uses PHP default session mechanism
                handler_id: null
                # improves the security of the cookies used for sessions
                cookie_secure: 'auto'
                cookie_samesite: 'lax'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!--
                    enabled: enables the support of sessions in the app
                    handler-id: ID of the service used for session storage
                                NULL means that Symfony uses PHP default session mechanism
                    cookie-secure and cookie-samesite: improves the security of the cookies used for sessions
                -->
                <framework:session enabled="true"
                                   handler-id="null"
                                   cookie-secure="auto"
                                   cookie-samesite="lax"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                // enables the support of sessions in the app
                ->enabled(true)
                // ID of the service used for session storage
                // NULL means that Symfony uses PHP default session mechanism
                ->handlerId(null)
                // improves the security of the cookies used for sessions
                ->cookieSecure('auto')
                ->cookieSamesite('lax')
            ;
        };

Setting the ``handler_id`` config option to ``null`` means that Symfony will
use the native PHP session mechanism. The session metadata files will be stored
outside of the Symfony application, in a directory controlled by PHP. Although
this usually simplify things, some session expiration related options may not
work as expected if other applications that write to the same directory have
short max lifetime settings.

If you prefer, you can use the ``session.handler.native_file`` service as
``handler_id`` to let Symfony manage the sessions itself. Another useful option
is ``save_path``, which defines the directory where Symfony will store the
session metadata files:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: 'session.handler.native_file'
                save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:session enabled="true"
                                   handler-id="session.handler.native_file"
                                   save-path="%kernel.project_dir%/var/sessions/%kernel.environment%"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->session()
                // ...
                ->handlerId('session.handler.native_file')
                ->savePath('%kernel.project_dir%/var/sessions/%kernel.environment%')
            ;
        };

Check out the Symfony config reference to learn more about the other available
:ref:`Session configuration options <config-framework-session>`. You can also
:doc:`store sessions in a database </session/database>`.

Basic Usage
-----------

The sessions is available througth the Request and the RequestStack.
Symfony provides a request_stack service that is injected in your services and
controllers if you type-hint an argument with
:class:`Symfony\\Component\\HttpFoundation\\RequestStack`::

    use Symfony\Component\HttpFoundation\RequestStack;

    class SomeService
    {
        private $requestStack;

        public function __construct(RequestStack $requestStack)
        {
            $this->requestStack = $requestStack;
        }

        public function someMethod()
        {
            // stores an attribute in the session for later reuse
            $this->requestStack->getSession()->set('attribute-name', 'attribute-value');

            // gets an attribute by name
            $foo = $this->session->get('foo');

            // the second argument is the value returned when the attribute doesn't exist
            $filters = $this->session->get('filters', []);

            // ...
        }
    }

.. deprecated:: 5.3

    The ``SessionInterface`` and ``session`` service were deprecated in
    Symfony 5.3. Instead, inject the ``RequestStack`` service to get the session
    object of the current request.

Stored attributes remain in the session for the remainder of that user's session.
By default, session attributes are key-value pairs managed with the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag`
class.

.. deprecated:: 5.3

    The ``NamespacedAttributeBag`` class is deprecated since Symfony 5.3.
    If you need this feature, you will have to implement the class yourself.

If your application needs are complex, you may prefer to use
:ref:`namespaced session attributes <namespaced-attributes>` which are managed with the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\NamespacedAttributeBag`
class. Before using them, override the ``session_listener`` service definition to build
your ``Session`` object with the default ``AttributeBag`` by the ``NamespacedAttributeBag``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        session.factory:
            autoconfigure: true
            class: App\Session\SessionFactory
            arguments:
            - '@request_stack'
            - '@session.storage.factory'
            - ['@session_listener', 'onSessionUsage']
            - '@session.namespacedattributebag'

        session.namespacedattributebag:
            class: Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag

.. code-block:: php

    namespace App\Session;

    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;

    class SessionFactory
    {
        private $requestStack;
        private $storageFactory;
        private $usageReporter;
        private $sessionAttributes;

        public function __construct(RequestStack $requestStack, SessionStorageFactoryInterface $storageFactory, callable $usageReporter, NamespacedAttributeBag $sessionAttributes)
        {
            $this->requestStack = $requestStack;
            $this->storageFactory = $storageFactory;
            $this->usageReporter = $usageReporter;
            $this->sessionAttributes = $sessionAttributes;
        }

        public function createSession(): SessionInterface
        {
            return new Session(
                $this->storageFactory->createStorage($this->requestStack->getMainRequest()),
                $this->sessionAttributes,
                null,
                $this->usageReporter
            );
        }
    }

.. _session-avoid-start:

Avoid Starting Sessions for Anonymous Users
-------------------------------------------

Sessions are automatically started whenever you read, write or even check for
the existence of data in the session. This may hurt your application performance
because all users will receive a session cookie. In order to prevent that, you
must *completely* avoid accessing the session.

More about Sessions
-------------------

.. toctree::
    :maxdepth: 1

    session/database
    session/locale_sticky_session
    session/php_bridge
    session/proxy_examples

.. _`HttpFoundation component`: https://symfony.com/components/HttpFoundation
