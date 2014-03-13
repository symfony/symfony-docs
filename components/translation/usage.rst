.. index::
    single: Translation; Usage

Using the Translator
====================

Imagine you want to translate the string *"Symfony2 is great"* into French::

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\Loader\ArrayLoader;

    $translator = new Translator('fr_FR');
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', array(
        'Symfony2 is great!' => 'J\'aime Symfony2!',
    ), 'fr_FR');

    echo $translator->trans('Symfony2 is great!');

In this example, the message *"Symfony2 is great!"* will be translated into
the locale set in the constructor (``fr_FR``) if the message exists in one of
the message catalogs.

.. _component-translation-placeholders:

Message Placeholders
--------------------

Sometimes, a message containing a variable needs to be translated::

    // ...
    $translated = $translator->trans('Hello '.$name);

    echo $translated;

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. *"Hello Ryan"* or *"Hello Fabien"*). Instead of writing a translation
for every possible iteration of the ``$name`` variable, you can replace the
variable with a "placeholder"::

    // ...
    $translated = $translator->trans(
        'Hello %name%',
        array('%name%' => $name)
    );

    echo $translated;

Symfony2 will now look for a translation of the raw message (``Hello %name%``)
and *then* replace the placeholders with their values. Creating a translation
is done just as before:

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Hello %name%</source>
                        <target>Bonjour %name%</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        return array(
            'Hello %name%' => 'Bonjour %name%',
        );

    .. code-block:: yaml

        'Hello %name%': Bonjour %name%

.. note::

    The placeholders can take on any form as the full message is reconstructed
    using the PHP :phpfunction:`strtr function<strtr>`. But the ``%...%`` form
    is recommended, to avoid problems when using Twig.

As you've seen, creating a translation is a two-step process:

#. Abstract the message that needs to be translated by processing it through
   the ``Translator``.

#. Create a translation for the message in each locale that you choose to
   support.

The second step is done by creating message catalogs that define the translations
for any number of different locales.

Creating Translations
---------------------

The act of creating translation files is an important part of "localization"
(often abbreviated `L10n`_). Translation files consist of a series of
id-translation pairs for the given domain and locale. The source is the identifier
for the individual translation, and can be the message in the main locale (e.g.
*"Symfony is great"*) of your application or a unique identifier (e.g.
``symfony2.great`` - see the sidebar below).

Translation files can be created in several different formats, XLIFF being the
recommended format. These files are parsed by one of the loader classes.

.. configuration-block::

    .. code-block:: xml

        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony2 is great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                    <trans-unit id="2">
                        <source>symfony2.great</source>
                        <target>J'aime Symfony2</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        return array(
            'Symfony2 is great' => 'J\'aime Symfony2',
            'symfony2.great'    => 'J\'aime Symfony2',
        );

    .. code-block:: yaml

        Symfony2 is great: J'aime Symfony2
        symfony2.great:    J'aime Symfony2

.. sidebar:: Using Real or Keyword Messages

    This example illustrates the two different philosophies when creating
    messages to be translated::

        $translator->trans('Symfony2 is great');

        $translator->trans('symfony2.great');

    In the first method, messages are written in the language of the default
    locale (English in this case). That message is then used as the "id"
    when creating translations.

    In the second method, messages are actually "keywords" that convey the
    idea of the message. The keyword message is then used as the "id" for
    any translations. In this case, translations must be made for the default
    locale (i.e. to translate ``symfony2.great`` to ``Symfony2 is great``).

    The second method is handy because the message key won't need to be changed
    in every translation file if you decide that the message should actually
    read "Symfony2 is really great" in the default locale.

    The choice of which method to use is entirely up to you, but the "keyword"
    format is often recommended.

    Additionally, the ``php`` and ``yaml`` file formats support nested ids to
    avoid repeating yourself if you use keywords instead of real text for your
    ids:

    .. configuration-block::

        .. code-block:: yaml

            symfony2:
                is:
                    great: Symfony2 is great
                    amazing: Symfony2 is amazing
                has:
                    bundles: Symfony2 has bundles
            user:
                login: Login

        .. code-block:: php

            array(
                'symfony2' => array(
                    'is' => array(
                        'great'   => 'Symfony2 is great',
                        'amazing' => 'Symfony2 is amazing',
                    ),
                    'has' => array(
                        'bundles' => 'Symfony2 has bundles',
                    ),
                ),
                'user' => array(
                    'login' => 'Login',
                ),
            );

    The multiple levels are flattened into single id/translation pairs by
    adding a dot (``.``) between every level, therefore the above examples are
    equivalent to the following:

    .. configuration-block::

        .. code-block:: yaml

            symfony2.is.great: Symfony2 is great
            symfony2.is.amazing: Symfony2 is amazing
            symfony2.has.bundles: Symfony2 has bundles
            user.login: Login

        .. code-block:: php

            return array(
                'symfony2.is.great'    => 'Symfony2 is great',
                'symfony2.is.amazing'  => 'Symfony2 is amazing',
                'symfony2.has.bundles' => 'Symfony2 has bundles',
                'user.login'           => 'Login',
            );

.. _component-translation-pluralization:

Pluralization
-------------

Message pluralization is a tough topic as the rules can be quite complex. For
instance, here is the mathematic representation of the Russian pluralization
rules::

    (($number % 10 == 1) && ($number % 100 != 11))
        ? 0
        : ((($number % 10 >= 2)
            && ($number % 10 <= 4)
            && (($number % 100 < 10)
            || ($number % 100 >= 20)))
                ? 1
                : 2
    );

As you can see, in Russian, you can have three different plural forms, each
given an index of 0, 1 or 2. For each form, the plural is different, and
so the translation is also different.

When a translation has different forms due to pluralization, you can provide
all the forms as a string separated by a pipe (``|``)::

    'There is one apple|There are %count% apples'

To translate pluralized messages, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method::

    $translator->transChoice(
        'There is one apple|There are %count% apples',
        10,
        array('%count%' => 10)
    );

The second argument (``10`` in this example) is the *number* of objects being
described and is used to determine which translation to use and also to populate
the ``%count%`` placeholder.

Based on the given number, the translator chooses the right plural form.
In English, most words have a singular form when there is exactly one object
and a plural form for all other numbers (0, 2, 3...). So, if ``count`` is
``1``, the translator will use the first string (``There is one apple``)
as the translation. Otherwise it will use ``There are %count% apples``.

Here is the French translation:

.. code-block:: text

    'Il y a %count% pomme|Il y a %count% pommes'

Even if the string looks similar (it is made of two sub-strings separated by a
pipe), the French rules are different: the first form (no plural) is used when
``count`` is ``0`` or ``1``. So, the translator will automatically use the
first string (``Il y a %count% pomme``) when ``count`` is ``0`` or ``1``.

Each locale has its own set of rules, with some having as many as six different
plural forms with complex rules behind which numbers map to which plural form.
The rules are quite simple for English and French, but for Russian, you'd
may want a hint to know which rule matches which string. To help translators,
you can optionally "tag" each string:

.. code-block:: text

    'one: There is one apple|some: There are %count% apples'

    'none_or_one: Il y a %count% pomme|some: Il y a %count% pommes'

The tags are really only hints for translators and don't affect the logic
used to determine which plural form to use. The tags can be any descriptive
string that ends with a colon (``:``). The tags also do not need to be the
same in the original message as in the translated one.

.. tip::

    As tags are optional, the translator doesn't use them (the translator will
    only get a string based on its position in the string).

Explicit Interval Pluralization
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The easiest way to pluralize a message is to let the Translator use internal
logic to choose which string to use based on a given number. Sometimes, you'll
need more control or want a different translation for specific cases (for
``0``, or when the count is negative, for example). For such cases, you can
use explicit math intervals:

.. code-block:: text

    '{0} There are no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples'

The intervals follow the `ISO 31-11`_ notation. The above string specifies
four different intervals: exactly ``0``, exactly ``1``, ``2-19``, and ``20``
and higher.

You can also mix explicit math rules and standard rules. In this case, if
the count is not matched by a specific interval, the standard rules take
effect after removing the explicit rules:

.. code-block:: text

    '{0} There are no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples'

For example, for ``1`` apple, the standard rule ``There is one apple`` will
be used. For ``2-19`` apples, the second standard rule ``There are %count%
apples`` will be selected.

An :class:`Symfony\\Component\\Translation\\Interval` can represent a finite set
of numbers:

.. code-block:: text

    {1,2,3,4}

Or numbers between two other numbers:

.. code-block:: text

    [1, +Inf[
    ]-1,2[

The left delimiter can be ``[`` (inclusive) or ``]`` (exclusive). The right
delimiter can be ``[`` (exclusive) or ``]`` (inclusive). Beside numbers, you
can use ``-Inf`` and ``+Inf`` for the infinite.

Forcing the Translator Locale
-----------------------------

When translating a message, the Translator uses the specified locale or the
``fallback`` locale if necessary. You can also manually specify the locale to
use for translation::

    $translator->trans(
        'Symfony2 is great',
        array(),
        'messages',
        'fr_FR'
    );

    $translator->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        array('%count%' => 10),
        'messages',
        'fr_FR'
    );

.. _`L10n`: http://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 31-11`: http://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals
