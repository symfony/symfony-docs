.. index::
    single: Notifier; Chatters

How to send Chat Messages
=========================

.. versionadded:: 5.0

    The Notifier component was introduced in Symfony 5.0 as an
    :doc:`experimental feature </contributing/code/experimental>`.

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

            $chatter->send($message);

            // ...
        }
    }

.. seealso::

    Read :ref:`the main Notifier guide <notifier-chatter-dsn>` to see how
    to configure the different transports.

Adding Interactions to a Slack Message
--------------------------------------

With a Slack message, you can use the 
:class:`Symfony\\Component\\Notifier\\Bridge\\Slack\\SlackOptions` to add
some interactive options called `Block elements`_::

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
                    'https://example.com/symfony-logo.png',
                    'Symfony'
                )
            )
        )
        ->block(new SlackDividerBlock())
        ->block($contributeToSymfonyBlocks);

    // Add the custom options to the chat message and send the message
    $chatMessage->options($slackOptions);

    $chatter->send($chatMessage);

.. _`Block elements`: https://api.slack.com/reference/block-kit/block-elements
