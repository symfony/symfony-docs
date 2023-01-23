.. index::
    single: Notifier; Chatters

Slack Notifier
==============

The Slack Notifier package allows to use Slack via the Symfony Notifier
component. Read the :doc:`main Notifier docs </notifier>` to learn about installing
and configuring that component.

Adding Interactions to a Message
--------------------------------

With a Slack message, you can use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Slack\\SlackOptions` class
to add some interactive options called `Block elements`_::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackActionsBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackImageBlockElement;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Contribute To Symfony');

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

    $slackOptions = (new SlackOptions())
        ->block((new SlackSectionBlock())
            ->text('The Symfony Community')
            ->accessory(
                new SlackImageBlockElement(
                    'https://symfony.com/favicons/apple-touch-icon.png',
                    'Symfony'
                )
            )
        )
        ->block(new SlackDividerBlock())
        ->block($contributeToSymfonyBlocks);

    // Add the custom options to the chat message and send the message
    $chatMessage->options($slackOptions);

    $chatter->send($chatMessage);

Adding Fields and Values to a Message
-------------------------------------

To add fields and values to your message you can use the
:method:`SlackSectionBlock::field() <Symfony\\Component\\Notifier\\Bridge\\Slack\\Block\\SlackSectionBlock::field>` method::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Symfony Feature');

    $options = (new SlackOptions())
        ->block((new SlackSectionBlock())->text('My message'))
        ->block(new SlackDividerBlock())
        ->block(
            (new SlackSectionBlock())
                ->field('*Max Rating*')
                ->field('5.0')
                ->field('*Min Rating*')
                ->field('1.0')
        );

    // Add the custom options to the chat message and send the message
    $chatMessage->options($options);

    $chatter->send($chatMessage);

The result will be something like:

.. image:: /_images/notifier/slack/field-method.png
   :align: center

.. versionadded:: 5.1

    The `field()` method was introduced in Symfony 5.1.

Adding a Header to a Message
----------------------------

To add a header to your message use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Slack\\Block\\SlackHeaderBlock` class::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackHeaderBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Symfony Feature');

    $options = (new SlackOptions())
        ->block((new SlackHeaderBlock('My Header')))
        ->block((new SlackSectionBlock())->text('My message'))
        ->block(new SlackDividerBlock())
        ->block(
            (new SlackSectionBlock())
                ->field('*Max Rating*')
                ->field('5.0')
                ->field('*Min Rating*')
                ->field('1.0')
        );

    // Add the custom options to the chat message and send the message
    $chatMessage->options($options);

    $chatter->send($chatMessage);

The result will be something like:

.. image:: /_images/notifier/slack/slack-header.png
   :align: center

.. versionadded:: 5.3

    The ``SlackHeaderBlock`` class was introduced in Symfony 5.3.

Adding a Footer to a Message
----------------------------

To add a footer to your message use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Slack\\Block\\SlackContextBlock` class::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackContextBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Symfony Feature');

    $contextBlock = (new SlackContextBlock())
        ->text('My Context')
        ->image('https://symfony.com/logos/symfony_white_03.png', 'Symfony Logo')
    ;

    $options = (new SlackOptions())
        ->block((new SlackSectionBlock())->text('My message'))
        ->block(new SlackDividerBlock())
        ->block(
            (new SlackSectionBlock())
                ->field('*Max Rating*')
                ->field('5.0')
                ->field('*Min Rating*')
                ->field('1.0')
        )
        ->block($contextBlock)
    ;

    $chatter->send($chatMessage);

The result will be something like:

.. image:: /_images/notifier/slack/slack-footer.png
   :align: center

.. versionadded:: 5.3

    The ``SlackContextBlock`` class was introduced in Symfony 5.3.

Sending a Message as a Reply
----------------------------

To send your slack message as a reply in a thread use the
:method:`SlackOptions::threadTs() <Symfony\\Component\\Notifier\\Bridge\\Slack\\SlackOptions::threadTs>` method::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
    use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Symfony Feature');

    $options = (new SlackOptions())
        ->block((new SlackSectionBlock())->text('My reply'))
        ->threadTs('1621592155.003100')
    ;

    // Add the custom options to the chat message and send the message
    $chatMessage->options($options);

    $chatter->send($chatMessage);

The result will be something like:

.. image:: /_images/notifier/slack/message-reply.png
   :align: center

.. versionadded:: 5.3

    The ``threadTs()`` method was introduced in Symfony 5.3.

.. _`Block elements`: https://api.slack.com/reference/block-kit/block-elements
