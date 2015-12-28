.. index::
    single: Security; Throttle Failed Login

How to Throttle Failed Login Attempts
=====================================

Sometimes, it's useful to throttle login attempts when you encounter multiple
recent failed logins.  This can be useful in combatting brute-force
password-guessing login attacks.  This can be done by implementing a
``security.authentication.failure`` listener to log failed login attempts, and
a security provider decorator to deny login attempts from the same IP address.

Authentication Failure Listener
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The purpose of this authentication failure listener is to persist the ip address
and any other data of the client.  The example here uses a doctrine table to
persist the data, but you could use another method (Redis, for example).

.. code-block:: php

    namespace AppBundle\Security\Authentication\Listener;

    use Doctrine\ORM\EntityRepositoryInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

    class LogFailedLoginAttempt
    {
        /**
         * The doctrine repository to which the failed login attempt will be
         * persisted.
         *
         * @var EntityRepositoryInterface
         */
        private $repository;

        /**
         * The current request contains the ip address and other $_SERVER globals
         * to persist.
         *
         * @var RequestStack
         */
        private $requestStack;

        public function __construct(RequestStack $requestStack, EntityRepositoryInterface $repository)
        {
            $this->repository = $repository;
            $this->requestStack = $requestStack;
        }

        public function onAuthenticationFailureEvent(AuthenticationFailureEvent $event)
        {
            $this->repository->logAuthenicationFailure(
                $event->getToken(),
                $this->requestStack->getCurrentRequest()
            );
        }
    }

Now you are logging all the relevant details about every failed login attempt.

Security Provider Decorator
~~~~~~~~~~~~~~~~~~~~~~~~~~~

This security provider will decorate another security provider and will reject
any authentication attempts it needs to throttle.

.. code-block:: php

    namespace AppBundle\Security\Authentication\Provider;

    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    class LoginThrottleProvider implements AuthenticationProviderInterface
    {
        /**
         * @var AuthenticationProviderInterface
         */
        private $decoratedProvider;

        /**
         * The throttle service decides whether to throttle a certain
         * ip address or not.
         */
        private $throttleService;

        public function __construct(AuthenticationProviderInterface $provider, $throttleService)
        {
            $this->decoratedProvider = $provider;
            $this->throttleService = $throttleService;
        }

        public function authenticate(TokenInterface $token)
        {
            if ($this->throttleService->isThrottled($token)) {
                throw new AuthenticationException(
                    'Too many failed authentication attempts.'
                );
            }

            return $this->decoratedProvider->authenticate($token);
        }

        public function supports(TokenInterface $token)
        {
            return $this->decoratedProvider->supports($token);
        }
    }

The implementation of the throttle service is outside the scope of this
documentation and will depend on your application's needs.  It is common
for an application to require additional means of authentication when
multiple failed logins are detected, such as the addition of a CAPTCHA
to the login page, or requiring two-factor authentication.
