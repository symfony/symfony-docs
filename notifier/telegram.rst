.. index::
    single: Notifier; Chatters

Telegram Notifier
=================

The Telegram Notifier package allows to use Telegram via the Symfony Notifier
component. Read the :doc:`main Notifier docs </notifier>` to learn about installing
and configuring that component.

Adding Interactions to a Message
--------------------------------

With a Telegram message, you can use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Telegram\\TelegramOptions` class
to add `message options`_::

    use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
    use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
    use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('');

    // Create Telegram options
    $telegramOptions = (new TelegramOptions())
        ->chatId('@symfonynotifierdev')
        ->parseMode('MarkdownV2')
        ->disableWebPagePreview(true)
        ->disableNotification(true)
        ->replyMarkup((new InlineKeyboardMarkup())
            ->inlineKeyboard([
                (new InlineKeyboardButton('Visit symfony.com'))
                    ->url('https://symfony.com/'),
            ])
        );

    // Add the custom options to the chat message and send the message
    $chatMessage->options($telegramOptions);

    $chatter->send($chatMessage);

.. _`message options`: https://core.telegram.org/bots/api
