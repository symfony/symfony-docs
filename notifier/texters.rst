.. index::
    single: Notifier; Texters

How to send SMS Messages
========================

.. versionadded:: 5.0

    The Notifier component was introduced in Symfony 5.0.

The :class:`Symfony\\Component\\Notifier\\TexterInterface` class allows
you to send SMS messages::

    // src/Controller/SecurityController.php
    namespace App\Controller;

    use Symfony\Component\Notifier\Message\SmsMessage;
    use Symfony\Component\Notifier\TexterInterface;
    use Symfony\Component\Routing\Annotation\Route;

    class SecurityController
    {
        /**
         * @Route("/login/success")
         */
        public function loginSuccess(TexterInterface $texter)
        {
            $sms = new SmsMessage(
                // the phone number to send the SMS message to
                '+1411111111',
                // the message
                'A new login was detected!'
            );

            $sentMessage = $texter->send($sms);

            // ...
        }
    }

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. versionadded:: 5.2

    The ``SentMessage`` class was introduced in Symfony 5.2.

.. seealso::

    Read :ref:`the main Notifier guide <notifier-texter-dsn>` to see how
    to configure the different transports.
