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

In this article, you'll learn how to use the Translation component in the
Symfony Framework. You can read the
:doc:`Translation component documentation </components/translation/usage>`
to learn even more. Overall, the process has several steps:

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

Installation
------------

First, run this command to install the translator before using it:

.. code-block:: terminal

    $ composer require symfony/translation

Configuration
-------------

The previous command creates an initial config file where you can define the
default locale of the app and the :ref:`fallback locales <translation-fallback>`
that will be used if Symfony can't find some translation:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            default_locale: 'en'
            translator:
                fallbacks: ['en']
                # ...

    .. code-block:: xml

        <!-- config/packages/translation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config default-locale="en">
                <framework:translator>
                    <framework:fallback>en</framework:fallback>
                    <!-- ... -->
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['fallbacks' => ['en']],
            // ...
        ]);

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
    use Symfony\Contracts\Translation\TranslatorInterface;

    public function index(TranslatorInterface $translator)
    {
        $translated = $translator->trans('Symfony is great');

        // ...
    }

.. _translation-resources:

When this code is executed, Symfony will attempt to translate the message
"Symfony is great" based on the ``locale`` of the user. For this to work,
you need to tell Symfony how to translate the message via a "translation
resource", which is usually a file that contains a collection of translations
for a given locale. This "dictionary" of translations can be created in several
different formats, XLIFF being the recommended format:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
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

    .. code-block:: yaml

        # translations/messages.fr.yaml
        Symfony is great: J'aime Symfony

    .. code-block:: php

        // translations/messages.fr.php
        return [
            'Symfony is great' => 'J\'aime Symfony',
        ];

For information on where these files should be located, see
:ref:`translation-resource-locations`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony``. You can also translate
the message inside your :ref:`templates <translation-tags>`.

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony uses the following process:

* The ``locale`` of the current user, which is stored on the request is determined;

* A catalog (e.g. big collection) of translated messages is loaded from translation
  resources defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
  :ref:`fallback locale <translation-fallback>` are also loaded and
  added to the catalog if they don't already exist. The end result is a large
  "dictionary" of translations.

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

When using the ``trans()`` method, Symfony looks for the exact string inside
the appropriate message catalog and returns it (if it exists).

Message Placeholders
--------------------

Sometimes, a message containing a variable needs to be translated::

    use Symfony\Contracts\Translation\TranslatorInterface;

    public function index(TranslatorInterface $translator, $name)
    {
        $translated = $translator->trans('Hello '.$name);

        // ...
    }

However, creating a translation for this string is impossible since the translator
will try to look up the exact message, including the variable portions
(e.g. *"Hello Ryan"* or *"Hello Fabien"*).

For details on how to handle this situation, see :ref:`component-translation-placeholders`
in the components documentation. For how to do this in templates, see :ref:`translation-tags`.

Pluralization
-------------

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

To handle this, use the :method:`Symfony\\Component\\Translation\\Translator::transChoice`
method or the ``transchoice`` tag/filter in your :ref:`template <translation-tags>`.

For much more information, see :ref:`component-translation-pluralization`
in the Translation component documentation.

.. deprecated:: 4.2

    In Symfony 4.2 the ``Translator::transChoice()`` method was deprecated in
    favor of using ``Translator::trans()`` with ``%count%`` as the parameter
    driving plurals.

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

You can also specify the message domain and pass some additional variables:

.. code-block:: twig

    {% trans with {'%name%': 'Fabien'} from 'app' %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from 'app' into 'fr' %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} from 'app' %}
        {0} %name%, there are no apples|{1} %name%, there is one apple|]1,Inf[ %name%, there are %count% apples
    {% endtranschoice %}

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

    .. code-block:: twig

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

Extracting Translation Contents and Updating Catalogs Automatically
-------------------------------------------------------------------

The most time-consuming tasks when translating an application is to extract all
the template contents to be translated and to keep all the translation files in
sync. Symfony includes a command called ``translation:update`` that helps you
with these tasks:

.. code-block:: terminal

    # updates the French translation file with the missing strings found in templates/
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

* the ``src/Resources/<bundle name>/translations/`` directory;

* the ``Resources/translations/`` directory inside of any bundle.

.. deprecated:: 4.2

    Using the ``src/Resources/<bundle name>/translations/`` directory to store
    translations was deprecated in Symfony 4.2. Use instead the directory
    defined in the ``default_path`` option (which is ``translations/`` by default).

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in any of the top two directories.

The override mechanism works at a key level: only the overridden keys need
to be listed in a higher priority message file. When a key is not found
in a message file, the translator will automatically fall back to the lower
priority message files.

The filename of the translation files is also important: each message file
must be named according to the following path: ``domain.locale.loader``:

* **domain**: An optional way to organize messages into groups (e.g. ``admin``,
  ``navigation`` or the default ``messages``) - see :ref:`using-message-domains`;

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony should load and parse the file (e.g. ``xlf``,
  ``php``, ``yaml``, etc).

The loader can be the name of any registered loader. By default, Symfony
provides many loaders, including:

* ``xlf``: XLIFF file;
* ``php``: PHP file;
* ``yaml``: YAML file.

The choice of which loader to use is entirely up to you and is a matter of
taste. The recommended option is to use ``xlf`` for translations.
For more options, see :ref:`component-translator-message-catalogs`.

.. note::

    You can add other directories with the :ref:`paths <reference-translator-paths>`
    option in the configuration:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/translation.yaml
            framework:
                translator:
                    paths:
                        - '%kernel.project_dir%/custom/path/to/translations'

        .. code-block:: xml

            <!-- config/packages/translation.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
            >

                <framework:config>
                    <framework:translator>
                        <framework:path>%kernel.project_dir%/custom/path/to/translations</framework:path>
                    </framework:translator>
                </framework:config>
            </container>

        .. code-block:: php

            // config/packages/translation.php
            $container->loadFromExtension('framework', [
                'translator' => [
                    'paths' => [
                        '%kernel.project_dir%/custom/path/to/translations',
                    ],
                ],
            ]);

.. note::

    You can also store translations in a database, or any other storage by
    providing a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface.
    See the :ref:`dic-tags-translation-loader` tag for more information.

.. caution::

    Each time you create a *new* translation resource (or install a bundle
    that includes a translation resource), be sure to clear your cache so
    that Symfony can discover the new translation resources:

    .. code-block:: terminal

        $ php bin/console cache:clear

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

    When Symfony can't find a translation in the given locale, it will
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
no longer needs to be a painful process and boils down to these steps:

* Abstract messages in your application by wrapping each in either the
  :method:`Symfony\\Component\\Translation\\Translator::trans` or
  :method:`Symfony\\Component\\Translation\\Translator::transChoice` methods
  (learn about this in :doc:`/components/translation/usage`);

* Translate each message into multiple locales by creating translation message
  files. Symfony discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set on the user's session.

Learn more
----------

.. toctree::
    :maxdepth: 1

    translation/locale
    translation/debug
    translation/lint

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: http://atlantic18.github.io/DoctrineExtensions/doc/translatable.html
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
.. _`TranslationBundle`: https://github.com/php-translation/symfony-bundle
