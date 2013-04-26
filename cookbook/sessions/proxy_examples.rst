.. index::
   single: Sessions, session proxy, proxy

Session Proxy Examples
----------------------

The session proxy mechanism has a variety of uses, in this
example we'll demonstrate two common uses. Rather than injecting
the session handler as normal, you inject the handler into the proxy
and regsiter the proxy with the session::

    <?php
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionStorage;

    $proxy = new YourProxy(new PdoSessionStorage());
    $session = new Session(new NativeSessionStorage($proxy));

The example below show should give some ideas for ``YourProxy``.


Encryption of Session Data
--------------------------

If you wanted to encrypt the session data you could use the proxy to encrypt
and decrypt the session as required::

    <?php
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class NoGuestUserSessionProxy extends SessionHandlerProxy
    {
        private $key;

        public function __construct(\SessionHandlerInterface $handler, $key)
        {
            $this->key = $key;

            parent::__construct($handler);
        }

        public function read($id)
        {
            $data = parent::write($id, $data);

            return mcrypt_decrypt(\MCRYPT_3DES, $this->key, $data);
        }

        public function write($id, $data)
        {
            $data = mcrypt_encrypt(\MCRYPT_3DES, $this->key, $data);

            return parent::write($id, $data);
        }
    }


Readonly Guest sessions
-----------------------

There are some applications where a session is required for guest
users, but there is no particular need persist the session. In this
case you can intercept the session before it writes::

    <?php
    use Foo\User;
    use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

    class ReadOnlyGuestSessionProxy extends SessionHandlerProxy
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

