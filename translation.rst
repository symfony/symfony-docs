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

.. _translation-domain:

Translations can be organized into groups, called **domains**. By default, all
messages use the default ``messages`` domain::

    echo $translator->trans('Hello World', domain: 'messages');

The translation process has several steps:

#. :ref:`Enable and configure <translation-configuration>` Symfony's
   translation service;

#. Abstract strings (i.e. "messages") by :ref:`wrapping them in calls
   <translation-basic>` to the ``Translator``;

#. :ref:`Create translation resources/files <translation-resources>`
   for each supported locale that translate each message in the application;

#. Determine, :ref:`set and manage the user's locale <translation-locale>`
   for the request and optionally
   :ref:`on the user's entire session <locale-sticky-session>`.

Installation
------------

First, run this command to install the translator before using it:

.. code-block:: terminal

    $ composer require symfony/translation

Symfony includes several internationalization polyfills (``symfony/polyfill-intl-icu``,
``symfony/polyfill-intl-messageformatter``, etc.) that allow you to use translation
features even without the `PHP intl extension`_. However, these polyfills only
support English translations, so you must install the PHP ``intl`` extension
when translating into other languages.

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

    .. code-block:: php

        // config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'default_locale' => 'en',
                'translator' => [
                    'default_path' => '%kernel.project_dir%/translations',
                ],
            ],
        ]);

.. tip::

    You can also define the :ref:`enabled_locales option <reference-translator-enabled-locales>`
    to restrict the locales that your application is available in.

.. _translation-basic:

Basic Translation
-----------------

Translation of text is done through the ``translator`` service
(:class:`Symfony\\Component\\Translation\\Translator`). To translate a block of
text (called a *message*), use the
:method:`Symfony\\Component\\Translation\\Translator::trans` method. Suppose,
for example, that you're translating a static message from inside a
controller::

    // ...
    use Symfony\Contracts\Translation\TranslatorInterface;

    public function index(TranslatorInterface $translator): Response
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
        Symfony is great: Symfony est génial

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="symfony_is_great">
                        <source>Symfony is great</source>
                        <target>Symfony est génial</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.fr.php
        return [
            'Symfony is great' => 'Symfony est génial',
        ];

You can find more information on where these files
:ref:`should be located <translation-resource-locations>`.

Now, if the language of the user's locale is French (e.g. ``fr_FR`` or ``fr_BE``),
the message will be translated into ``Symfony est génial``. You can also translate
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

#. The ``locale`` of the current user, which is stored on the request is
   determined; this is typically set via a ``_locale`` :ref:`attribute on
   your routes <translation-locale-url>`;

#. A catalog of translated messages is loaded from translation resources
   defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
   :ref:`fallback locale <translation-fallback>` and the
   :ref:`enabled locales <reference-translator-enabled-locales>` are also
   loaded and added to the catalog if they don't already exist. The end result
   is a large "dictionary" of translations.

#. If the message is located in the catalog, the translation is returned. If
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

Instead, you can replace the variable parts with **placeholders** wrapped in
``%`` characters:

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        say_hello: 'Hello %name%!'

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="urn:oasis:names:tc:xliff:document:1.2
            https://docs.oasis-open.org/xliff/v1.2/os/xliff-core-1.2-strict.xsd">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="say_hello">
                        <source>say_hello</source>
                        <target>Hello %name%!</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.en.php
        return [
            'say_hello' => 'Hello %name%!',
        ];

Then, pass the placeholder values as the second argument of the ``trans()`` method::

    // ...
    $translated = $translator->trans('say_hello', ['%name%' => 'Fabien']);
    // $translated = 'Hello Fabien!'

Symfony replaces the placeholders with the given values using PHP's
:phpfunction:`strtr` function. The ``%`` wrapping is a convention to make
placeholders easy to spot, but it's not required. You can use any wrapper
(e.g. ``#name#`` or ``{name}``), as long as the key in the parameters array
matches the placeholder in the message exactly. This basic mechanism works in
all translation formats and covers most use cases.

When your translations need to handle more complex scenarios, basic
placeholders are not enough. For example, you might need to adjust a message:

* based on a count: *"There is one apple"* vs *"There are 5 apples"*
* based on gender: *"He accepted the invitation"* vs *"She accepted the invitation"*
  vs *"They accepted the invitation"*
* based on the user's locale: e.g. different date formats, such as
  *"Published on January 25, 2026"* (English) vs *"Publicado el 25 de Enero de 2026"* (Spanish)

Symfony handles all these cases through the :doc:`ICU MessageFormat </reference/formats/message_format>`
syntax, using PHP's :phpclass:`MessageFormatter` class. ICU placeholders use ``{name}``
instead of ``%name%`` and require the ``+intl-icu`` suffix in the translation
filename (e.g. ``messages+intl-icu.en.yaml``). Read more about this in
:doc:`/reference/formats/message_format`.

.. _translatable-objects:

Translatable Objects
--------------------

In many applications, translatable text does not live only in Twig templates
and controllers. Enums, services, value objects and form types often need to
produce messages that will be translated later. Translating those messages at
creation time forces you to inject the ``translator`` service everywhere and to
mock it in every test.

A **translatable object** solves this by storing all the information needed for
a future translation (the message ID, parameters and domain) without actually
translating anything. When the object eventually reaches a Twig template or any
other translation-aware layer, it is translated automatically.

Symfony ships with :class:`Symfony\\Component\\Translation\\TranslatableMessage`,
which implements :class:`Symfony\\Contracts\\Translation\\TranslatableInterface`.
You can use it directly or create your own implementations of the interface.

Basic Usage
~~~~~~~~~~~

Create a ``TranslatableMessage`` anywhere in your code. The translation
happens later, when the object is rendered::

    use Symfony\Component\Translation\TranslatableMessage;

    // a translatable message using the default domain ("messages")
    $message = new TranslatableMessage('notification.welcome');

    // with parameters and a custom domain
    $status = new TranslatableMessage(
        'order.status',
        ['%status%' => $order->getStatus()],
        'store'
    );

In Twig, pass translatable objects to the ``trans`` filter just like regular strings:

.. code-block:: html+twig

    <h1>{{ message|trans }}</h1>
    <p>{{ status|trans }}</p>

.. tip::

    Use the :ref:`t() shortcut function <reference-twig-function-t>` to create
    translatable objects with less boilerplate, both in Twig and PHP::

        use function Symfony\Component\Translation\t;

        $message = t('notification.welcome');
        $status  = t('order.status', ['%status%' => $order->getStatus()], 'store');

.. tip::

    The translation parameters of a ``TranslatableMessage`` can themselves be
    :class:`Symfony\\Component\\Translation\\TranslatableMessage` instances.

Translatable Objects In Practice
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Enums are a common source of user-facing text. Consider a ``UserRole`` enum
that needs to display a translated label in the UI::

    enum UserRole: string
    {
        case User  = 'ROLE_USER';
        case Admin = 'ROLE_ADMIN';
    }

A first approach is to return plain strings or translation keys from a method.
However, this has two significant drawbacks:

#. You cannot attach translation parameters (e.g. for pluralization) or specify
   a translation domain.
#. The :ref:`translation:extract command <extracting-translation-contents>`
   cannot detect the keys, so it will not update your translation files automatically
   and the ``--clean`` option will wrongly flag those keys as unused.

Using ``TranslatableMessage`` fixes both problems::

    use Symfony\Component\Translation\TranslatableMessage;

    enum UserRole: string
    {
        case User  = 'ROLE_USER';
        case Admin = 'ROLE_ADMIN';

        public function label(): TranslatableMessage
        {
            return match ($this) {
                self::User  => new TranslatableMessage('user_role.user'),
                self::Admin => new TranslatableMessage('user_role.admin'),
            };
        }
    }

In Twig, render the label with the ``trans`` filter:

.. code-block:: html+twig

    <span>{{ role.label|trans }}</span>

The ``translation:extract`` command will detect these ``TranslatableMessage``
constructors and keep your translation catalogs up to date.

Custom TranslatableInterface Implementation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need more control, implement
:class:`Symfony\\Contracts\\Translation\\TranslatableInterface` directly on
any class. The interface requires a single ``trans()`` method::

    use Symfony\Contracts\Translation\TranslatableInterface;
    use Symfony\Contracts\Translation\TranslatorInterface;

    enum UserRole: string implements TranslatableInterface
    {
        case User  = 'ROLE_USER';
        case Admin = 'ROLE_ADMIN';

        public function trans(TranslatorInterface $translator, ?string $locale = null): string
        {
            return $translator->trans('user_role.'.$this->name, locale: $locale);
        }
    }

Any object that implements ``TranslatableInterface`` can be passed to the
``trans`` Twig filter and will be translated automatically. This approach is
useful when translation logic is more complex than a static message ID, for
example when the message depends on runtime conditions.

Non-Translatable Messages
~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, you may want to explicitly prevent a message from being
translated. You can ensure this behavior by using the
:class:`Symfony\\Component\\Translation\\StaticMessage` class::

    use Symfony\Component\Translation\StaticMessage;

    $message = new StaticMessage('This message will never be translated.');

This can be useful when rendering user-defined content or other strings
that must remain exactly as given.

.. _translation-in-templates:

Translations in Templates
-------------------------

Most of the time, translation occurs in templates. Symfony provides native
support for both Twig and PHP templates.

.. _translation-filters:

Using Twig Filters
~~~~~~~~~~~~~~~~~~

The ``trans`` filter can be used to translate *variable texts* and complex expressions:

.. code-block:: twig

    {{ message|trans }}

    {{ message|trans({'%name%': 'Fabien'}, 'app') }}

.. tip::

    You can set the translation domain for an entire Twig template with a single tag:

    .. code-block:: twig

       {% trans_default_domain 'app' %}

    Note that this only influences the current template, not any "included"
    template (in order to avoid side effects).

By default, the translated messages are output escaped; apply the ``raw``
filter after the translation filter to avoid the automatic escaping:

.. code-block:: html+twig

    {% set message = '<h3>foo</h3>' %}

    {# strings and variables translated via a filter are escaped by default #}
    {{ message|trans|raw }}
    {{ '<h3>bar</h3>'|trans|raw }}

.. _translation-tags:

Using Twig Tags
~~~~~~~~~~~~~~~

Symfony provides a specialized Twig tag ``trans`` to help with message
translation of *static blocks of text*:

.. code-block:: twig

    {% trans %}Hello %name%{% endtrans %}

.. warning::

    The ``%var%`` notation of placeholders is required when translating in
    Twig templates using the tag.

.. tip::

    If you need to use the percent character (``%``) in a string, escape it by
    doubling it: ``{% trans %}Percent: %percent%%%{% endtrans %}``

You can also specify the message domain and pass some additional variables:

.. code-block:: twig

    {% trans with {'%name%': 'Fabien'} from 'app' %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from 'app' into 'fr' %}Hello %name%{% endtrans %}

.. warning::

    Using the translation tag has the same effect as the filter, but with one
    major difference: automatic output escaping is **not** applied to translations
    using a tag.

Global Translation Parameters
-----------------------------

If the content of a translation parameter is repeated across multiple
translation messages (e.g. a company name, or a version number), you can define
it as a global translation parameter. This helps you avoid repeating the same
values manually in each message.

You can configure these global parameters in the ``translator.globals`` option
of your main configuration file using either ``%...%`` or ``{...}`` syntax:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translator.yaml
        translator:
            # ...
            globals:
                # when using the '%' wrapping characters, you must escape them
                '%%app_name%%': 'My application'
                '{app_version}': '1.2.3'
                '{url}': { message: 'url', parameters: { scheme: 'https://' }, domain: 'global' }

    .. code-block:: php

        // config/packages/translator.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                // ...
                'translator' => [
                    'globals' => [
                        // when using the '%' wrapping characters, you must escape them
                        '%%app_name%%' => 'My application',
                        '{app_version}' => '1.2.3',
                        '{url}' => ['message' => 'url', 'parameters' => ['scheme' => 'https://']],
                    ],
                ],
            ],
        ]);

Once defined, you can use these parameters in translation messages anywhere in
your application:

.. code-block:: twig

    {{ 'Application version: {app_version}'|trans }}
    {# output: "Application version: 1.2.3" #}

    {# parameters passed to the message override global parameters #}
    {{ 'Package version: {app_version}'|trans({'{app_version}': '2.3.4'}) }}
    # Displays "Package version: 2.3.4"

Forcing the Translator Locale
-----------------------------

When translating a message, the translator uses the specified locale or the
``fallback`` locale if necessary. You can also manually specify the locale to
use for translation::

    $translator->trans('Symfony is great', locale: 'fr_FR');

.. _extracting-translation-contents:

Extracting Translation Contents and Updating Catalogs Automatically
-------------------------------------------------------------------

The most time-consuming task when translating an application is to extract all
the template contents to be translated and to keep all the translation files in
sync. Symfony includes a command called ``translation:extract`` that helps you
with these tasks:

.. code-block:: terminal

    # shows all the messages that should be translated for the French language
    $ php bin/console translation:extract --dump-messages fr

    # updates the French translation files with the missing strings for that locale
    $ php bin/console translation:extract --force fr

    # check out the command help to see its options (prefix, output format, domain, sorting, etc.)
    $ php bin/console translation:extract --help

The ``translation:extract`` command looks for missing translations in:

* Templates stored in the ``templates/`` directory (or any other directory
  defined in the :ref:`twig.default_path <config-twig-default-path>` and
  :ref:`twig.paths <config-twig-paths>` config options);
* Any PHP file/class that injects or :doc:`autowires </service_container/autowiring>`
  the ``translator`` service and makes calls to the ``trans()`` method;
* Any PHP file/class stored in the ``src/`` directory that creates
  :ref:`translatable objects <translatable-objects>` using the constructor or
  the ``t()`` method or calls the ``trans()`` method;
* Any PHP file/class stored in the ``src/`` directory that uses
  :ref:`Constraints Attributes <validation-constraints>`  with ``*message`` named argument(s).

.. tip::

    Install the ``nikic/php-parser`` package in your project to improve the
    results of the ``translation:extract`` command. This package enables an
    `AST`_ parser that can find many more translatable items:

    .. code-block:: terminal

        $ composer require nikic/php-parser

By default, when the ``translation:extract`` command creates new entries in the
translation file, it uses the same content as both the source and the pending
translation. The only difference is that the pending translation is prefixed by
``__``. You can customize this prefix using the ``--prefix`` option:

.. code-block:: terminal

    $ php bin/console translation:extract --force --prefix="NEW_" fr

Alternatively, you can use the ``--no-fill`` option to leave the pending translation
completely empty when creating new entries in the translation catalog. This is
particularly useful when using external translation tools, as it makes it easier
to spot untranslated strings:

.. code-block:: terminal

    # when using the --no-fill option, the --prefix option is ignored
    $ php bin/console translation:extract --force --no-fill fr

.. _translation-resource-locations:

Translation Resource/File Names and Locations
---------------------------------------------

Symfony looks for message files (i.e. translations) in the following default locations:

* the ``translations/`` directory (at the root of the project);
* the ``translations/`` directory inside of any bundle (and also their
  ``Resources/translations/`` directory, which is no longer recommended for bundles).

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in the first directory. Bundles are
processed in the order in which they are listed in the ``config/bundles.php`` file,
so bundles appearing earlier have higher priority.

The override mechanism works at a key level: only the overridden keys need
to be listed in a higher priority message file. When a key is not found
in a message file, the translator will automatically fall back to the lower
priority message files.

The filename of the translation files is also important: each message file
must be named according to the following path: ``domain.locale.loader``:

* **domain**: The translation domain;

* **locale**: The locale that the translations are for (e.g. ``en_GB``, ``en``, etc);

* **loader**: How Symfony should load and parse the file (e.g. ``xlf``,
  ``php``, ``yaml``, etc).

The loader can be the name of any registered loader. By default, Symfony
provides many loaders which are selected based on the following file extensions:

* ``.yaml``: YAML file (you can also use the ``.yml`` file extension);
* ``.xlf``: XLIFF file (you can also use the ``.xliff`` file extension);
* ``.php``: a PHP file that returns an array with the translations;
* ``.csv``: CSV file;
* ``.json``: JSON file;
* ``.ini``: INI file;
* ``.dat``, ``.res``: `ICU resource bundle`_;
* ``.mo``: `Machine object format`_;
* ``.po``: `Portable object format`_;
* ``.qt``: `QT Translations TS XML`_ file;

The choice of which loader to use is entirely up to you and is a matter of
taste. The recommended option is to use YAML for simple projects and use XLIFF
if you're generating translations with specialized programs or teams.

.. warning::

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

        .. code-block:: php

            // config/packages/translation.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'framework' => [
                    'translator' => [
                        'paths' => ['%kernel.project_dir%/custom/path/to/translations'],
                    ],
                ],
            ]);

Translations of Doctrine Entities
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Unlike the contents of templates, it's not practical to translate the contents
stored in Doctrine Entities using translation catalogs. Instead, use the
Doctrine `Translatable Extension`_.

Custom Translation Resources
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your translations use a format not supported by Symfony or you store them
in a special way (e.g. not using files or Doctrine entities), you need to provide
a custom class implementing the :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface`
interface. See the :ref:`dic-tags-translation-loader` tag for more information.

.. _translation-providers:

Translation Providers
---------------------

When using external translators to translate your application, you must send
them the new contents to translate frequently and merge the results back in the
application.

Instead of doing this manually, Symfony provides integration with several
third-party translation services. You can upload and download (called "push"
and "pull") translations to/from these services and merge the results
automatically in the application.

Installing and Configuring a Third Party Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before pushing/pulling translations to a third-party provider, you must install
the package that provides integration with that provider:

======================  ===========================================================
Provider                Install with
======================  ===========================================================
`Crowdin`_              ``composer require symfony/crowdin-translation-provider``
`Loco (localise.biz)`_  ``composer require symfony/loco-translation-provider``
`Lokalise`_             ``composer require symfony/lokalise-translation-provider``
`Phrase`_                ``composer require symfony/phrase-translation-provider``
======================  ===========================================================

Each library includes a :ref:`Symfony Flex recipe <symfony-flex>` that will add
a configuration example to your ``.env`` file. For example, suppose you want to
use Loco. First, install it:

.. code-block:: terminal

    $ composer require symfony/loco-translation-provider

You'll now have a new line in your ``.env`` file that you can uncomment:

.. code-block:: env

    # .env
    LOCO_DSN=loco://API_KEY@default

The ``LOCO_DSN`` isn't a *real* address: it's a convenient format that offloads
most of the configuration work to Symfony. The ``loco`` scheme activates the
Loco provider that you installed, which knows all about how to push and
pull translations via Loco. The *only* part you need to change is the
``API_KEY`` placeholder.

This table shows the full list of available DSN formats for each provider:

======================  ==============================================================
Provider                DSN
======================  ==============================================================
`Crowdin`_              ``crowdin://PROJECT_ID:API_TOKEN@ORGANIZATION_DOMAIN.default``
`Loco (localise.biz)`_  ``loco://API_KEY@default``
`Lokalise`_             ``lokalise://PROJECT_ID:API_KEY@default``
`Phrase`_               ``phrase://PROJECT_ID:API_TOKEN@default?userAgent=myProject``
======================  ==============================================================

To enable a translation provider, customize the DSN in your ``.env`` file and
configure the ``providers`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            translator:
                providers:
                    loco:
                        dsn: '%env(LOCO_DSN)%'
                        domains: ['messages']
                        locales: ['en', 'fr']

    .. code-block:: php

        # config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'translator' => [
                    'providers' => [
                        'loco' => [
                            'dsn' => env('LOCO_DSN'),
                            'domains' => ['messages'],
                            'locales' => ['en', 'fr'],
                        ],
                    ],
                ],
            ],
        ]);

.. important::

    If you use Phrase as a provider you must configure a user agent in your dsn. See
    `Identification via User-Agent`_ for reasoning and some examples.

    Also, the locale *names* in Phrase must follow RFC 4646 (e.g. pt-BR rather than pt_BR).
    Not doing so will result in Phrase creating a new locale for the imported keys.

.. tip::

    If you use Crowdin as a provider and some of your locales are different from
    the `Crowdin Language Codes`_, you have to set the `Custom Language Codes`_ in the Crowdin project
    for each of your locales, in order to override the default value. You need to select the
    "locale" placeholder and specify the custom code in the "Custom Code" field.

.. tip::

    If you use Lokalise as a provider and a locale format following the `ISO
    639-1`_ (e.g. "en" or "fr"), you have to set the `Custom Language Name setting`_
    in Lokalise for each of your locales, in order to override the
    default value (which follow the `ISO 639-1`_ succeeded by a sub-code in
    capital letters that specifies the national variety (e.g. "GB" or "US"
    according to `ISO 3166-1 alpha-2`_)).

.. tip::

    The Phrase provider uses Phrase's tag feature to map translations to Symfony's translation
    domains. If you need some assistance with organizing your tags in Phrase, you might want
    to consider the `Phrase Tag Bundle`_ which provides some commands helping you with that.

Pushing and Pulling Translations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

After configuring the credentials to access the translation provider, you can
now use the following commands to push (upload) and pull (download) translations:

.. code-block:: terminal

    # push all local translations to the Loco provider for the locales and domains
    # configured in config/packages/translation.yaml file.
    # it will update existing translations already on the provider.
    $ php bin/console translation:push loco --force

    # push new local translations to the Loco provider for the French locale
    # and the validators domain.
    # it will **not** update existing translations already on the provider.
    $ php bin/console translation:push loco --locales fr --domains validators

    # push new local translations and delete provider's translations that not
    # exists anymore in local files for the French locale and the validators domain.
    # it will **not** update existing translations already on the provider.
    $ php bin/console translation:push loco --delete-missing --locales fr --domains validators

    # check out the command help to see its options (format, domains, locales, etc.)
    $ php bin/console translation:push --help

.. code-block:: terminal

    # pull all provider's translations to local files for the locales and domains
    # configured in config/packages/translation.yaml file.
    # it will overwrite completely your local files.
    $ php bin/console translation:pull loco --force

    # pull new translations from the Loco provider to local files for the French
    # locale and the validators domain.
    # it will **not** overwrite your local files, only add new translations.
    $ php bin/console translation:pull loco --locales fr --domains validators

    # check out the command help to see its options (format, domains, locales, intl-icu, etc.)
    $ php bin/console translation:pull --help

    # the "--as-tree" option will write YAML messages as a tree-like structure instead
    # of flat keys
    $ php bin/console translation:pull loco --force --as-tree

Creating Custom Providers
~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to using Symfony's built-in translation providers, you can create
your own providers. To do so, you need to create two classes:

#. The first class must implement :class:`Symfony\\Component\\Translation\\Provider\\ProviderInterface`;
#. The second class needs to be a factory which will create instances of the first class. It must implement
:class:`Symfony\\Component\\Translation\\Provider\\ProviderFactoryInterface` (you can extend :class:`Symfony\\Component\\Translation\\Provider\\AbstractProviderFactory` to simplify its creation).

After creating these two classes, you need to register your factory as a service
and tag it with :ref:`translation.provider_factory <reference-dic-tags-translation-provider-factory>`.

.. _translation-locale:

Handling the User's Locale
--------------------------

Translating happens based on the user's locale. The locale of the current user
is stored in the request and is accessible via the ``Request`` object::

    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request): void
    {
        $locale = $request->getLocale();
    }

To set the user's locale, you may want to create a custom event listener so
that it's set before any other parts of the system (i.e. the translator) need
it::

        public function onKernelRequest(RequestEvent $event): void
        {
            $request = $event->getRequest();

            // some logic to determine the $locale
            $request->setLocale($locale);
        }

.. note::

    The custom listener must be called **before** ``LocaleListener``, which
    initializes the locale based on the current request. To do so, set your
    listener priority to a higher value than ``LocaleListener`` priority (which
    you can obtain by running the ``debug:event kernel.request`` command).

Read :ref:`locale-sticky-session` for more information on making the user's
locale "sticky" to their session.

.. note::

    Setting the locale using ``$request->setLocale()`` in the controller is
    too late to affect the translator. Either set the locale via a listener
    (like above), the URL (see next) or call ``setLocale()`` directly on the
    ``translator`` service.

See the :ref:`translation-locale-url` section below about setting the
locale via routing.

.. _translation-locale-url:

The Locale and the URL
~~~~~~~~~~~~~~~~~~~~~~

Since you can store the locale of the user in the session, it may be tempting
to use the same URL to display a resource in different languages based on the
user's locale. For example, ``http://www.example.com/contact`` could show
content in English for one user and French for another user. Unfortunately,
this violates a fundamental rule of the Web: that a particular URL returns the
same resource regardless of the user. To further muddy the problem, which
version of the content would be indexed by search engines?

A better policy is to include the locale in the URL using the
:ref:`special _locale parameter <routing-locale-parameter>`:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ContactController.php
        namespace App\Controller;

        // ...
        class ContactController extends AbstractController
        {
            #[Route(
                path: '/{_locale}/contact',
                name: 'contact',
                requirements: [
                    '_locale' => 'en|fr|de',
                ],
            )]
            public function contact(): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /{_locale}/contact
            controller: App\Controller\ContactController::index
            requirements:
                _locale: en|fr|de

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\ContactController;

        return Routes::config([
            'contact' => [
                'path' => '/{_locale}/contact',
                'controller' => [ContactController::class, 'index'],
                'requirements' => [
                    '_locale' => 'en|fr|de',
                ],
            ],
        ]);

When using the special ``_locale`` parameter in a route, the matched locale
is *automatically set on the Request* and can be retrieved via the
:method:`Symfony\\Component\\HttpFoundation\\Request::getLocale` method. In
other words, if a user visits the URI ``/fr/contact``, the locale ``fr`` will
automatically be set as the locale for the current request.

You can now use the locale to create routes to other translated pages in your
application.

.. tip::

    Define the locale requirement as a :ref:`container parameter <configuration-parameters>`
    to avoid hardcoding its value in all your routes.

.. _translation-default-locale:

Setting a Default Locale
~~~~~~~~~~~~~~~~~~~~~~~~

What if the user's locale hasn't been determined? You can guarantee that a
locale is set on each user's request by defining a ``default_locale`` for
the framework:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            default_locale: en

    .. code-block:: php

        // config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'default_locale' => 'en',
            ],
        ]);

This ``default_locale`` is also relevant for the translator, as shown in the
next section.

Selecting the Language Preferred by the User
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your application supports multiple languages, the first time a user visits your
site it's common to redirect them to the best possible language according to their
preferences. This is achieved with the ``getPreferredLanguage()`` method of the
:ref:`Request object <controller-request-argument>`::

    // get the Request object somehow (e.g. as a controller argument)
    $request = ...
    // pass an array of the locales (their script and region parts are optional) supported
    // by your application and the method returns the best locale for the current user
    $locale = $request->getPreferredLanguage(['pt', 'fr_Latn_CH', 'en_US'] );

Symfony finds the best possible language based on the locales passed as argument
and the value of the ``Accept-Language`` HTTP header. If it can't find a perfect
match between them, Symfony will try to find a partial match based on the language
(e.g. ``fr_CA`` would match ``fr_Latn_CH`` because their language is the same).
If there's no perfect or partial match, this method returns the first locale passed
as argument (that's why the order of the passed locales is important).

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
   which can be configured as follows. When this option is not defined, it
   defaults to the ``default_locale`` setting mentioned in the previous section.

   .. configuration-block::

       .. code-block:: yaml

           # config/packages/translation.yaml
           framework:
               translator:
                   fallbacks: ['en']
                   # ...

       .. code-block:: php

           // config/packages/translation.php
           namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return [
                'framework' => [
                'translator' => [
                    'fallbacks' => ['en'],
                ],
            ],
        ];

.. note::

    When Symfony can't find a translation in the given locale, it will
    add the missing translation to the log file. For details,
    see :ref:`reference-framework-translator-logging`.

Computing Fallback Locales Programmatically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``LocaleFallbackProvider`` class was introduced in Symfony 8.1.

Symfony determines locale fallbacks using several rules, such as the ICU parent
locale chain, `locale subtag`_ shortening, and configured fallback locales.

This logic is encapsulated in the :class:`Symfony\\Component\\Translation\\LocaleFallbackProvider`
class. You can use this class when you need to compute the fallback chain for a
locale outside of the ``Translator``::

    use Symfony\Component\Translation\LocaleFallbackProvider;

    $provider = new LocaleFallbackProvider(['en']);
    $fallbacks = $provider->computeFallbackLocales('es_AR');
    // ['es_419', 'es', 'en']

The class also provides a method to validate locale strings::

    use Symfony\Component\Translation\LocaleFallbackProvider;

    // throws InvalidArgumentException if the locale contains invalid characters
    LocaleFallbackProvider::validateLocale($locale);

.. _locale-switcher:

Switch Locale Programmatically
------------------------------

Sometimes you need to change the application's locale dynamically while running
some code. For example, a console command that renders email templates in
different languages. In such cases, you only need to switch the locale temporarily.

The ``LocaleSwitcher`` class allows you to do that::

    use Symfony\Component\Translation\LocaleSwitcher;

    class SomeService
    {
        public function __construct(
            private LocaleSwitcher $localeSwitcher,
        ) {
        }

        public function someMethod(): void
        {
            $currentLocale = $this->localeSwitcher->getLocale();

            // set the application locale programmatically to 'fr' (French):
            // this affects translation, URL generation, etc.
            $this->localeSwitcher->setLocale('fr');

            // reset the locale to the default one configured via the
            // 'default_locale' option in config/packages/translation.yaml
            $this->localeSwitcher->reset();

            // run some code with a specific locale, temporarily, without
            // changing the locale for the rest of the application
            $this->localeSwitcher->runWithLocale('es', function() {
                // e.g. render templates, send emails, etc. using the 'es' (Spanish) locale
            });

            // optionally, receive the current locale as an argument:
            $this->localeSwitcher->runWithLocale('es', function(string $locale) {

                // here, the $locale argument will be set to 'es'

            });

            // ...
        }
    }

The ``LocaleSwitcher`` class changes the locale of:

* All services tagged with ``kernel.locale_aware``;
* The default locale set via ``\Locale::setDefault()``;
* The ``_locale`` parameter of the ``RequestContext`` service (if available),
  so generated URLs reflect the new locale.

.. note::

    The LocaleSwitcher applies the new locale only for the current request,
    and its effect is lost on subsequent requests, such as after a redirect.

    See :ref:`how to make the locale persist across requests <locale-sticky-session>`.

When using :ref:`autowiring <services-autowire>`, type-hint any controller or
service argument with the :class:`Symfony\\Component\\Translation\\LocaleSwitcher`
class to inject the locale switcher service. Otherwise, configure your services
manually and inject the ``translation.locale_switcher`` service.

.. _translation-debug:

How to Find Missing or Unused Translation Messages
--------------------------------------------------

When you work with many translation messages in different languages, it can be
hard to keep track which translations are missing and which are not used
anymore. The ``debug:translation`` command helps you to find these missing or
unused translation messages templates:

.. code-block:: twig

    {# messages can be found when using the trans filter and tag #}
    {% trans %}Symfony is great{% endtrans %}

    {{ 'Symfony is great'|trans }}

.. warning::

    The extractors can't find messages translated outside templates (like form
    labels or controllers) unless using :ref:`translatable objects
    <translatable-objects>` or calling the ``trans()`` method on a translator.
    Dynamic translations using variables or expressions in templates are not
    detected either:

    .. code-block:: twig

        {# this translation uses a Twig variable, so it won't be detected #}
        {% set message = 'Symfony is great' %}
        {{ message|trans }}

Suppose your application's default_locale is ``fr`` and you have configured
``en`` as the fallback locale (see :ref:`configuration
<translation-configuration>` and :ref:`fallback <translation-fallback>` for
how to configure these). And suppose you've already set up some translations
for the ``fr`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony is great</source>
                        <target>Symfony est génial</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # translations/messages.fr.yaml
        Symfony is great: Symfony est génial

    .. code-block:: php

        // translations/messages.fr.php
        return [
            'Symfony is great' => 'Symfony est génial',
        ];

and for the ``en`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
                        <source>Symfony is great</source>
                        <target>Symfony is great</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # translations/messages.en.yaml
        Symfony is great: Symfony is great

    .. code-block:: php

        // translations/messages.en.php
        return [
            'Symfony is great' => 'Symfony is great',
        ];

To inspect all messages in the ``fr`` locale for the application, run:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
     unused     Symfony is great    Symfony est génial      Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

It shows you a table with the result when translating the message in the ``fr``
locale and the result when the fallback locale ``en`` would be used. On top
of that, it will also show you when the translation is the same as the fallback
translation (this could indicate that the message was not correctly translated).
Furthermore, it indicates that the message ``Symfony is great`` is unused
because it is translated, but you haven't used it anywhere yet.

Now, if you translate the message in one of your templates, you will get this
output:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
                Symfony is great    Symfony est génial      Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

The state is empty which means the message is translated in the ``fr`` locale
and used in one or more templates.

If you delete the message ``Symfony is great`` from your translation file
for the ``fr`` locale and run the command, you will get:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ---------  ------------------  ----------------------  -------------------------------
     State      Id                  Message Preview (fr)    Fallback Message Preview (en)
    ---------  ------------------  ----------------------  -------------------------------
     missing    Symfony is great    Symfony is great        Symfony is great
    ---------  ------------------  ----------------------  -------------------------------

The state indicates the message is missing because it is not translated in
the ``fr`` locale but it is still used in the template. Moreover, the message
in the ``fr`` locale equals the message in the ``en`` locale. This is a
special case because the untranslated message id equals its translation in
the ``en`` locale.

If you copy the content of the translation file in the ``en`` locale to the
translation file in the ``fr`` locale and run the command, you will get:

.. code-block:: terminal

    $ php bin/console debug:translation fr

    ----------  ------------------  ----------------------  -------------------------------
     State       Id                  Message Preview (fr)    Fallback Message Preview (en)
    ----------  ------------------  ----------------------  -------------------------------
     fallback    Symfony is great    Symfony is great        Symfony is great
    ----------  ------------------  ----------------------  -------------------------------

You can see that the translations of the message are identical in the ``fr``
and ``en`` locales which means this message was probably copied from English
to French and maybe you forgot to translate it.

By default, all domains are inspected, but it is possible to specify a single
domain:

.. code-block:: terminal

    $ php bin/console debug:translation en --domain=messages

When the application has a lot of messages, it is useful to display only the
unused or only the missing messages, by using the ``--only-unused`` or
``--only-missing`` options:

.. code-block:: terminal

    $ php bin/console debug:translation en --only-unused
    $ php bin/console debug:translation en --only-missing

Debug Command Exit Codes
~~~~~~~~~~~~~~~~~~~~~~~~

The exit code of the ``debug:translation`` command changes depending on the
status of the translations. Use the following public constants to check it::

    use Symfony\Bundle\FrameworkBundle\Command\TranslationDebugCommand;

    // generic failure (e.g. there are no translations)
    TranslationDebugCommand::EXIT_CODE_GENERAL_ERROR;

    // there are missing translations
    TranslationDebugCommand::EXIT_CODE_MISSING;

    // there are unused translations
    TranslationDebugCommand::EXIT_CODE_UNUSED;

    // some translations are using the fallback translation
    TranslationDebugCommand::EXIT_CODE_FALLBACK;

These constants are defined as "bit masks", so you can combine them as follows::

    if (TranslationDebugCommand::EXIT_CODE_MISSING | TranslationDebugCommand::EXIT_CODE_UNUSED) {
        // ... there are missing and/or unused translations
    }

.. _translation-lint:

How to Find Errors in Translation Files
---------------------------------------

Symfony processes all the application translation files as part of the process
that compiles the application code before executing it. If there's an error in
any translation file, you'll see an error message explaining the problem.

If you prefer, you can also validate the syntax of any YAML and XLIFF
translation file using the ``lint:yaml`` and ``lint:xliff`` commands:

.. code-block:: terminal

    # lint a single file
    $ php bin/console lint:yaml translations/messages.en.yaml
    $ php bin/console lint:xliff translations/messages.en.xlf

    # lint a whole directory
    $ php bin/console lint:yaml translations
    $ php bin/console lint:xliff translations

    # lint multiple files or directories
    $ php bin/console lint:yaml translations path/to/trans
    $ php bin/console lint:xliff translations/messages.en.xlf translations/messages.es.xlf

The linter results can be exported to JSON using the ``--format`` option:

.. code-block:: terminal

    $ php bin/console lint:yaml translations/ --format=json
    $ php bin/console lint:xliff translations/ --format=json

When running these linters inside `GitHub Actions`_, the output is automatically
adapted to the format required by GitHub, but you can force that format too:

.. code-block:: terminal

    $ php bin/console lint:yaml translations/ --format=github
    $ php bin/console lint:xliff translations/ --format=github

.. tip::

    The Yaml component provides a stand-alone ``yaml-lint`` binary allowing
    you to lint YAML files without having to create a console application:

    .. code-block:: terminal

        $ php vendor/bin/yaml-lint translations/

The ``lint:yaml`` and ``lint:xliff`` commands validate the YAML and XML syntax
of the translation files, but not their contents. Use the following command
to check that the translation contents are also correct:

    .. code-block:: terminal

        # checks the contents of all the translation catalogues in all locales
        $ php bin/console lint:translations

        # checks the contents of the translation catalogues for Italian (it) and Japanese (ja) locales
        $ php bin/console lint:translations --locale=it --locale=ja

Testing Translations
--------------------

Symfony provides tools to simplify testing translation-related code and features.

Identity Translator
~~~~~~~~~~~~~~~~~~~

When testing features that use the translation service, you often don't need
to verify the actual translated content. Instead of mocking the
:class:`Symfony\\Contracts\\Translation\\TranslatorInterface`, you can use the
:class:`Symfony\\Component\\Translation\\IdentityTranslator`, which implements
the interface without loading any translation catalogs.

Instead of looking up translations, ``IdentityTranslator`` always returns the
original message after applying parameter substitution and message selection
(e.g. pluralization)::

    use Symfony\Component\Translation\IdentityTranslator;

    $translator = new IdentityTranslator();

    // with keyword keys, the key itself is returned
    $translator->trans('app.greeting');
    // => "app.greeting"
    $translator->trans('app.greeting', ['%name%' => 'Fabien']);
    // => "app.greeting"

    // when using real messages as keys, parameters are replaced in the key
    $translator->trans('Hello %name%!', ['%name%' => 'Fabien']);
    // => "Hello Fabien!"

    // message selection (including pluralization) still applies
    $translator->trans('{0} No results|one result|%count% results', ['%count%' => 3]);
    // => "3 results"

The locale defaults to ``\Locale::getDefault()`` (or ``en`` when the ``intl``
extension is not available) and can be changed using ``setLocale()``. The
locale only affects message selection; no translation catalog is ever used.

.. _translation-pseudo-localization:

Pseudo-Localization Translator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    The pseudolocalization translator is meant to be used for development only.

The following image shows a typical menu on a webpage:

.. image:: /_images/translation/pseudolocalization-interface-original.png
    :alt: A menu showing multiple items nicely aligned next to each other.

This other image shows the same menu when the user switches the language to
Spanish. Unexpectedly, some text is cut and other contents are so long that
they overflow and you can't see them:

.. image:: /_images/translation/pseudolocalization-interface-translated.png
    :alt: In Spanish, some menu items contain more letters which result in them being cut.

These kinds of errors are very common, because different languages can be longer
or shorter than the original application language. Another common issue is to
only check if the application works when using basic accented letters, instead
of checking for more complex characters such as the ones found in Polish,
Czech, etc.

These problems can be solved with `pseudolocalization`_, a software testing method
used for testing internationalization. In this method, instead of translating
the text of the software into a foreign language, the textual elements of an
application are replaced with an altered version of the original language.

For example, ``Account Settings`` is *translated* as ``[!!! Àççôûñţ
Šéţţîñĝš !!!]``. First, the original text is expanded in length with characters
like ``[!!! !!!]`` to test the application when using languages more verbose
than the original one. This solves the first problem.

In addition, the original characters are replaced by similar but accented
characters. This makes the text highly readable, while allowing you to test the
application with all kinds of accented and special characters. This solves the
second problem.

Full support for pseudolocalization was added to help you debug
internationalization issues in your applications. You can enable and configure
it in the translator configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            translator:
                pseudo_localization:
                    # replace characters by their accented version
                    accents: true
                    # wrap strings with brackets
                    brackets: true
                    # controls how many extra characters are added to make text longer
                    expansion_factor: 1.4
                    # maintain the original HTML tags of the translated contents
                    parse_html: true
                    # also translate the contents of these HTML attributes
                    localizable_html_attributes: ['title']

    .. code-block:: php

        // config/packages/translation.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'framework' => [
                'translator' => [
                    'pseudo_localization' => [
                        // replace characters by their accented version
                        'accents' => true,
                        // wrap strings with brackets
                        'brackets' => true,
                        // controls how many extra characters are added to make text longer
                        'expansion_factor' => 1.4,
                        // maintain the original HTML tags of the translated contents
                        'parse_html' => true,
                        // also translate the contents of these HTML attributes
                        'localizable_html_attributes' => ['title'],
                    ],
                ],
            ],
        ]);

That's all. The application will now start displaying those strange, but
readable, contents to help you internationalize it. See for example the
difference in the `Symfony Demo`_ application. This is the original page:

.. image:: /_images/translation/pseudolocalization-symfony-demo-disabled.png
    :alt: The Symfony demo login page.
    :class: with-browser

And this is the same page with pseudolocalization enabled:

.. image:: /_images/translation/pseudolocalization-symfony-demo-enabled.png
    :alt: The Symfony demo login page with pseudolocalization.
    :class: with-browser

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

    reference/formats/message_format
    reference/formats/xliff

.. _`i18n`: https://en.wikipedia.org/wiki/Internationalization_and_localization
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`PHP intl extension`: https://php.net/book.intl
.. _`Translatable Extension`: https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md
.. _`Custom Language Name setting`: https://docs.lokalise.com/en/articles/1400492-uploading-files#custom-language-codes
.. _`ICU resource bundle`: https://github.com/unicode-org/icu-docs/blob/main/design/bnf_rb.txt
.. _`Portable object format`: https://www.gnu.org/software/gettext/manual/html_node/PO-Files.html
.. _`Machine object format`: https://www.gnu.org/software/gettext/manual/html_node/MO-Files.html
.. _`QT Translations TS XML`: https://doc.qt.io/qt-5/linguist-ts-file-format.html
.. _`GitHub Actions`: https://docs.github.com/en/free-pro-team@latest/actions
.. _`pseudolocalization`: https://en.wikipedia.org/wiki/Pseudolocalization
.. _`Symfony Demo`: https://github.com/symfony/demo
.. _`Crowdin Language Codes`: https://developer.crowdin.com/language-codes
.. _`Custom Language Codes`: https://support.crowdin.com/project-settings/#languages
.. _`Identification via User-Agent`: https://developers.phrase.com/api/#overview--identification-via-user-agent
.. _`Phrase Tag Bundle`: https://github.com/wickedOne/phrase-tag-bundle
.. _`Crowdin`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Translation/Bridge/Crowdin/README.md
.. _`Loco (localise.biz)`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Translation/Bridge/Loco/README.md
.. _`Lokalise`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Translation/Bridge/Lokalise/README.md
.. _`Phrase`: https://github.com/symfony/symfony/blob/{version}/src/Symfony/Component/Translation/Bridge/Phrase/README.md
.. _`AST`: https://en.wikipedia.org/wiki/Abstract_syntax_tree
.. _`locale subtag`: https://datatracker.ietf.org/doc/html/rfc4646
