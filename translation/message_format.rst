.. index::
    single: Translation; Message Format

How to Translate Messages using the ICU MessageFormat
=====================================================

Messages (i.e. strings) in applications are almost never completely static.
They contain variables or other complex logic like pluralization. To
handle this, the Translator component supports the `ICU MessageFormat`_ syntax.

.. tip::

    You can test out examples of the ICU MessageFormatter in this `online editor`_.

Using the ICU Message Format
----------------------------

In order to use the ICU Message Format, the message domain has to be
suffixed with ``+intl-icu``:

======================  ===============================
Normal file name        ICU Message Format filename
======================  ===============================
``messages.en.yaml``    ``messages+intl-icu.en.yaml``
``messages.fr_FR.xlf``  ``messages+intl-icu.fr_FR.xlf``
``admin.en.yaml``       ``admin+intl-icu.en.yaml``
======================  ===============================

All messages in this file will now be processed by the
:phpclass:`MessageFormatter` during translation.

.. _component-translation-placeholders:

Message Placeholders
--------------------

The basic usage of the MessageFormat allows you to use placeholders (called
*arguments* in ICU MessageFormat) in your messages:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml
        say_hello: 'Hello {name}!'

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="say_hello">
                        <source>say_hello</source>
                        <target>Hello {name}!</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            'say_hello' => "Hello {name}!",
        ];


.. caution::

    In the previous translation format, placeholders were often wrapped in ``%``
    (e.g. ``%name%``). This ``%`` character is no longer valid with the ICU
    MessageFormat syntax, so you must rename your parameters if you are upgrading
    from the previous format.

Everything within the curly braces (``{...}``) is processed by the formatter
and replaced by its placeholder::

    // prints "Hello Fabien!"
    echo $translator->trans('say_hello', ['name' => 'Fabien']);

    // prints "Hello Symfony!"
    echo $translator->trans('say_hello', ['name' => 'Symfony']);

Selecting Different Messages Based on a Condition
-------------------------------------------------

The curly brace syntax allows to "modify" the output of the variable. One of
these functions is the ``select`` function. It acts like PHP's `switch statement`_
and allows you to use different strings based on the value of the variable. A
typical usage of this is gender:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml

        # the 'other' key is required, and is selected if no other case matches
        invitation_title: >-
            {organizer_gender, select,
                female   {{organizer_name} has invited you to her party!}
                male     {{organizer_name} has invited you to his party!}
                multiple {{organizer_name} have invited you to their party!}
                other    {{organizer_name} has invited you to their party!}
            }

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="invitation_title">
                        <source>invitation_title</source>
                        <!-- the 'other' key is required, and is selected if no other case matches -->
                        <target>{organizer_gender, select,
                            female   {{organizer_name} has invited you to her party!}
                            male     {{organizer_name} has invited you to his party!}
                            multiple {{organizer_name} have invited you to their party!}
                            other    {{organizer_name} has invited you to their party!}
                        }</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            // the 'other' key is required, and is selected if no other case matches
            'invitation_title' => '{organizer_gender, select,
                female   {{organizer_name} has invited you to her party!}
                male     {{organizer_name} has invited you to his party!}
                multiple {{organizer_name} have invited you to their party!}
                other    {{organizer_name} has invited you to their party!}
            }',
        ];

This might look very complex. The basic syntax for all functions is
``{variable_name, function_name, function_statement}`` (where, as you see
later, ``function_statement`` is optional for some functions). In this case,
the function name is ``select`` and its statement contains the "cases" of this
select. This function is applied over the ``organizer_gender`` variable::

    // prints "Ryan has invited you to his party!"
    echo $translator->trans('invitation_title', [
        'organizer_name' => 'Ryan',
        'organizer_gender' => 'male',
    ]);

    // prints "John & Jane have invited you to their party!"
    echo $translator->trans('invitation_title', [
        'organizer_name' => 'John & Jane',
        'organizer_gender' => 'multiple',
    ]);

    // prints "ACME Company has invited you to their party!"
    echo $translator->trans('invitation_title', [
        'organizer_name' => 'ACME Company',
        'organizer_gender' => 'not_applicable',
    ]);

The ``{...}`` syntax alternates between "literal" and "code" mode. This allows
you to use literal text in the select statements:

#. The first ``{organizer_gender, select, ...}`` block starts the "code" mode,
   which means ``organizer_gender`` is processed as a variable.
#. The inner ``{... has invited you to her party!}`` block brings you back in
   "literal" mode, meaning the text is not processed.
#. Inside this block, ``{organizer_name}`` starts "code" mode again, allowing
   ``organizer_name`` to be processed as a variable.

.. tip::

    While it might seem more logical to only put ``her``, ``his`` or ``their``
    in the switch statement, it is better to use "complex arguments" at the
    outermost structure of the message. The strings are in this way better
    readable for translators and, as you can see in the ``multiple`` case, other
    parts of the sentence might be influenced by the variables.

.. tip::

    It's possible to translate ICU MessageFormat messages directly in code,
    without having to define them in any file::

        $invitation = '{organizer_gender, select,
            female   {{organizer_name} has invited you to her party!}
            male     {{organizer_name} has invited you to his party!}
            multiple {{organizer_name} have invited you to their party!}
            other    {{organizer_name} has invited you to their party!}
        }';

        // prints "Ryan has invited you to his party!"
        echo $translator->trans(
            $invitation,
            [
                'organizer_name' => 'Ryan',
                'organizer_gender' => 'male',
            ],
            // if you prefer, the required "+intl-icu" suffix is also defined as a constant:
            // Symfony\Component\Translation\MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            'messages+intl-icu'
        );

.. _component-translation-pluralization:

Pluralization
-------------

Another interesting function is ``plural``. It allows you to
handle pluralization in your messages (e.g. ``There are 3 apples`` vs
``There is one apple``). The function looks very similar to the ``select`` function:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml
        num_of_apples: >-
            {apples, plural,
                =0    {There are no apples}
                one   {There is one apple...}
                other {There are # apples!}
            }

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="num_of_apples">
                        <source>num_of_apples</source>
                        <target>{apples, plural, =0 {There are no apples} one {There is one apple...} other {There are # apples!}}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            'num_of_apples' => '{apples, plural,
                =0    {There are no apples}
                one   {There is one apple...}
                other {There are # apples!}
            }',
        ];

Pluralization rules are actually quite complex and differ for each language.
For instance, Russian uses different plural forms for numbers ending with 1;
numbers ending with 2, 3 or 4; numbers ending with 5, 6, 7, 8 or 9; and even
some exceptions to this!

In order to properly translate this, the possible cases in the ``plural``
function are also different for each language. For instance, Russian has
``one``, ``few``, ``many`` and ``other``, while English has only ``one`` and
``other``. The full list of possible cases can be found in Unicode's
`Language Plural Rules`_ document. By prefixing with ``=``, you can match exact
values (like ``0`` in the above example).

Usage of this string is the same as with variables and select::

    // prints "There is one apple..."
    echo $translator->trans('num_of_apples', ['apples' => 1]);

    // prints "There are 23 apples!"
    echo $translator->trans('num_of_apples', ['apples' => 23]);

.. note::

    You can also set an ``offset`` variable to determine whether the
    pluralization should be offset (e.g. in sentences like ``You and # other people``
    / ``You and # other person``).

.. tip::

    When combining the ``select`` and ``plural`` functions, try to still have
    ``select`` as outermost function:

    .. code-block:: text

        {gender_of_host, select,
            female {{num_guests, plural, offset:1
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to her party.}
                =2    {{host} invites {guest} and one other person to her party.}
                other {{host} invites {guest} and # other people to her party.}
            }}
            male {{num_guests, plural, offset:1
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to his party.}
                =2    {{host} invites {guest} and one other person to his party.}
                other {{host} invites {guest} and # other people to his party.}
            }}
            other {{num_guests, plural, offset:1
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to their party.}
                =2    {{host} invites {guest} and one other person to their party.}
                other {{host} invites {guest} and # other people to their party.}
            }}
        }

.. sidebar:: Using Ranges in Messages

    The pluralization in the legacy Symfony syntax could be used with custom
    ranges (e.g. have different messages for 0-12, 12-40 and 40+). The ICU
    message format does not have this feature. Instead, this logic should be
    moved to PHP code::

        // Instead of
        $message = $translator->trans('balance_message', $balance);
        // with a message like:
        // ]-Inf,0]Oops! I'm down|]0,1000]I still have money|]1000,Inf]I have lots of money

        // use three different messages for each range:
        if ($balance < 0) {
            $message = $translator->trans('no_money_message');
        } elseif ($balance < 1000) {
            $message = $translator->trans('some_money_message');
        } else {
            $message = $translator->trans('lots_of_money_message');
        }

Additional Placeholder Functions
--------------------------------

Besides these, the ICU MessageFormat comes with a couple other interesting functions.

Ordinal
~~~~~~~

Similar to ``plural``, ``selectordinal`` allows you to use numbers as ordinal scale:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml
        finish_place: >-
            You finished {place, selectordinal,
                one   {#st}
                two   {#nd}
                few   {#rd}
                other {#th}
            }!

        # when only formatting the number as ordinal (like above), you can also
        # use the `ordinal` function:
        finish_place: You finished {place, ordinal}!

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="finish_place">
                        <source>finish_place</source>
                        <target>You finished {place, selectordinal, one {#st} two {#nd} few {#rd} other {#th}}!</target>
                    </trans-unit>

                    <!-- when only formatting the number as ordinal (like
                         above), you can also use the `ordinal` function: -->
                    <trans-unit id="finish_place">
                        <source>finish_place</source>
                        <target>You finished {place, ordinal}!</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            'finish_place' => 'You finished {place, selectordinal,
                one {#st}
                two {#nd}
                few {#rd}
                other {#th}
            }!',

            // when only formatting the number as ordinal (like above), you can
            // also use the `ordinal` function:
            'finish_place' => 'You finished {place, ordinal}!',
        ];

.. code-block:: php

    // prints "You finished 1st!"
    echo $translator->trans('finish_place', ['place' => 1]);

    // prints "You finished 9th!"
    echo $translator->trans('finish_place', ['place' => 9]);

    // prints "You finished 23rd!"
    echo $translator->trans('finish_place', ['place' => 23]);

The possible cases for this are also shown in Unicode's `Language Plural Rules`_ document.

Date and Time
~~~~~~~~~~~~~

The date and time function allows you to format dates in the target locale
using the :phpclass:`IntlDateFormatter`:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml
        published_at: 'Published at {publication_date, date} - {publication_date, time, short}'

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="published_at">
                        <source>published_at</source>
                        <target>Published at {publication_date, date} - {publication_date, time, short}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            'published_at' => 'Published at {publication_date, date} - {publication_date, time, short}',
        ];

The "function statement" for the ``time`` and ``date`` functions can be one of
``short``, ``medium``, ``long`` or ``full``, which correspond to the
`constants defined by the IntlDateFormatter class`_::

    // prints "Published at Jan 25, 2019 - 11:30 AM"
    echo $translator->trans('published_at', ['publication_date' => new \DateTime('2019-01-25 11:30:00')]);

Numbers
~~~~~~~

The ``number`` formatter allows you to format numbers using Intl's :phpclass:`NumberFormatter`:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages+intl-icu.en.yaml
        progress: '{progress, number, percent} of the work is done'
        value_of_object: 'This artifact is worth {value, number, currency}'

    .. code-block:: xml

        <!-- translations/messages+intl-icu.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="progress">
                        <source>progress</source>
                        <target>{progress, number, percent} of the work is done</target>
                    </trans-unit>

                    <trans-unit id="value_of_object">
                        <source>value_of_object</source>
                        <target>This artifact is worth {value, number, currency}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages+intl-icu.en.php
        return [
            'progress' => '{progress, number, percent} of the work is done',
            'value_of_object' => 'This artifact is worth {value, number, currency}',
        ];

.. code-block:: php

    // prints "82% of the work is done"
    echo $translator->trans('progress', ['progress' => 0.82]);
    // prints "100% of the work is done"
    echo $translator->trans('progress', ['progress' => 1]);

    // prints "This artifact is worth $9,988,776.65"
    // if we would translate this to i.e. French, the value would be shown as
    // "9 988 776,65 €"
    echo $translator->trans('value_of_object', ['value' => 9988776.65]);

.. _`online editor`: http://format-message.github.io/icu-message-format-for-translators/
.. _`ICU MessageFormat`: https://unicode-org.github.io/icu/userguide/format_parse/messages/
.. _`switch statement`: https://www.php.net/control-structures.switch
.. _`Language Plural Rules`: http://www.unicode.org/cldr/charts/latest/supplemental/language_plural_rules.html
.. _`constants defined by the IntlDateFormatter class`: https://www.php.net/manual/en/class.intldateformatter.php
