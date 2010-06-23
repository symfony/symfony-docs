<?php

namespace Application\HelloBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class HelloControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertFalse($crawler->filter('html:contains("Hello Fabien")')->isEmpty());
    }
}
