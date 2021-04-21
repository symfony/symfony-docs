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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework
                ->defaultLocale('en')
                ->translator()
                    ->defaultPath('%kernel.project_dir%/translations')
            ;
        };

The locale used in translations is the one stored on the request. This is
typically set via a ``_locale`` attribute on your routes (see :ref:`translation-locale-url`).

.. _translation-basic:

Basic Translation
-----------------

Translation of text is done through the  ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). To translate a block
of text (called a *message*), use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method. Suppose,
for example, that you're translating a static message from inside a controller::

    // ...
    use Symfony\Contracts\Translation\TranslatorInterface;

    public function index(TranslatorInterface $translator)
    {
        $translated = $translator->trans('Symfony is great');

        // ...
    }

.. _translation-resources:

When this code is run, Symfony will attempt to translate the message
"Symfony is great" based on the ``locale`` of the user. For this to work,
you need to tell Symfony how to translate the message via a "translation
resource", which is usually a file that contains a collection of translations
for a given locale. This "dictionary" of translations can be created in several
different formats:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.fr.yaml
        Symfony is great: J'aime Symfony

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
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

        // translations/messages.fr.php
        return [
            'Symfony is great' => "J'aime Symfony",
        ];

For information on where these files should be located, see
:ref:`translation-resource-locations`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``J'aime Symfony``. You can also translate
the message inside your :ref:`templates <translation-in-templates>`.

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

To actually translate the message, Symfony uses the following process when
using the ``trans()`` method:

#. The ``locale`` of the current user, which is stored on the request is determined;

#. A catalog (e.g. big collection) of translated messages is loaded from translation
   resources defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
   :ref:`fallback locale <translation-fallback>` are also loaded and
   added to the catalog if they don't already exist. The end result is a large
   "dictionary" of translations. This catalog is cached in production to
   minimize performance impact.

#. If the message is located in the catalog, the translation is returned. If
   not, the translator returns the original message.

.. tip::

    When translating strings that are not in the default domain (``messages``),
    you must specify the domain as the third argument of ``trans()``::

        $translator->trans('Symfony is great', [], 'admin');

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

.. _translatable-objects:

Translatable Objects
--------------------

.. versionadded:: 5.2

    Translatable objects were introduced in Symfony 5.2.

Sometimes translating contents in templates is cumbersome because you need the
original message, the translation parameters and the translation domain for
each content. Making the translation in the controller or services simplifies
your templates, but requires injecting the translator service in different
parts of your application and mocking it in your tests.

Instead of translating a string at the time of creation, you can use a
"translatable object", which is an instance of the
:class:`Symfony\\Component\\Translation\\TranslatableMessage` class. This object stores
all the information needed to fully translate its contents when needed::

    use Symfony\Component\Translation\TranslatableMessage;

    // the first argument is required and it's the original message
    $message = new TranslatableMessage('Symfony is great!');
    // the optional second argument defines the translation parameters and
    // the optional third argument is the translation domain
    $status = new TranslatableMessage('order.status', ['%status%' => $order->getStatus()], 'store');

Templates are now much simpler because you can pass translatable objects to the
``trans`` filter:

.. code-block:: html+twig

    <h1>{{ message|trans }}</h1>
    <p>{{ status|trans }}</p>

.. tip::

    There's also a :ref:`function called t() <reference-twig-function-t>`,
    available both in Twig and PHP, as a shortcut to create translatable objects.

.. _translation-in-templates:

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony provides native
support for both Twig and PHP templates.

.. _translation-tags:

Using Twig Tags
~~~~~~~~~~~~~~~

Symfony provides a specialized Twig tag ``trans`` to help with message
translation of *static blocks of text*:

.. code-block:: twig

    {% trans %}Hello %name%{% endtrans %}

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

.. _translation-filters:

Using Twig Filters
~~~~~~~~~~~~~~~~~~

The ``trans`` filter can be used to translate *variable texts* and complex expressions:

.. code-block:: twig

    {{ message|trans }}

    {{ message|trans({'%name%': 'Fabien'}, 'app') }}

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
  the ``translator`` service and makes calls to the ``trans()`` method.
* Any PHP file/class stored in the ``src/`` directory that creates
  :ref:`translatable-objects` using the constructor or the ``t()`` method or calls
  the ``trans()`` method.

.. versionadded:: 5.3

    Support for extracting Translatable objects has been introduced in
    Symfony 5.3.

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

* **domain**: Domains are a way to organize messages into groups. Unless
  parts of the application are explicitly separated from each other, it is
  recommended to only use the default ``messages`` domain (e.g.
  ``messages.en.yaml``).

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony should load and parse the file (e.g. ``xlf``,
  ``php``, ``yaml``, etc).

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
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <framework:config>
                    <framework:translator>
                        <framework:path>%kernel.project_dir%/custom/path/to/translations</framework:path>
                    </framework:translator>
                </framework:config>
            </container>

        .. code-block:: php

            // config/packages/translation.php
            use Symfony\Config\FrameworkConfig;

            return static function (FrameworkConfig $framework) {
                $framework->translator()
                    ->paths(['%kernel.project_dir%/custom/path/to/translations'])
                ;
            };

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

Imagine that the user's locale is ``es_AR`` and that you're translating the
key ``Symfony is great``. To find the Spanish translation, Symfony actually
checks translation resources for several locales:

#. First, Symfony looks for the translation in a ``es_AR`` (Argentinean
   Spanish) translation resource (e.g. ``messages.es_AR.yaml``);

#. If it wasn't found, Symfony looks for the translation in the
   parent locale, which is automatically defined only for some locales. In
   this example, the parent locale is ``es_419`` (Latin American Spanish);

#. If it wasn't found, Symfony looks for the translation in a ``es``
   (Spanish) translation resource (e.g. ``messages.es.yaml``);

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
           use Symfony\Config\FrameworkConfig;

            return static function (FrameworkConfig $framework) {
                // ...
                $framework->translator()
                    ->fallbacks(['en'])
                ;
            };

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
    translation/locale
    translation/debug
    translation/lint
    translation/xliff

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ICU MessageFormat`: https://unicode-org.github.io/icu/userguide/format_parse/messages/
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
