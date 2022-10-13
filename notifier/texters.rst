.. index::
    single: Notifier; Texters

How to send SMS Messages
========================

The :class:`Symfony\\Component\\Notifier\\TexterInterface` class allows
you to send SMS messages::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Component\Notifier\Message\SmsMessage;
    use Symfony\Component\Notifier\TexterInterface;
    use Symfony\Component\Routing\Annotation\Route;

    class SecurityController
    {
        #[Route('/login/success')]
        public function loginSuccess(TexterInterface $texter)
        {
            $sms = new SmsMessage(
                // the phone number to send the SMS message to
                '+1411111111',
                // the message
                'A new login was detected!',
                // optionally, you can override default "from" defined in transports
                '+1422222222',
            );

            $sentMessage = $texter->send($sms);

            // ...
        }
    }

.. versionadded:: 6.2

    The 3rd argument of ``SmsMessage`` (``$from``) was introduced in Symfony 6.2.

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. seealso::

    Read :ref:`the main Notifier guide <notifier-texter-dsn>` to see how
    to configure the different transports.
