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

Translations can be organized into groups, called **domains**. By default, all
messages use the default ``messages`` domain::

    echo $translator->trans('Hello World', domain: 'messages');

The translation process has several steps:

#. :ref:`Enable and configure <translation-configuration>` Symfony's
   translation service;

#. Abstract strings (i.e. "messages") by wrapping them in calls to the
   ``Translator`` (":ref:`translation-basic`");

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

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework
                ->defaultLocale('en')
                ->translator()
                    ->defaultPath('%kernel.project_dir%/translations')
            ;
        };

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

#. The ``locale`` of the current user, which is stored on the request is
   determined; this is typically set via a ``_locale`` attribute on your routes
   (see :ref:`translation-locale-url`);

#. A catalog of translated messages is loaded from translation resources
   defined for the ``locale`` (e.g. ``fr_FR``). Messages from the
   :ref:`fallback locale <translation-fallback>` are also loaded and added to
   the catalog if they don't already exist. The end result is a large
   "dictionary" of translations.

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

Another complication is when you have translations that may or may not be
plural, based on some variable:

.. code-block:: text

    There is one apple.
    There are 5 apples.

To manage these situations, Symfony follows the `ICU MessageFormat`_ syntax by
using PHP's :phpclass:`MessageFormatter` class. Read more about this in
:doc:`/reference/formats/message_format`.

.. _translatable-objects:

Translatable Objects
--------------------

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

    The translation parameters can also be a :class:`Symfony\\Component\\Translation\\TranslatableMessage`.

.. tip::

    There's also a :ref:`function called t() <reference-twig-function-t>`,
    available both in Twig and PHP, as a shortcut to create translatable objects.

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

.. caution::

    Using the translation tag has the same effect as the filter, but with one
    major difference: automatic output escaping is **not** applied to translations
    using a tag.

Forcing the Translator Locale
-----------------------------

When translating a message, the translator uses the specified locale or the
``fallback`` locale if necessary. You can also manually specify the locale to
use for translation::

    $translator->trans('Symfony is great', locale: 'fr_FR');

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
  the ``translator`` service and makes calls to the ``trans()`` method.
* Any PHP file/class stored in the ``src/`` directory that creates
  :ref:`translatable-objects` using the constructor or the ``t()`` method or calls
  the ``trans()`` method.
* Any PHP file/class stored in the ``src/`` directory that uses
  :ref:`Constraints Attributes <validation-constraints>`  with ``*message`` named argument(s).

.. versionadded:: 6.2

    The support of PHP files/classes that use constraint attributes was
    introduced in Symfony 6.2.

.. _translation-resource-locations:

Translation Resource/File Names and Locations
---------------------------------------------

Symfony looks for message files (i.e. translations) in the following default locations:

* the ``translations/`` directory (at the root of the project);
* the ``translations/`` directory inside of any bundle (and also their
  ``Resources/translations/`` directory, which is no longer recommended for bundles).

The locations are listed here with the highest priority first. That is, you can
override the translation messages of a bundle in the first directory.

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

.. versionadded:: 6.1

    The ``.xliff`` file extension support was introduced in Symfony 6.1.

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

            return static function (FrameworkConfig $framework): void {
                $framework->translator()
                    ->paths(['%kernel.project_dir%/custom/path/to/translations'])
                ;
            };

.. note::

    You can also store translations in a database; it can be handled by
    Doctrine through the `Translatable Extension`_ or the `Translatable Behavior`_
    (PHP 5.4+). For more information, see the documentation for these libraries.

    For any other storage, you need to provide a custom class implementing the
    :class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface`
    interface. See the :ref:`dic-tags-translation-loader` tag for more
    information.

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

====================  ===========================================================
Provider              Install with
====================  ===========================================================
Crowdin               ``composer require symfony/crowdin-translation-provider``
Loco (localise.biz)   ``composer require symfony/loco-translation-provider``
Lokalise              ``composer require symfony/lokalise-translation-provider``
====================  ===========================================================

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
Loco provider that you just installed, which knows all about how to push and
pull translations via Loco. The *only* part you need to change is the
``API_KEY`` placeholder.

This table shows the full list of available DSN formats for each provider:

=====================  ==========================================================
Provider               DSN
=====================  ==========================================================
Crowdin                crowdin://PROJECT_ID:API_TOKEN@ORGANIZATION_DOMAIN.default
Loco (localise.biz)    loco://API_KEY@default
Lokalise               lokalise://PROJECT_ID:API_KEY@default
=====================  ==========================================================

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
                    <framework:provider name="loco" dsn="%env(LOCO_DSN)%">
                        <framework:domain>messages</framework:domain>
                        <!-- ... -->
                        <framework:locale>en</framework:locale>
                        <framework:locale>fr</framework:locale>
                        <!-- ... -->
                    </framework:provider>
                </framework:translator>
            </framework:config>
        </container>

    .. code-block:: php

        # config/packages/translation.php
        $container->loadFromExtension('framework', [
            'translator' => [
                'providers' => [
                    'loco' => [
                        'dsn' => env('LOCO_DSN'),
                        'domains' => ['messages'],
                        'locales' => ['en', 'fr'],
                    ],
                ],
            ],
        ]);

.. tip::

    If you use Lokalise as a provider and a locale format following the `ISO
    639-1`_ (e.g. "en" or "fr"), you have to set the `Custom Language Name setting`_
    in Lokalise for each of your locales, in order to override the
    default value (which follow the `ISO 639-1`_ succeeded by a sub-code in
    capital letters that specifies the national variety (e.g. "GB" or "US"
    according to `ISO 3166-1 alpha-2`_)).

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

.. _translation-locale:

Handling the User's Locale
--------------------------

Translating happens based on the user's locale. The locale of the current user
is stored in the request and is accessible via the ``Request`` object::

    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request)
    {
        $locale = $request->getLocale();
    }

To set the user's locale, you may want to create a custom event listener so
that it's set before any other parts of the system (i.e. the translator) need
it::

        public function onKernelRequest(RequestEvent $event)
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
            public function contact()
            {
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        contact:
            path:       /{_locale}/contact
            controller: App\Controller\ContactController::index
            requirements:
                _locale: en|fr|de

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="contact" path="/{_locale}/contact">
                controller="App\Controller\ContactController::index">
                <requirement key="_locale">en|fr|de</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\ContactController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('contact', '/{_locale}/contact')
                ->controller([ContactController::class, 'index'])
                ->requirements([
                    '_locale' => 'en|fr|de',
                ])
            ;
        };

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

            <framework:config default-locale="en"/>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->defaultLocale('en');
        };

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

            return static function (FrameworkConfig $framework): void {
                // ...
                $framework->translator()
                    ->fallbacks(['en'])
                ;
            };

.. note::

    When Symfony can't find a translation in the given locale, it will
    add the missing translation to the log file. For details,
    see :ref:`reference-framework-translator-logging`.

.. _locale-switcher:

Switch Locale Programmatically
------------------------------

.. versionadded:: 6.1

    The ``LocaleSwitcher`` was introduced in Symfony 6.1.

Sometimes you need to change the locale of the application dynamically
just to run some code. Imagine a console command that renders Twig templates
of emails in different languages. You need to change the locale only to
render those templates.

The ``LocaleSwitcher`` class allows you to change at once the locale
of:

* All the services that are tagged with ``kernel.locale_aware``;
* ``\Locale::setDefault()``;
* If the ``RequestContext`` service is available, the ``_locale``
  parameter (so urls are generated with the new locale)::

    use Symfony\Component\Translation\LocaleSwitcher;

    class SomeService
    {
        public function __construct(
            private LocaleSwitcher $localeSwitcher,
        ) {
        }

        public function someMethod()
        {
            // you can get the current application locale like this:
            $currentLocale = $this->localeSwitcher->getLocale();

            // you can set the locale for the entire application like this:
            // (from now on, the application will use 'fr' (French) as the
            // locale; including the default locale used to translate Twig templates)
            $this->localeSwitcher->setLocale('fr');

            // reset the current locale of your application to the configured default locale
            // in config/packages/translation.yaml, by option 'default_locale'
            $this->localeSwitcher->reset();

            // you can also run some code with a certain locale, without
            // changing the locale for the rest of the application
            $this->localeSwitcher->runWithLocale('es', function() {

                // e.g. render here some Twig templates using 'es' (Spanish) locale

            });

            // ...
        }
    }

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

.. caution::

    The extractors can't find messages translated outside templates (like form
    labels or controllers) unless using :ref:`translatable-objects` or calling
    the ``trans()`` method on a translator (since Symfony 5.3). Dynamic
    translations using variables or expressions in templates are not
    detected either:

    .. code-block:: twig

        {# this translation uses a Twig variable, so it won't be detected #}
        {% set message = 'Symfony is great' %}
        {{ message|trans }}

Suppose your application's default_locale is ``fr`` and you have configured
``en`` as the fallback locale (see :ref:`translation-configuration` and
:ref:`translation-fallback` for how to configure these). And suppose
you've already setup some translations for the ``fr`` locale:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/messages.fr.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="1">
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
     unused     Symfony is great    J'aime Symfony          Symfony is great
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
                Symfony is great    J'aime Symfony          Symfony is great
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
in the ``fr`` locale equals to the message in the ``en`` locale. This is a
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

If you prefer, you can also validate the contents of any YAML and XLIFF
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
.. _`ICU MessageFormat`: https://unicode-org.github.io/icu/userguide/format_parse/messages/
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`Translatable Extension`: https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md
.. _`Translatable Behavior`: https://github.com/KnpLabs/DoctrineBehaviors
.. _`Custom Language Name setting`: https://docs.lokalise.com/en/articles/1400492-uploading-files#custom-language-codes
.. _`ICU resource bundle`: https://github.com/unicode-org/icu-docs/blob/main/design/bnf_rb.txt
.. _`Portable object format`: https://www.gnu.org/software/gettext/manual/html_node/PO-Files.html
.. _`Machine object format`: https://www.gnu.org/software/gettext/manual/html_node/MO-Files.html
.. _`QT Translations TS XML`: https://doc.qt.io/qt-5/linguist-ts-file-format.html
.. _`GitHub Actions`: https://docs.github.com/en/free-pro-team@latest/actions
