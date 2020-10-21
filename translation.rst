.. index::
   single: Translations

Translations
============

The term "internationalization" (often abbreviated `i18n`_) refers to the
process of abstracting strings and other locale-specific pieces out of your
application into a layer where they can be translated and converted based
on the user's locale (i.e. language and country). For text, this means
wrapping each with a function capable of translating the text (or "message")
into the language of the user::

    // text will *always* print out in English
    dump('Hello World');
    die();

    // text can be translated into the end-user's language or
    // default to English
    dump($translator->trans('Hello World'));
    die();

.. note::

    The term *locale* refers roughly to the user's language and country. It
    can be any string that your application uses to manage translations and
    other format differences (e.g. currency format). The `ISO 639-1`_
    *language* code, an underscore (``_``), then the `ISO 3166-1 alpha-2`_
    *country* code (e.g. ``fr_FR`` for French/France) is recommended.

The translation process has several steps:

#. :ref:`Enable and configure <translation-configuration>` Symfony's
   translation service;

#. Abstract strings (i.e. "messages") by wrapping them in calls to the
   ``Translator`` (":ref:`translation-basic`");

#. :ref:`Create translation resources/files <translation-resources>`
   for each supported locale that translate each message in the application;

#. Determine, :doc:`set and manage the user's locale </translation/locale>`
   for the request and optionally
   :doc:`on the user's entire session </session/locale_sticky_session>`.

.. _translation-configuration:

Configuration
-------------

Translations are handled by a ``translator`` service that uses the user's
locale to lookup and return translated messages. Before using it, enable the
``translator`` in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            translator: { fallbacks: [en] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:translator>
                    <framework:fallback>en</framework:fallback>
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', [
            'translator' => ['fallbacks' => ['en']],
        ]);

See :ref:`translation-fallback` for details on the ``fallbacks`` key
and what Symfony does when it doesn't find a translation.

The locale used in translations is the one stored on the request. This is
typically set via a ``_locale`` attribute on your routes (see :ref:`translation-locale-url`).

.. _translation-basic:

Basic Translation
-----------------

Translation of text is done through the  ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). To translate a block
of text (called a *message*), use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method. Suppose,
for example, that you're translating a simple message from inside a controller::

    // ...

    public function indexAction()
    {
        $translated = $this->get('translator')->trans('Symfony is great');

        // ...
    }

.. _translation-resources:

When this code is executed, Symfony will attempt to translate the message
"Symfony is great" based on the ``locale`` of the user. For this to work,
you need to tell Symfony how to translate the message via a "translation
resource", which is usually a file that contains a collection of translations
for a given locale. This "dictionary" of translations can be created in several
different formats:

.. configuration-block::

    .. code-block:: yaml

        # messages.fr.yml
        Symfony is great: J'aime Symfony

    .. code-block:: xml

        <!-- messages.fr.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="symfony_is_great">
                        <source>Symfony is great</source>
                        <target>J'aime Symfony</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // messages.fr.php
        return [
            'Symfony is great' => 'J\'aime Symfony',
        ];

For information on where these files should be located, see
:ref:`translation-resource-locations`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony``. You can also translate
the message inside your :ref:`templates <translation-tags>`.

.. _translation-real-vs-keyword-messages:

Using Real or Keyword Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This example illustrates the two different philosophies when creating
messages to be translated::

    $translator->trans('Symfony is great');

    $translator->trans('symfony.great');

In the first method, messages are written in the language of the default
locale (English in this case). That message is then used as the "id"
when creating translations.

In the second method, messages are actually "keywords" that convey the
idea of the message. The keyword message is then used as the "id" for
any translations. In this case, translations must be made for the default
locale (i.e. to translate ``symfony.great`` to ``Symfony is great``).

The second method is handy because the message key won't need to be changed
in every translation file if you decide that the message should actually
read "Symfony is really great" in the default locale.

The choice of which method to use is entirely up to you, but the "keyword"
format is often recommended for multi-language applications, whereas for
shared bundles that contain translation resources we recommend the real
message, so your application can choose to disable the translator layer
and you will see a readable message.

Additionally, the ``php`` and ``yaml`` file formats support nested ids to
avoid repeating yourself if you use keywords instead of real text for your
ids:

.. configuration-block::

    .. code-block:: yaml

        symfony:
            is:
                # id is symfony.is.great
                great: Symfony is great
                # id is symfony.is.amazing
                amazing: Symfony is amazing
            has:
                # id is symfony.has.bundles
                bundles: Symfony has bundles
        user:
            # id is user.login
            login: Login

    .. code-block:: php

        [
            'symfony' => [
                'is' => [
                    // id is symfony.is.great
                    'great'   => 'Symfony is great',
                    // id is symfony.is.amazing
                    'amazing' => 'Symfony is amazing',
                ],
                'has' => [
                    // id is symfony.has.bundles
                    'bundles' => 'Symfony has bundles',
                ],
            ],
            'user' => [
                // id is user.login
                'login' => 'Login',
            ],
        ];

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony uses a simple process:

#. The ``locale`` of the current user, which is stored on the request is determined;

#. A catalog (i.e. big collection) of translated messages is loaded from translation
   resources defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
   :ref:`fallback locale <translation-fallback>` are also loaded and
   added to the catalog if they don't already exist. The end result is a large
   "dictionary" of translations.

#. If the message is located in the catalog, the translation is returned. If
   not, the translator returns the original message.

When using the ``trans()`` method, Symfony looks for the exact string inside
the appropriate message catalog and returns it (if it exists).

.. tip::

    When translating strings that are not in the default domain (``messages``),
    you must specify the domain as the third argument of ``trans()``::

        $translator->trans('Symfony is great', [], 'admin');

Message Placeholders
~~~~~~~~~~~~~~~~~~~~

Sometimes, a message containing a variable needs to be translated::

    public function indexAction($name)
    {
        $translated = $this->get('translator')->trans('Hello '.$name.'!');

        // ...
    }

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. *"Hello Ryan!"* or *"Hello Fabien!"*). Instead of writing a translation
for every possible iteration of the ``$name`` variable, you can replace the
variable with a "placeholder"::

    // ...

    public function indexAction($name)
    {
        $translated = $this->get('translator')->trans('Hello %name%');

        // ...
    }

Symfony will now look for a translation of the raw message (``Hello %name%``)
and *then* replace the placeholders with their values. Creating a translation
is done just as before:

.. configuration-block::

    .. code-block:: yaml

        'Hello %name%!': Bonjour %name% !

    .. code-block:: xml

        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Hello %name%!</source>
                        <target>Bonjour %name% !</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        return [
            'Hello %name%!' => 'Bonjour %name% !',
        ];

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

Pluralization
-------------

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

When a translation has different forms due to pluralization, you can provide
all the forms as a string separated by a pipe (``|``)::

    'There is one apple|There are %count% apples'

To translate these messages, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice`
method or the ``transchoice`` tag/filter in your :ref:`template <translation-tags>`.
To translate pluralized messages, use the
:method:`Symfony\\Component\\Translation\\Translator::transChoice` method::

    // the %count% placeholder is assigned to the second argument...
    $this->get('translator')->transChoice(
        'There is one apple|There are %count% apples',
        10
    );

    // ...but you can define more placeholders if needed
    $this->get('translator')->transChoice(
        'Hurry up %name%! There is one apple left.|There are %count% apples left.',
        10,
        // no need to include %count% here; Symfony does that for you
        ['%name%' => $user->getName()]
    );

The second argument (``10`` in this example) is the *number* of objects being
described and is used to determine which translation to use and also to populate
the ``%count%`` placeholder.

.. versionadded:: 3.2

    Before Symfony 3.2, the placeholder used to select the plural (``%count%``
    in this example) must be included in the third optional argument of the
    ``transChoice()`` method::

        $translator->transChoice(
            'There is one apple|There are %count% apples',
            10,
            ['%count%' => 10]
        );

    Starting from Symfony 3.2, when the only placeholder is ``%count%``, you
    don't have to pass this third argument.

Based on the given number, the translator chooses the right plural form.
In English, most words have a singular form when there is exactly one object
and a plural form for all other numbers (0, 2, 3...). So, if ``count`` is
``1``, the translator will use the first string (``There is one apple``)
as the translation. Otherwise it will use ``There are %count% apples``.

Here is the French translation::

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

    '{0} There are no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf[ There are many apples'

The intervals follow the `ISO 31-11`_ notation. The above string specifies
four different intervals: exactly ``0``, exactly ``1``, ``2-19``, and ``20``
and higher.

You can also mix explicit math rules and standard rules. In this case, if
the count is not matched by a specific interval, the standard rules take
effect after removing the explicit rules:

.. code-block:: text

    '{0} There are no apples|[20,Inf[ There are many apples|There is one apple|a_few: There are %count% apples'

For example, for ``1`` apple, the standard rule ``There is one apple`` will
be used. For ``2-19`` apples, the second standard rule
``There are %count% apples`` will be selected.

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

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony provides native
support for both Twig and PHP templates.

.. _translation-tags:

Twig Templates
~~~~~~~~~~~~~~

Symfony provides specialized Twig tags (``trans`` and ``transchoice``) to
help with message translation of *static blocks of text*:

.. code-block:: twig

    {% trans %}Hello %name%{% endtrans %}

    {% transchoice count %}
        {0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples
    {% endtranschoice %}

The ``transchoice`` tag automatically gets the ``%count%`` variable from
the current context and passes it to the translator. This mechanism only
works when you use a placeholder following the ``%var%`` pattern.

.. caution::

    The ``%var%`` notation of placeholders is required when translating in
    Twig templates using the tag.

.. tip::

    If you need to use the percent character (``%``) in a string, escape it by
    doubling it: ``{% trans %}Percent: %percent%%%{% endtrans %}``

You can also pass some additional variables and specify the message domain:

.. code-block:: twig

    {% trans with {'%name%': 'Fabien'} into 'fr' %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} %}
        {0} %name%, there are no apples|{1} %name%, there is one apple|]1,Inf[ %name%, there are %count% apples
    {% endtranschoice %}

    {% trans with {'%name%': 'Fabien'} from 'custom_domain' %}Hello %name%{% endtrans %}

.. _translation-filters:

The ``trans`` and ``transchoice`` filters can be used to translate *variable
texts* and complex expressions:

.. code-block:: twig

    {{ message|trans }}

    {{ message|transchoice(5) }}

    {{ message|trans({'%name%': 'Fabien'}, 'app') }}

    {{ message|transchoice(5, {'%name%': 'Fabien'}, 'app') }}

.. tip::

    Using the translation tags or filters have the same effect, but with
    one subtle difference: automatic output escaping is only applied to
    translations using a filter. In other words, if you need to be sure
    that your translated message is *not* output escaped, you must apply
    the ``raw`` filter after the translation filter:

    .. code-block:: html+twig

        {# text translated between tags is never escaped #}
        {% trans %}
            <h3>foo</h3>
        {% endtrans %}

        {% set message = '<h3>foo</h3>' %}

        {# strings and variables translated via a filter are escaped by default #}
        {{ message|trans|raw }}
        {{ '<h3>bar</h3>'|trans|raw }}

.. tip::

    You can set the translation domain for an entire Twig template with a single tag:

    .. code-block:: twig

        {% trans_default_domain 'app' %}

    Note that this only influences the current template, not any "included"
    template (in order to avoid side effects).

PHP Templates
~~~~~~~~~~~~~

The translator service is accessible in PHP templates through the
``translator`` helper:

.. code-block:: html+php

    <?= $view['translator']->trans('Symfony is great') ?>

    <?= $view['translator']->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        ['%count%' => 10]
    ) ?>

Forcing the Translator Locale
-----------------------------

When translating a message, the translator uses the specified locale or the
``fallback`` locale if necessary. You can also manually specify the locale to
use for translation::

    $translator->trans(
        'Symfony is great',
        [],
        'messages',
        'fr_FR'
    );

    $translator->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        [],
        'messages',
        'fr_FR'
    );

Extracting Translation Contents and Updating Catalogs Automatically
-------------------------------------------------------------------

The most time-consuming tasks when translating an application is to extract all
the template contents to be translated and to keep all the translation files in
sync. Symfony includes a command called ``translation:update`` that helps you
with these tasks:

.. code-block:: terminal

    # updates the French translation file with the missing strings found in app/Resources/ templates
    $ php bin/console translation:update --dump-messages --force fr

    # updates the English translation file with the missing strings found in AppBundle
    $ php bin/console translation:update --dump-messages --force en AppBundle

.. note::

    If you want to see the missing translation strings without actually updating
    the translation files, remove the ``--force`` option from the command above.

.. tip::

    If you need to extract translation strings from other sources, such as
    controllers, forms and flash messages, consider using the more advanced
    third-party `TranslationBundle`_.

.. _translation-resource-locations:

Translation Resource/File Names and Locations
---------------------------------------------

Symfony looks for message files (i.e. translations) in the following default locations:

* the ``translations/`` directory (at the root of the project);

* the ``app/Resources/translations`` directory;

* the ``app/Resources/<bundle name>/translations`` directory;

* the ``Resources/translations/`` directory inside of any bundle.

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in any of the top 2 directories.

The override mechanism works at a key level: only the overridden keys need
to be listed in a higher priority message file. When a key is not found
in a message file, the translator will automatically fall back to the lower
priority message files.

The filename of the translation files is also important: each message file
must be named according to the following path: ``domain.locale.loader``:

* **domain**: Domains are a way to organize messages into groups. Unless
  parts of the application are explicitly separated from each other, it is
  recommended to only use default ``messages`` domain.

  If no domains are explicitly defined while using the translator, Symfony 
  will default to the ``messages`` domain (e.g. ``messages.en.yaml``)

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony should load and parse the file (e.g. ``xlf``,
  ``php``, ``yml``, etc).

The loader can be the name of any registered loader. By default, Symfony
provides many loaders:

* ``.yaml``: YAML file
* ``.xlf``: XLIFF file;
* ``.php``: Returning a PHP array;
* ``.csv``: CSV file;
* ``.json``: JSON file;
* ``.ini``: INI file;
* ``.dat``, ``.res``: ICU resource bundle;
* ``.mo``: Machine object format;
* ``.po``: Portable object format;
* ``.qt``: QT Translations XML file;

The choice of which loader to use is entirely up to you and is a matter of
taste. The recommended option is to use YAML for simple projects and use XLIFF
if you're generating translations with specialized programs or teams.

.. caution::

    Each time you create a *new* message catalog (or install a bundle
    that includes a translation catalog), be sure to clear your cache so
    that Symfony can discover the new translation resources:

    .. code-block:: terminal

        $ php bin/console cache:clear

.. note::

    You can add other directories with the :ref:`paths <reference-translator-paths>`
    option in the configuration:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            framework:
                translator:
                    paths:
                        - '%kernel.project_dir%/translations'

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <framework:config>
                    <framework:translator>
                        <framework:path>%kernel.project_dir%/translations</framework:path>
                    </framework:translator>
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', [
                'translator' => [
                    'paths' => [
                        '%kernel.project_dir%/translations',
                    ],
                ],
            ]);

.. note::

    You can also store translations in a database, or any other storage by
    providing a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface.
    See the :ref:`dic-tags-translation-loader` tag for more information.

.. _translation-fallback:

Fallback Translation Locales
----------------------------

Imagine that the user's locale is ``fr_FR`` and that you're translating the
key ``Symfony is great``. To find the French translation, Symfony actually
checks translation resources for several locales:

#. First, Symfony looks for the translation in a ``fr_FR`` translation resource
   (e.g. ``messages.fr_FR.xlf``);

#. If it wasn't found, Symfony looks for the translation in a ``fr`` translation
   resource (e.g. ``messages.fr.xlf``);

#. If the translation still isn't found, Symfony uses the ``fallbacks`` configuration
   parameter, which defaults to ``en`` (see `Configuration`_).

.. note::

    When Symfony doesn't find a translation in the given locale, it will
    add the missing translation to the log file. For details,
    see :ref:`reference-framework-translator-logging`.

Handling the User's Locale
--------------------------

Translating happens based on the user's locale. Read :doc:`/translation/locale`
to learn more about how to handle it.

Translating Database Content
----------------------------

The translation of database content should be handled by Doctrine through
the `Translatable Extension`_ or the `Translatable Behavior`_ (PHP 5.4+).
For more information, see the documentation for these libraries.

Debugging Translations
----------------------

When you work with many translation messages in different languages, it can
be hard to keep track which translations are missing and which are not used
anymore. Read :doc:`/translation/debug` to find out how to identify these
messages.

Summary
-------

With the Symfony Translation component, creating an internationalized application
no longer needs to be a painful process and boils down to just a few basic
steps:

* Abstract messages in your application by wrapping each in either the
  :method:`Symfony\\Component\\Translation\\Translator::trans` or
  :method:`Symfony\\Component\\Translation\\Translator::transChoice` methods;

* Translate each message into multiple locales by creating translation message
  files. Symfony discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set on the user's session.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /translation/*
    /validation/translations

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 31-11`: https://en.wikipedia.org/wiki/Interval_(mathematics)#Notations_for_intervals
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: http://atlantic18.github.io/DoctrineExtensions/doc/translatable.html
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
.. _`TranslationBundle`: https://github.com/php-translation/symfony-bundle
