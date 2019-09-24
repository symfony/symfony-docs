.. index::
    single: Translation; Create Custom Message formatter

Create Custom Message Formatter
===============================

The default Message Formatter provide a simple and easy way that deals with the most common use-cases
such as message placeholders and pluralization. But in some cases, you may want to use a custom message formatter
that fit to your specific needs, for example, handle nested conditions of pluralization or select sub-messages
via a fixed set of keywords (e.g. gender).

Suppose in your application you want to displays different text depending on arbitrary conditions,
for example upon whether the guest is male or female. To do this, we will use the `ICU Message Format`_
which the most suitable ones you first need to create a `IntlMessageFormatter` and pass it to the `Translator`.

.. _components-translation-message-formatter:

Creating a Custom Message Formatter
-----------------------------------

To define a custom message formatter that is able to read these kinds of rules, you must create a
new class that implements the
:class:`Symfony\\Component\\Translation\\Formatter\\MessageFormatterInterface`::

    use Symfony\Component\Translation\Formatter\MessageFormatterInterface;

    class IntlMessageFormatter implements MessageFormatterInterface
    {
        public function format($message, $locale, array $parameters = array())
        {
            $formatter = new \MessageFormatter($locale, $message);
            if (null === $formatter) {
                throw new \InvalidArgumentException(sprintf('Invalid message format. Reason: %s (error #%d)', intl_get_error_message(), intl_get_error_code()));
            }

            $message = $formatter->format($parameters);
            if ($formatter->getErrorCode() !== U_ZERO_ERROR) {
                throw new \InvalidArgumentException(sprintf('Unable to format message. Reason: %s (error #%s)', $formatter->getErrorMessage(), $formatter->getErrorCode()));
            }

            return $message;
        }
    }

Once created, simply pass it as the second argument to the `Translator`::

    use Symfony\Component\Translation\Translator;

    $translator = new Translator('fr_FR', new IntlMessageFormatter());

    var_dump($translator->trans('The guest is {gender, select, m {male} f {female}}', [ 'gender' => 'm' ]));

It will print *"The guest is male"*.

.. _`ICU Message Format`: http://userguide.icu-project.org/formatparse/messages
