<?php

namespace Application\HelloBundle\Tests\Controller;

use Symfony\Framework\WebBundle\Test\WebTestCase;

class HelloControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $client->request('GET', '/hello/Fabien');
        $client->assertResponseSelectExists('html:contains("Hello Fabien")');
    }
}
