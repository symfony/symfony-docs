.. index::
   single: Sessions, Session Proxy, Proxy

Session Proxy Examples
======================

The session proxy mechanism has a variety of uses and this article demonstrates
two common uses. Rather than using the regular session handler, you can create
a custom save handler by defining a class that extends the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Proxy\\SessionHandlerProxy`
class.

Then, define the class as a :ref:`service <service-container-creating-service>`.
If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
that happens automatically.

Finally, use the ``framework.session.handler_id`` configuration option to tell
Symfony to use your session handler instead of the default one:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            session:
                # ...
                handler_id: App\Session\CustomSessionHandler

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:session handler-id="App\Session\CustomSessionHandler"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use App\Session\CustomSessionHandler;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->session()
                ->handlerId(CustomSessionHandler::class)
            ;
        };

Keep reading the next sections to learn how to use the session handlers in practice
to solve two common use cases: encrypt session information and define read-only
guest sessions.

Encryption of Session Data
--------------------------

If you want to encrypt the session data, you can use the proxy to encrypt and
decrypt the session as required. The following example uses the `php-encryption`_
library, but you can adapt it to any other library that you may be using::

    // src/Session/EncryptedSessionProxy.php
    namespace App\Session;

    use Defuse\Crypto\Crypto;
    use Defuse\Crypto\Key;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class EncryptedSessionProxy extends SessionHandlerProxy
    {
        private $key;

        public function __construct(\SessionHandlerInterface $handler, Key $key)
        {
            $this->key = $key;

            parent::__construct($handler);
        }

        public function read($id)
        {
            $data = parent::read($id);

            return Crypto::decrypt($data, $this->key);
        }

        public function write($id, $data)
        {
            $data = Crypto::encrypt($data, $this->key);

            return parent::write($id, $data);
        }
    }

Read-only Guest Sessions
------------------------

There are some applications where a session is required for guest users, but
where there is no particular need to persist the session. In this case you
can intercept the session before it is written::

    // src/Session/ReadOnlySessionProxy.php
    namespace App\Session;

    use App\Entity\User;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;
    use Symfony\Component\Security\Core\Security;

    class ReadOnlySessionProxy extends SessionHandlerProxy
    {
        private $security;

        public function __construct(\SessionHandlerInterface $handler, Security $security)
        {
            $this->security = $security;

            parent::__construct($handler);
        }

        public function write($id, $data)
        {
            if ($this->getUser() && $this->getUser()->isGuest()) {
                return;
            }

            return parent::write($id, $data);
        }

        private function getUser()
        {
            $user = $this->security->getUser();
            if (is_object($user)) {
                return $user;
            }
        }
    }

.. _`php-encryption`: https://github.com/defuse/php-encryption
