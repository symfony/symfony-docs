.. index::
    single: Notifier; Chatters

How to send Chat Messages
=========================

.. versionadded:: 5.0

    The Notifier component was introduced in Symfony 5.0.

The :class:`Symfony\\Component\\Notifier\\ChatterInterface` class allows
you to send messages to chat services like Slack or Telegram::

    // src/Controller/CheckoutController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Notifier\ChatterInterface;
    use Symfony\Component\Notifier\Message\ChatMessage;
    use Symfony\Component\Routing\Annotation\Route;

    class CheckoutController extends AbstractController
    {
        /**
         * @Route("/checkout/thankyou")
         */
        public function thankyou(ChatterInterface $chatter)
        {
            $message = (new ChatMessage('You got a new invoice for 15 EUR.'))
                // if not set explicitly, the message is send to the
                // default transport (the first one configured)
                ->transport('slack');

            $sentMessage = $chatter->send($message);

            // ...
        }
    }

The ``send()`` method returns a variable of type
:class:`Symfony\\Component\\Notifier\\Message\\SentMessage` which provides
information such as the message ID and the original message contents.

.. versionadded:: 5.2

    The ``SentMessage`` class was introduced in Symfony 5.2.

.. seealso::

    Read :ref:`the main Notifier guide <notifier-chatter-dsn>` to see how
    to configure the different transports.

Adding Interactions to a Slack Message
--------------------------------------

With a Slack message, you can use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Slack\\SlackOptions` class
to add some interactive options called `Block elements`_::

    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackActionsBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
    use Symfony\Component\Notifier\Bridge\Slack\Block\SlackImageBlock;
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

Adding Fields and Values to a Slack Message
-------------------------------------------

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

Adding Interactions to a Discord Message
----------------------------------------

With a Discord message, you can use the
:class:`Symfony\\Component\\Notifier\\Bridge\\Discord\\DiscordOptions` class
to add some interactive options called `Embed elements`_::

    use Symfony\Component\Notifier\Bridge\Discord\DiscordOptions;
    use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordEmbed;
    use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFieldEmbedObject;
    use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFooterEmbedObject;
    use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordMediaEmbedObject;
    use Symfony\Component\Notifier\Message\ChatMessage;

    $chatMessage = new ChatMessage('');

    // Create Discord Embed
    $discordOptions = (new DiscordOptions())
        ->username('connor bot')
        ->addEmbed((new DiscordEmbed())
            ->color(2021216)
            ->title('New song added!')
            ->thumbnail((new DiscordMediaEmbedObject())
            ->url('https://i.scdn.co/image/ab67616d0000b2735eb27502aa5cb1b4c9db426b'))
            ->addField((new DiscordFieldEmbedObject())
                ->name('Track')
                ->value('[Common Ground](https://open.spotify.com/track/36TYfGWUhIRlVjM8TxGUK6)')
                ->inline(true)
            )
            ->addField((new DiscordFieldEmbedObject())
                ->name('Artist')
                ->value('Alasdair Fraser')
                ->inline(true)
            )
            ->addField((new DiscordFieldEmbedObject())
                ->name('Album')
                ->value('Dawn Dance')
                ->inline(true)
            )
            ->footer((new DiscordFooterEmbedObject())
                ->text('Added ...')
                ->iconUrl('https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Spotify_logo_without_text.svg/200px-Spotify_logo_without_text.svg.png')
            )
        )
    ;

    // Add the custom options to the chat message and send the message
    $chatMessage->options($discordOptions);

    $chatter->send($chatMessage);

Adding Interactions to a Telegram Message
-----------------------------------------

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

.. _`Block elements`: https://api.slack.com/reference/block-kit/block-elements
.. _`Embed elements`: https://discord.com/developers/docs/resources/webhook
.. _`message options`: https://core.telegram.org/bots/api
