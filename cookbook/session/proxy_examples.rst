.. index::
   single: Sessions, Session Proxy, Proxy

Session Proxy Examples
======================

The session proxy mechanism has a variety of uses and this article demonstrates
two common uses. Rather than using the regular session handler, you can create
a custom save handler just by defining a class that extends the
:class:`Symfony\\Component\\HttpFoundation\\Session\\Storage\\Proxy\\SessionHandlerProxy`
class.

Then, define a new service related to the custom session handler:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.session_handler:
                class: AppBundle\Session\CustomSessionHandler

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.session_handler" class="AppBundle\Session\CustomSessionHandler" />
        </services>

    .. code-block:: php

        // app/config/config.php
        $container->register('app.session_handler', 'AppBundle\Session\CustomSessionHandler');

Finally, use the ``framework.session.handler_id`` configuration option to tell
Symfony to use your own session handler instead of the default one:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            session:
                # ...
                handler_id: app.session_handler

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config>
            <framework:session handler-id="app.session_handler" ... />
        </framework:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'session' => array(
                // ...
                'handler_id' => 'app.session_handler',
            ),
        ));

Keep reading the next sections to learn how to use the session handlers in practice
to solve two common use cases: encrypt session information and define readonly
guest sessions.

Encryption of Session Data
--------------------------

If you wanted to encrypt the session data, you could use the proxy to encrypt
and decrypt the session as required::

    // src/AppBundle/Session/EncryptedSessionProxy.php
    namespace AppBundle\Session;

    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class EncryptedSessionProxy extends SessionHandlerProxy
    {
        private $key;

        public function __construct(\SessionHandlerInterface $handler, $key)
        {
            $this->key = $key;

            parent::__construct($handler);
        }

        public function read($id)
        {
            $data = parent::read($id);

            return mcrypt_decrypt(\MCRYPT_3DES, $this->key, $data);
        }

        public function write($id, $data)
        {
            $data = mcrypt_encrypt(\MCRYPT_3DES, $this->key, $data);

            return parent::write($id, $data);
        }
    }

Readonly Guest Sessions
-----------------------

There are some applications where a session is required for guest users, but
where there is no particular need to persist the session. In this case you
can intercept the session before it is written::

    // src/AppBundle/Session/ReadOnlySessionProxy.php
    namespace AppBundle\Session;

    use AppBundle\Entity\User;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class ReadOnlySessionProxy extends SessionHandlerProxy
    {
        private $user;

        public function __construct(\SessionHandlerInterface $handler, User $user)
        {
            $this->user = $user;

            parent::__construct($handler);
        }

        public function write($id, $data)
        {
            if ($this->user->isGuest()) {
                return;
            }

            return parent::write($id, $data);
        }
    }
