.. index::
   single: Tests; HTTP authentication

How to Simulate HTTP Authentication in a Functional Test
========================================================

Authenticating requests in functional tests can slow down the entire test suite.
This could become an issue especially when the tests reproduce the same steps
that users follow to authenticate, such as submitting a login form or using
OAuth authentication services.

The trick to make tests faster is to bypass the authentication process, create
the *authentication token* yourself and store it in the session.

This technique requires some knowledge of the Security component internals,
but the following example shows a complete example that you can adapt to your
needs::

    // src/AppBundle/Tests/Controller/DefaultControllerTest.php
    namespace AppBundle\Tests\Controller;

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
            $session = $this->client->getContainer()->get('session');

            // the firewall context defaults to the firewall name
            $firewallContext = 'secured_area';

            $token = new UsernamePasswordToken('admin', null, $firewallContext, array('ROLE_ADMIN'));
            $session->set('_security_'.$firewallContext, serialize($token));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $this->client->getCookieJar()->set($cookie);
        }
    }
