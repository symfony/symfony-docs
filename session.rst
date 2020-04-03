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
        $container->loadFromExtension('framework', [
            'session' => [
                // enables the support of sessions in the app
                'enabled' => true,
                // ID of the service used for session storage
                // NULL means that Symfony uses PHP default session mechanism
                'handler_id' => null,
                // improves the security of the cookies used for sessions
                'cookie_secure' => 'auto',
                'cookie_samesite' => 'lax',
            ],
        ]);

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
        $container->loadFromExtension('framework', [
            'session' => [
                // ...
                'handler_id' => 'session.handler.native_file',
                'save_path' => '%kernel.project_dir%/var/sessions/%kernel.environment%',
            ],
        ]);

Check out the Symfony config reference to learn more about the other available
:ref:`Session configuration options <config-framework-session>`. Also, if you
prefer to store session metadata in a database instead of the filesystem,
check out this article: :doc:`/doctrine/pdo_session_storage`.

Basic Usage
-----------

Symfony provides a session service that is injected in your services and
controllers if you type-hint an argument with
:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface`::

    use Symfony\Component\HttpFoundation\Session\SessionInterface;

    class SomeService
    {
        private $session;

        public function __construct(SessionInterface $session)
        {
            $this->session = $session;
        }

        public function someMethod()
        {
            // stores an attribute in the session for later reuse
            $this->session->set('attribute-name', 'attribute-value');

            // gets an attribute by name
            $foo = $this->session->get('foo');

            // the second argument is the value returned when the attribute doesn't exist
            $filters = $this->session->get('filters', []);

            // ...
        }
    }

.. tip::

    Every ``SessionInterface`` implementation is supported. If you have your
    own implementation, type-hint this in the argument instead.

Stored attributes remain in the session for the remainder of that user's session.
By default, session attributes are key-value pairs managed with the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag`
class.

If your application needs are complex, you may prefer to use
:ref:`namespaced session attributes <namespaced-attributes>` which are managed with the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Attribute\\NamespacedAttributeBag`
class. Before using them, override the ``session`` service definition to replace
the default ``AttributeBag`` by the ``NamespacedAttributeBag``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        session:
            public: true
            class: Symfony\Component\HttpFoundation\Session\Session
            arguments: ['@session.storage', '@session.namespacedattributebag']

        session.namespacedattributebag:
            class: Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag

.. _session-avoid-start:

Avoid Starting Sessions for Anonymous Users
-------------------------------------------

Sessions are automatically started whenever you read, write or even check for
the existence of data in the session. This may hurt your application performance
because all users will receive a session cookie. In order to prevent that, you
must *completely* avoid accessing the session.

For example, if your templates include some code to display the
:ref:`flash messages <flash-messages>`, sessions will start even if the user
is not logged in and even if you haven't created any flash messages. To avoid
this behavior, add a check before trying to access the flash messages:

.. code-block:: html+twig

    {# this check prevents starting a session when there are no flash messages #}
    {% if app.request.hasPreviousSession %}
        {% for message in app.flashes('notice') %}
            <div class="flash-notice">
                {{ message }}
            </div>
        {% endfor %}
    {% endif %}

More about Sessions
-------------------

.. toctree::
    :maxdepth: 1

    /doctrine/pdo_session_storage
    session/locale_sticky_session
    session/php_bridge
    session/proxy_examples

.. _`HttpFoundation component`: https://symfony.com/components/HttpFoundation
