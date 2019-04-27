.. index::
    single: Translation; Message Format

How to translate messages using the ICU MessageFormat
=====================================================

.. versionadded:: 4.2

   Support for ICU MessageFormat was introduced in Symfony 4.2.

Messages (i.e. strings) in applications are almost never completely static.
They contain variables or other complexer logic like pluralization. In order to
handle this, the Translator component supports the `ICU MessageFormat`_ syntax.

Using the ICU Message Format
----------------------------

In order to use the ICU Message Format, the :ref:`message domain
<using-message-domains>` has to be suffixed with ``intl-icu``:

======================  ===============================
Normal file name        ICU Message Format filename
======================  ===============================
``messages.en.yml``     ``messages+intl-icu.en.yml``
``messages.fr_FR.xlf``  ``messages+intl-icu.fr_FR.xlf``
``admin.en.yml``        ``admin+intl-icu.en.yml``
======================  ===============================

All messages in this file will now be processed by the
:phpclass:`MessageFormatter` during translation.

.. _component-translation-placeholders:

Message Placeholders
--------------------

The basic usage of the MessageFormat allows you to use placeholder (called
*arguments* in ICU MessageFormat) in your messages:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        say_hello: 'Hello {name}!'

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
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

        // translations/messages.en.php
        return [
            'say_hello' => "Hello {name}!",
        ];

Everything within the curly braces (``{...}``) is processed by the formatter
and replaced by it's placeholder::

    // ...
    echo $translator->trans('say_hello', ['name' => 'Fabien']); // Hello Fabien!
    echo $translator->trans('say_hello', ['name' => 'Symfony']); // Hello Symfony!

Selecting Different Messages Based on a Condition
-------------------------------------------------

The curly brace syntax allows to "modify" the output of the variable. One of
these functions is the ``switch`` function. It acts like PHP's `switch statement`_
and allows to use different strings based on the value of the variable. A
typical usage of this is gender:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        invitation_title: >
            {organizer_gender, select,
                female {{organizer_name} has invited you for her party!}
                male   {{organizer_name} has invited you for his party!}
                other  {{organizer_name} have invited you for their party!}
            }

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="invitation_title">
                        <source>invitation_title</source>
                        <target>{organizer_gender, select, female {{organizer_name} has invited you for her party!} male   {{organizer_name} has invited you for his party!} other {{organizer_name} have invited you for their party!}}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'invitation_title' => '{organizer_gender, select,
                female {{organizer_name} has invited you for her party!}
                male   {{organizer_name} has invited you for his party!}
                other  {{organizer_name} have invited you for their party!}
            }',
        ];

This might look very complex. The basic syntax for all functions is
``{variable_name, function_name, function_statement}``. In this case, the
function name is ``select`` and its statement contains the "cases" of this
select. This function is applied over the ``organizer_gender`` variable::

    // ...

    // prints "Ryan has invited you for his party!"
    echo $translator->trans('invition_title', [
        'organizer_name' => 'Ryan',
        'organizer_gender' => 'male',
    ]);

    // prints "John & Jane have invited you for their party!"
    echo $translator->trans('invition_title', [
        'organizer_name' => 'John & Jane',
        'organizer_gender' => 'not_applicable',
    ]);

The ``{...}`` syntax alternates between "literal" and "code" mode. This allows
you to use literal text in the select statements:

#. The first ``{organizer_gender, select, ...}`` block starts the "code" mode,
   which means ``organizer_gender`` is processed as a variable.
#. The inner ``{... has invited you for her party!}`` block brings you back in
   "literal" mode, meaning the text is not processed.
#. Inside this block, ``{organizer_name}`` starts "code" mode again, allowing
   ``organizer_name`` to be processed as variable.

.. tip::

    While it might seem more logical to only put ``her``, ``his`` or ``their``
    in the switch statement, it is better to use "complex arguments" at the
    outermost structure of the message. The strings are in this way better
    readable for translators and, as you can see in the ``other`` case, other
    parts of the sentence mgith be influenced by the variable.

Pluralization
-------------

Another interesting function is ``plural``. It allows you to
handle pluralization in your messages (e.g. ``There are 3 apples`` vs
``There is one apple``). The function looks very similair to the ``select`` function:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        nr_of_apples: >
            {apples, plural,
                =0    {There are no apples :(}
                one   {There is one apple...}
                other {There are # apples!}
            }

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="nr_of_apples">
                        <source>nr_of_apples</source>
                        <target>{apples, plural, =0 {There are no apples :(} one {There is one apple...} other {There are # apples!}}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'nr_of_apples' => '{apples, plural,
                =0    {There are no apples :(}
                one   {There is one apple...}
                other {There are # apples!}
            }',
        ];

Pluralization rules are actually quite complex and differ for each language.
For instance, here is the mathematical representation of the Russian
pluralization rules::

    (($number % 10 == 1) && ($number % 100 != 11))
        ? 0
        : ((($number % 10 >= 2)
            && ($number % 10 <= 4)
            && (($number % 100 < 10)
            || ($number % 100 >= 20)))
                ? 1
                : 2
    );

In order to facilitate this, the possible cases in the ``plural`` function are
also different for each language. For instance, Russian has ``one``, ``few``,
``many`` and ``other``, while English has only ``one`` and ``other``. The full
list of possible cases can be found in Unicode's `Language Plural Rules`_
document. By prefixing with ``=``, you can match exact values (like ``0`` in
the above example).

Usage of this string is the same as with variables and select::

    // ...

    // prints "There is one apple..."
    echo $translator->trans('nr_of_apples', ['apples' => 1]);

    // prints "There are 23 apples!"
    echo $translator->trans('nr_of_apples', ['apples' => 23]);

.. note::

    You can also set an ``offset`` variable to determine whether the
    pluralization should be offset (e.g. in sentences like ``You and # other people``
    / ``You and # other person``). 

.. tip::

    When combining the ``select`` and ``plural`` functions, try to still have
    ``select`` as outermost function:

    .. code-block:: text

		{gender_of_host, select, 
            female {
                {num_guests, plural, offset:1 
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to her party.}
                =2    {{host} invites {guest} and one other person to her party.}
                other {{host} invites {guest} and # other people to her party.}}
            }
            male {
                {num_guests, plural, offset:1 
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to his party.}
                =2    {{host} invites {guest} and one other person to his party.}
                other {{host} invites {guest} and # other people to his party.}}
            }
            other {
                {num_guests, plural, offset:1 
                =0    {{host} does not give a party.}
                =1    {{host} invites {guest} to their party.}
                =2    {{host} invites {guest} and one other person to their party.}
                other {{host} invites {guest} and # other people to their party.}}
            }
        }

Other Placeholder Functions
---------------------------

Besides these, the MessageFormat comes with a couple other interesting functions.

Ordinal
~~~~~~~

Similair to ``plural``, ``selectordinal`` allows you to use numbers as ordinal scale:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        finish_place: >
            You finished {place, selectordinal,
                one {#st}
                two {#nd}
                few {#rd}
                other {#th}
            }!

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="finish_place">
                        <source>finish_place</source>
                        <target>{apples, plural, =0 {There are no apples :(} one {There is one apple...} other {There are # apples!}}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'finish_place' => 'You finished {place, selectordinal,
                one {#st}
                two {#nd}
                few {#rd}
                other {#th}
            }!',
        ];

.. code-block:: php

    // ...

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

        # translations/messages.en.yaml
        published_at: 'Published at {time, date} - {time, time, short}'

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="published_at">
                        <source>published_at</source>
                        <target>Published at {time, date} - {time, time, short}</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'published_at' => 'Published at {time, date} - {time, time, short}',
        ];

.. code-block:: php

    // ...

    // prints "Published at Jan 25, 2019 - 11:30 AM"
    echo $translator->trans('published_at', ['time' => new \DateTime('2019-01-25 11:30:00')]);

Numbers
~~~~~~~

The ``number`` formatter allows you to format numbers using Intl's :phpclass:`NumberFormatter`:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        progress: '{progress, number, percent} of the work is done'
        value_of_object: 'This artifact is worth {value, number, currency}'

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
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

        // translations/messages.en.php
        return [
            'progress' => '{progress, number, percent} of the work is done',
            'value_of_object' => 'This artifact is worth {value, number, currency}',
        ];

.. code-block:: php

    // ...

    // prints "82% of the work is done"
    echo $translator->trans('progress', ['progress' => 0.82]);
    // prints "100% of the work is done"
    echo $translator->trans('progress', ['progress' => 1]);

    // prints "This artifact is worth $9,988,776.65"
    // if we would translate this to i.e. French, the value would be shown as
    // "9 988 776,65 €"
    echo $translator->trans('value_of_object', ['value' => 9988776.65]);

.. _`ICU MessageFormat`: http://userguide.icu-project.org/formatparse/messages
.. _`switch statement`: https://php.net/control-structures.switch
.. _`Language Plural Rules`: http://www.unicode.org/cldr/charts/latest/supplemental/language_plural_rules.html
