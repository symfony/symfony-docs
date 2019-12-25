.. index::
   single: Notifications
   single: Components; Notifier

The Notifier Component
======================

    The Notifier component sends notifications via one or more channels
    (email, SMS, Slack, ...).

.. versionadded:: 5.0

    The Notifier component was introduced in Symfony 5.0 as an
    :doc:`experimental feature </contributing/code/experimental>`.

If you're using the Symfony Framework, read the
:doc:`Symfony Framework Notifier documentation </notifier>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/notifier

.. include:: /components/require_autoload.rst.inc

Email
-----

The Notifier component has notify you when something goes wrong::

    use Symfony\Bridge\Twig\Mime\NotificationEmail;
    use Symfony\Component\Mailer\Mailer;
    use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

    $transport = new EsmtpTransport('localhost');
    $mailer = new Mailer($transport);

    $email = (new NotificationEmail())
        ->form('fabien@symfony.com')
        ->to('fabien@symfony.com')
        ->exception($exception);

    $mailer->send($email);

The ``$email`` object is created via the :doc:`Mime component </components/mime>`.

And configurable email template::

    $email = (new NotificationEmail())
        ->htmlEmail('email/system.html.twig')
        ->textEmail('email/system.txt.twig');

With template::

    {% extends "@email/system.html.twig" %}
    {% block style %}
        {{ parent() }}
        .container.body_alert {
            border-top: 30px solid #ec5840;
        }
    {% endblock %}
    {% block lines %}
        This is an automated email for the MyApp application.
        {{ parent() }}
    {% endblock %}
    {% block action %}
        {{ parent() }}
        <spacer size="16"></spacer>
        <button class="secondary" href="https://myapp.com/">Go to MyApp</button>
    {% endblock %}
    {% block exception %}{% endblock %}
    {% block footer_content %}
        <p><small>&copy; MyApp</small></p>
    {% endblock %}


SMS
---------

Sending SMS Messages the easy way::

    /**
    * @Route("/checkout/thankyou")
    */
    public function thankyou(Texter $texter /* ... */) {
        $sms = new SmsMessage('+1415999888', 'Revenue has just increased by 1€ per year!');
        $texter->send($sms);

        return $this->render('checkout/thankyou.html.twig', [
            // ...
        ]);
    }

Below is the list of other popular provider with built-in support:

==================
Service
==================
Telegram
Nexmo
Slack
Twilio
==================

SMS low-level API::

    $sms = new SmsMessage('+1415999888', 'Revenue has just increased!');
    $twilio = Transport::fromDsn('twilio://SID:TOKEN@default?from=FROM');
    $twilio->send($sms);
    $nexmo = Transport::fromDsn('nexmo://KEY:SECRET@default?from=FROM');
    $nexmo->send($sms);

SMS... higher-level API::

    $texter = new Texter($twilio, $bus);
    $texter->send($sms);
    $transports = new Transports(['twilio' => $twilio, 'nexmo' => $nexmo]);
    $texter = new Texter($transports, $bus);
    $texter->send($sms);
    $sms->setTransport('nexmo');
    $texter->send($sms);
    $bus->dispatch($sms);

    $dsn = 'failover(twilio://SID:TOKEN@default?from=FROM nexmo://KEY:SECRET@default?from=FROM)';

Message
---------

Sending Messages the easy way::

    /**
     * @Route("/checkout/thankyou")
     */
    public function thankyou(Chatter $chatter /* ... */)
    {
     $message = new ChatMessage('Revenue increased by 1€ per year...');
        $chatter->send($message);
        return $this->render('checkout/thankyou.html.twig', [
            // ...
        ]);
    }

Messages low-level API::

    $message = new ChatMessage('Revenue increased by 1€ per year...');
    $slack = Transport::fromDsn('slack://TOKEN@default?channel=CHANNEL');
    $slack->send($sms);
    $telegram = Transport::fromDsn('telegram://TOKEN@default?channel=CHAT_ID');
    $telegram->send($sms);

Messages higher-level API::

    $transports = Transport::fromDsns([
        'slack' => 'slack://TOKEN@default?channel=CHANNEL',
        'telegram' => 'telegram://TOKEN@default?channel=CHAT_ID'
    ]);
    $chatter = new Chatter($transports, $bus);
    $chatter->send($message);
    $message->setTransport('telegram');
    $chatter->send($message);
    $bus->dispatch($message);

    $options = (new SlackOptions())
        ->iconEmoji('tada')
        ->iconUrl('https://symfony.com')
        ->username('SymfonyNext')
        ->channel($channel)
        ->block((new SlackSectionBlock())->text('Some Text'))
        ->block(new SlackDividerBlock())
        ->block((new SlackSectionBlock())
            ->text('Some Text in another block')
            ->accessory(new SlackImageBlockElement('http://placekitten.com/700/500', 'kitten'))
        )
    ;
    $message = new ChatMessage('Default Text', $options);

    $dsn = 'all(slack://TOKEN@default?channel=CHANNEL telegram://TOKEN@default?channel=CHAT_ID)';
