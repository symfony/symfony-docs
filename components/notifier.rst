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

.. caution::

	We're still working on the docs of this component. Check this page again
	in a few days.

Slack Actions Block for Slack Message
-------------------------------------

With a Slack Message you can add some interactive options called `Block elements`_::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackActionsBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;
    use Symfony\Component\Notifier\Chatter;
    use Symfony\Component\Notifier\Bridge\Slack\SlackTransport;

    // Initialize a chatter with Slack Transport
    $chatter = new Chatter(new SlackTransport('token'));

    // Create a message
    $chatMessage = (new ChatMessage())
        ->subject('Contribute To Symfony');

    // Create Slack Actions Block and add some buttons
    $contributeToSymfonyBlocks = (new SlackActionsBlock())
        ->button(
            'Improve Documentation',
            'https://symfony.com/doc/current/contributing/documentation/standards.html',
            'primary'
        )
        ->button(
            'Report bugs',
            'https://symfony.com/doc/current/contributing/code/bugs.html',
            'danger'
        );
    $contributeToSymfonyOptions = (new SlackOptions())->block($contributeToSymfonyBlocks);

    // Add the Buttons as Options to the Message
    $chatMessage->options($contributeToSymfonyOptions);

    // And then send the Message
    $chatter->send($chatMessage);


.. _`Block elements`: https://api.slack.com/reference/block-kit/block-elements
