.. index::
   single: Emails; Testing

How to functionally test an Email is sent
=========================================

Sending e-mails with Symfony2 is pretty straight forward thanks to the
``SwiftmailerBundle``, which leverages the power of the `Swiftmailer`_ library.

To functionally test that e-mails are sent, and even assert their subjects,
content or any other headers we can use :ref:`the Symfony2 Profiler <internals-profiler>`.

Let's start with an easy controller action that sends an e-mail::

    public function indexAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody('You should see me from the profiler!')
        ;

        $this->get('mailer')->send($message);

        return $this->render(...);
    }

.. note::

    Don't forget to enable profiler as explained in :doc:`/cookbook/testing/profiling`.

And the ``WebTestCase`` to assert the e-mail content should be similar to::

    // src/Acme/DemoBundle/Tests/Controller/MailControllerTest.php
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class MailControllerTest extends WebTestCase
    {
        public function testMailIsSentAndContentIsOk()
        {
            $client = static::createClient();
            $crawler = $client->request('GET', 'your_action_route_here');

            $mailCollector = $client->getProfile()->getCollector('swiftmailer');

            // Check that an e-mail was sent
            $this->assertEquals(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting e-mail data
            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertEquals('Hello Email', $message->getSubject());
            $this->assertEquals('send@example.com', key($message->getFrom()));
            $this->assertEquals('recipient@example.com', key($message->getTo()));
            $this->assertEquals('You should see me from the profiler!', $message->getBody());
        }
    }

