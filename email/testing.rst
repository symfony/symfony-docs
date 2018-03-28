.. index::
   single: Emails; Testing

How to Test that an Email is Sent in a Functional Test
======================================================

Sending emails with Symfony is pretty straightforward thanks to the
SwiftmailerBundle, which leverages the power of the `Swift Mailer`_ library.

To functionally test that an email was sent, and even assert the email subject,
content or any other headers, you can use :doc:`the Symfony Profiler </profiler>`.

Start with an easy controller action that sends an email::

    public function sendEmailAction($name, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody('You should see me from the profiler!')
        ;

        $mailer->send($message);

        return $this->render(...);
    }

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

            // enables the profiler for the next request (it does nothing if the profiler is not available)
            $client->enableProfiler();

            $crawler = $client->request('POST', '/path/to/above/action');

            $mailCollector = $client->getProfile()->getCollector('swiftmailer');

            // checks that an email was sent
            $this->assertSame(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting email data
            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertSame('Hello Email', $message->getSubject());
            $this->assertSame('send@example.com', key($message->getFrom()));
            $this->assertSame('recipient@example.com', key($message->getTo()));
            $this->assertSame(
                'You should see me from the profiler!',
                $message->getBody()
            );
        }
    }

Troubleshooting
---------------

Problem: The Collector Object Is ``null``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The email collector is only available when the profiler is enabled and collects
information, as explained in :doc:`/testing/profiling`.

Problem: The Collector Doesn't Contain the E-Mail
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If a redirection is performed after sending the email (for example when you send
an email after a form is processed and before redirecting to another page), make
sure that the test client doesn't follow the redirects, as explained in
:doc:`/testing`. Otherwise, the collector will contain the information of the
redirected page and the email won't be accessible.

.. _`Swift Mailer`: http://swiftmailer.org/
