.. index::
   single: Emails; Testing

How to Test that an Email is Sent in a Functional Test
======================================================

Sending emails with Symfony is pretty straightforward thanks to the
SwiftmailerBundle, which leverages the power of the `Swift Mailer`_ library.

To functionally test that an email was sent, and even assert the email subject,
content or any other headers, you can use :doc:`the Symfony Profiler </cookbook/profiler/index>`.

Start with an easy controller action that sends an email::

    public function sendEmailAction($name)
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

    Don't forget to enable the profiler as explained in :doc:`/cookbook/testing/profiling`.

In your functional test, use the ``swiftmailer`` collector on the profiler
to get information about the messages sent on the previous request::

    // tests/AppBundle/Controller/MailControllerTest.php
    namespace Tests\AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class MailControllerTest extends WebTestCase
    {
        public function testMailIsSentAndContentIsOk()
        {
            $client = static::createClient();

            // Enable the profiler for the next request (it does nothing if the profiler is not available)
            $client->enableProfiler();

            $crawler = $client->request('POST', '/path/to/above/action');

            $mailCollector = $client->getProfile()->getCollector('swiftmailer');

            // Check that an email was sent
            $this->assertEquals(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting email data
            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertEquals('Hello Email', $message->getSubject());
            $this->assertEquals('send@example.com', key($message->getFrom()));
            $this->assertEquals('recipient@example.com', key($message->getTo()));
            $this->assertEquals(
                'You should see me from the profiler!',
                $message->getBody()
            );
        }
    }

.. _`Swift Mailer`: http://swiftmailer.org/
