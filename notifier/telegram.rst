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

Updating Messages
-----------------

.. versionadded:: 6.2

    The ``TelegramOptions::edit()`` method was introduced in Symfony 6.2.

When working with interactive callback buttons, you can use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Telegram\\TelegramOptions` to reference
a previous message to edit::

    use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\Button\InlineKeyboardButton;
    use Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup\InlineKeyboardMarkup;
    use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Are you really sure?');
    $telegramOptions = (new TelegramOptions())
        ->chatId($chatId)
        ->edit($messageId) // extracted from callback payload or SentMessage
        ->replyMarkup((new InlineKeyboardMarkup())
            ->inlineKeyboard([
                (new InlineKeyboardButton('Absolutely'))->callbackData('yes'),
            ])
        );

Answering Callback Queries
--------------------------

.. versionadded:: 6.3

    The ``TelegramOptions::answerCallbackQuery()`` method was introduced in Symfony 6.3.

When sending message with inline keyboard buttons with callback data, you can use
:class:`Symfony\\Component\\Notifier\\Bridge\\Telegram\\TelegramOptions` to `answer callback queries`_::

    use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('Thank you!');
    $telegramOptions = (new TelegramOptions())
        ->chatId($chatId)
        ->answerCallbackQuery(
            callbackQueryId: '12345', // extracted from callback
            showAlert: true,
            cacheTime: 1,
        );

.. _`message options`: https://core.telegram.org/bots/api
