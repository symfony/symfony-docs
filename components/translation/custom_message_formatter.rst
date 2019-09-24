.. index::
    single: Translation; Create Custom Message formatter

Create a Custom Message Formatter
=================================

The default message formatter provided by Symfony solves the most common needs
when translating messages, such as using variables and pluralization. However,
if your needs are different, you can create your own message formatter.

Message formatters are PHP classes that implement the
:class:`Symfony\\Component\\Translation\\Formatter\\MessageFormatterInterface`::


    use Symfony\Component\Translation\Formatter\MessageFormatterInterface;

    class MyCustomMessageFormatter implements MessageFormatterInterface
    {
        public function format($message, $locale, array $parameters = [])
        {
            // ... format the message according to your needs

            return $message;
        }
    }

Now, pass an instance of this formatter as the second argument of the translator
to use it when translating messages::

    use Symfony\Component\Translation\Translator;

    $translator = new Translator('fr_FR', new IntlMessageFormatter());
    $message = $translator->trans($originalMessage, $translationParameters);

If you want to use this formatter to translate all messages in your Symfony
application, define a service for the formatter and use the
:ref:`translator.formatter <reference-framework-translator-formatter>` option
to set that service as the default formatter.
