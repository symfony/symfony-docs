.. index::
   single: Tests; HTTP authentication

How to Simulate HTTP Authentication in a Functional Test
========================================================

Authenticating requests in functional tests can slow down the entire test suite.
This could become an issue especially when the tests reproduce the same steps
that users follow to authenticate, such as submitting a login form or using
OAuth authentication services.

This article explains the two most popular techniques to avoid these issues and
create fast tests when using authentication.

Using a Faster Authentication Mechanism Only for Tests
------------------------------------------------------

When your application is using a ``form_login`` authentication, you can make
your tests faster by allowing them to use HTTP authentication. This way your
tests authenticate with the simple and fast HTTP Basic method whilst your real
users still log in via the normal login form.

The trick is to use the ``http_basic`` authentication in your application
firewall, but only in the configuration file used by tests:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/security.yaml
        security:
            firewalls:
                # replace 'main' by the name of your own firewall
                main:
                    http_basic: ~

    .. code-block:: xml

        <!-- config/packages/test/security.xml -->
        <security:config>
            <!-- replace 'main' by the name of your own firewall -->
            <security:firewall name="main">
                <security:http-basic/>
            </security:firewall>
        </security:config>

    .. code-block:: php

        // config/packages/test/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                // replace 'main' by the name of your own firewall
                'main' => [
                    'http_basic' => [],
                ],
            ],
        ]);

Tests can now authenticate via HTTP passing the username and password as server
variables using the second argument of ``createClient()``::

    $client = static::createClient([], [
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ]);

The username and password can also be passed on a per request basis::

    $client->request('DELETE', '/post/12', [], [], [
        'PHP_AUTH_USER' => 'username',
        'PHP_AUTH_PW'   => 'pa$$word',
    ]);

Creating the Authentication Token
---------------------------------

If your application uses a more advanced authentication mechanism, you can't
use the previous trick, but it's still possible to make tests faster. The trick
now is to bypass the authentication process, create the *authentication token*
yourself and store it in the session.

This technique requires some knowledge of the Security component internals,
but the following example shows a complete example that you can adapt to your
needs::

    // tests/Controller/DefaultControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\BrowserKit\Cookie;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

    class DefaultControllerTest extends WebTestCase
    {
        private $client = null;

        public function setUp()
        {
            $this->client = static::createClient();
        }

        public function testSecuredHello()
        {
            $this->logIn();
            $crawler = $this->client->request('GET', '/admin');

            $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $this->assertSame('Admin Dashboard', $crawler->filter('h1')->text());
        }

        private function logIn()
        {
            $session = self::$container->get('session');

            // somehow fetch the user (e.g. using the user repository)
            $user = ...;

            $firewallName = 'secure_area';
            // if you don't define multiple connected firewalls, the context defaults to the firewall name
            // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
            $firewallContext = 'secured_area';

            // you may need to use a different token class depending on your application.
            // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
            $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
            $session->set('_security_'.$firewallContext, serialize($token));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $this->client->getCookieJar()->set($cookie);
        }
    }
