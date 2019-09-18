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
    echo 'Hello World';

    // text can be translated into the end-user's language or
    // default to English
    echo $translator->trans('Hello World');

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

Installation
------------

First, run this command to install the translator before using it:

.. code-block:: terminal

    $ composer require symfony/translation

.. _translation-configuration:

Configuration
-------------

The previous command creates an initial config file where you can define the
default locale of the application and the directory where the translation files
are located:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            default_locale: 'en'
            translator:
                default_path: '%kernel.project_dir%/translations'

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
                    <framework:default-path>'%kernel.project_dir%/translations'</framework:default-path>
                    <!-- ... -->
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['default_path' => '%kernel.project_dir%/translations'],
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
            'Symfony is great' => "J'aime Symfony",
        ];

For information on where these files should be located, see
:ref:`translation-resource-locations`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony``. You can also translate
the message inside your :ref:`templates <translation-in-templates>`.

The Translation Process
~~~~~~~~~~~~~~~~~~~~~~~

To actually translate the message, Symfony uses the following process when
using the ``trans()`` method:

* The ``locale`` of the current user, which is stored on the request is determined;

* A catalog (e.g. big collection) of translated messages is loaded from translation
  resources defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
  :ref:`fallback locale <translation-fallback>` are also loaded and
  added to the catalog if they don't already exist. The end result is a large
  "dictionary" of translations. This catalog is cached in production to
  minimize performance impact.

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

.. _message-placeholders:
.. _pluralization:

Message Format
--------------

Sometimes, a message containing a variable needs to be translated::

    // ...
    $translated = $translator->trans('Hello '.$name);

However, creating a translation for this string is impossible since the
translator will try to look up the message including the variable portions
(e.g. *"Hello Ryan"* or *"Hello Fabien"*).

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

To manage these situations, Symfony follows the `ICU MessageFormat`_ syntax by
using PHP's :phpclass:`MessageFormatter` class. Read more about this in
:doc:`/translation/message_format`.

.. _translation-in-templates:

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony provides native
support for both Twig and PHP templates:

.. code-block:: html+twig

    <h1>{% trans %}Symfony is great!{% endtrans %}</h1>

Read :doc:`/translation/templates` for more information about the Twig tags and
filters for translation.

Extracting Translation Contents and Updating Catalogs Automatically
-------------------------------------------------------------------

The most time-consuming tasks when translating an application is to extract all
the template contents to be translated and to keep all the translation files in
sync. Symfony includes a command called ``translation:update`` that helps you
with these tasks:

.. code-block:: terminal

    # shows all the messages that should be translated for the French language
    $ php bin/console translation:update --dump-messages fr

    # updates the French translation files with the missing strings for that locale
    $ php bin/console translation:update --force fr

    # check out the command help to see its options (prefix, output format, domain, sorting, etc.)
    $ php bin/console translation:update --help

The ``translation:update`` command looks for missing translations in:

* Templates stored in the ``templates/`` directory (or any other directory
  defined in the :ref:`twig.default_path <config-twig-default-path>` and
  :ref:`twig.paths <config-twig-paths>` config options);
* Any PHP file/class that injects or :doc:`autowires </service_container/autowiring>`
  the ``translator`` service and makes calls to the ``trans()`` function.

.. _translation-resource-locations:

Translation Resource/File Names and Locations
---------------------------------------------

Symfony looks for message files (i.e. translations) in the following default locations:

* the ``translations/`` directory (at the root of the project);
* the ``Resources/translations/`` directory inside of any bundle.

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in the first directory.

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

Handling the User's Locale
--------------------------

Translating happens based on the user's locale. Read :doc:`/translation/locale`
to learn more about how to handle it.

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

#. If the translation still isn't found, Symfony uses the ``fallbacks`` option,
   which can be configured as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
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

            <framework:config>
                <framework:translator>
                    <framework:fallback>en</framework:fallback>
                    <!-- ... -->
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        $container->loadFromExtension('framework', [
            'translator' => ['fallbacks' => ['en']],
            // ...
        ]);

.. note::

    When Symfony can't find a translation in the given locale, it will
    add the missing translation to the log file. For details,
    see :ref:`reference-framework-translator-logging`.

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

* Abstract messages in your application by wrapping each in the
  :method:`Symfony\\Component\\Translation\\Translator::trans` method;

* Translate each message into multiple locales by creating translation message
  files. Symfony discovers and processes each file because its name follows
  a specific convention;

* Manage the user's locale, which is stored on the request, but can also
  be set on the user's session.

Learn more
----------

.. toctree::
    :maxdepth: 1

    translation/message_format
    translation/templates
    translation/locale
    translation/debug
    translation/lint

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ICU MessageFormat`: http://userguide.icu-project.org/formatparse/messages
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: http://atlantic18.github.io/DoctrineExtensions/doc/translatable.html
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
