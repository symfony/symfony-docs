.. index::
   single: Notifier
   single: Notifications
   single: Components; Notifier

The Notifier Component
======================

    The Notifier component sends notifications via one or more channels
    (email, SMS, Slack, ...).

Installation
------------

.. code-block:: terminal

    $ composer require symfony/notifier

.. include:: /components/require_autoload.rst.inc


Usage
-----

Transport
---------

Every Notification, Text or Chat Message needs a Transport in order to be delivered.

There are implement Transports for Slack, Twilio, Nexmo and Telegram.

You can create a Transport by implementing the TransportInterface.

For example you can send a Chat Message to a Slack Transport::

    $slackTransport = new SlackTransport(
        'xxxx-xxxxxxxxx-xxxx',
        'C1234567890',
        new CurlHttpClient()
    );
    $chatMessage = (new ChatMessage('Contribute to Symfony! It\'s fun and a great experience.'));
    $slackTransport->send($chatMessage);

Notifier
-------

Chatter
-------


Texter
------


Channel
-------


Message
-------

Recipient
---------



