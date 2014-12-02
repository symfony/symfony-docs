.. index::
    single: Security; How to control Session Concurrency

How to Control Session Concurrency
==================================

Sometimes, it's useful to restrict the number of concurrent sessions that a given
user can open using different browsers and devices. When the maximum number of
concurrent sessions is achieved, Symfony allows you to disable new sessions or to
expire the old ones. To do so, set the ``max_sessions`` limit using the ``session_concurrency``
option of your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_concurrency:
                        max_sessions: 2

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <firewall name="main">
                    <!-- ... -->
                    <session-concurrency
                        max-sessions="2"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_concurrency' => array(
                        'max_sessions' => 2,
                    ),
                ),
            ),
        ));

With this configuration, any user will be allowed to open up to 2 sessions, but
will fail to open the third one.

Maybe, you would like to close the older active session instead of disabling the
ability to open a new one. This can be achived setting the ``error_if_maximum_exceeded``
option to false in the firewall configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_concurrency:
                        max_sessions: 2
                        error_if_maximum_exceeded: false

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <firewall name="main">
                    <!-- ... -->
                    <session-concurrency
                        max-sessions="2"
                        error-if-maximum-exceeded="false"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_concurrency' => array(
                        'max_sessions' => 2,
                        'error_if_maximum_exceeded' => false,
                    ),
                ),
            ),
        ));

With these settings, when the user opens a new session, the older ones will be
marked as expired leaving only 2 active sessions. If the user makes a new
request with the expired session, will be logged out and redirected to ``/`` by
default. You can control where the user will be redirected when an expired
session is detected setting the ``expiration_url`` option in the firewall
configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                main:
                    # ...
                    session_concurrency:
                        max_sessions: 2
                        error_if_maximum_exceeded: false
                        expiration_url: /session-expired

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <firewall name="main">
                    <!-- ... -->
                    <session-concurrency
                        max-sessions="2"
                        error-if-maximum-exceeded="false"
                        expiration-url="/session-expired"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'=> array(
                    // ...
                    'session_concurrency' => array(
                        'max_sessions' => 2,
                        'error_if_maximum_exceeded' => false,
                        'expiration_url' => '/session-expired',
                    ),
                ),
            ),
        ));

If the ``max_sessions`` option is left to its default value (``0``) the maximum
number of sessions will not be checked, but it will allow you to manually expire
all sessions for a concrete user through the session registry:

.. code-block:: php

    // src/Acme/DemoBundle/Controller/DefaultController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Security\Core\User\UserInterface;

    class DefaultController extends Controller
    {
        public function expireUserSessionsAction(UserInterface $user)
        {
            /** @var $sessionRegistry \Symfony\Component\Security\Http\Session\SessionRegistry */
            $sessionRegistry = $this->get('security.authentication.session_registry');

            $sessions = $sessionRegistry->getAllSessions($user->getUsername());
            foreach ($sessions as $session) {
                $sessionRegistry->expireNow($session->getSessionId());
            }
        }
    }
