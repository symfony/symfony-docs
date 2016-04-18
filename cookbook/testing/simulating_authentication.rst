.. index::
   single: Tests; Simulating authentication

How to Simulate Authentication with a Token in a Functional Test
================================================================

Authenticating requests in functional tests might slow down the suite.
It could become an issue especially when ``form_login`` is used, since
it requires additional requests to fill in and submit the form.

One of the solutions is to configure your firewall to use ``http_basic`` in
the test environment as explained in
:doc:`/cookbook/testing/http_authentication`.
Another way would be to create a token yourself and store it in a session.
While doing this, you have to make sure that an appropriate cookie is sent
with a request. The following example demonstrates this technique::

    // tests/AppBundle/Controller/DefaultControllerTest.php
    namespace Tests\Appbundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\BrowserKit\Cookie;
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

            $this->assertTrue($this->client->getResponse()->isSuccessful());
            $this->assertGreaterThan(0, $crawler->filter('html:contains("Admin Dashboard")')->count());
        }

        private function logIn()
        {
            $session = $this->client->getContainer()->get('session');

            $firewall = 'secured_area';
            $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
            $session->set('_security_'.$firewall, serialize($token));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $this->client->getCookieJar()->set($cookie);
        }
    }

.. note::

    The technique described in :doc:`/cookbook/testing/http_authentication`
    is cleaner and therefore the preferred way.
