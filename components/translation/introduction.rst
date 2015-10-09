.. index::
    single: Translation
    single: Components; Translation

The Translation Component
=========================

    The Translation component provides tools to internationalize your
    application.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/translation`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/translation).

.. include:: /components/require_autoload.rst.inc

Constructing the Translator
---------------------------

The main access point of the Translation component is
:class:`Symfony\\Component\\Translation\\Translator`. Before you can use it,
you need to configure it and load the messages to translate (called *message
catalogs*).

Configuration
~~~~~~~~~~~~~

The constructor of the ``Translator`` class needs one argument: The locale.

.. code-block:: php

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;

    $translator = new Translator('fr_FR', new MessageSelector());

.. note::

    The locale set here is the default locale to use. You can override this
    locale when translating strings.

.. note::

    The term *locale* refers roughly to the user's language and country. It
    can be any string that your application uses to manage translations and
    other format differences (e.g. currency format). The `ISO 639-1`_
    *language* code, an underscore (``_``), then the `ISO 3166-1 alpha-2`_
    *country* code (e.g. ``fr_FR`` for French/France) is recommended.

.. _component-translator-message-catalogs:

Loading Message Catalogs
~~~~~~~~~~~~~~~~~~~~~~~~

The messages are stored in message catalogs inside the ``Translator``
class. A message catalog is like a dictionary of translations for a specific
locale.

The Translation component uses Loader classes to load catalogs. You can load
multiple resources for the same locale, which will then be combined into one
catalog.

The component comes with some default Loaders and you can create your own
Loader too. The default loaders are:

* :class:`Symfony\\Component\\Translation\\Loader\\ArrayLoader` - to load
  catalogs from PHP arrays.
* :class:`Symfony\\Component\\Translation\\Loader\\CsvFileLoader` - to load
  catalogs from CSV files.
* :class:`Symfony\\Component\\Translation\\Loader\\IcuDatFileLoader` - to load
  catalogs from resource bundles.
* :class:`Symfony\\Component\\Translation\\Loader\\IcuResFileLoader` - to load
  catalogs from resource bundles.
* :class:`Symfony\\Component\\Translation\\Loader\\IniFileLoader` - to load
  catalogs from ini files.
* :class:`Symfony\\Component\\Translation\\Loader\\MoFileLoader` - to load
  catalogs from gettext files.
* :class:`Symfony\\Component\\Translation\\Loader\\PhpFileLoader` - to load
  catalogs from PHP files.
* :class:`Symfony\\Component\\Translation\\Loader\\PoFileLoader` - to load
  catalogs from gettext files.
* :class:`Symfony\\Component\\Translation\\Loader\\QtFileLoader` - to load
  catalogs from QT XML files.
* :class:`Symfony\\Component\\Translation\\Loader\\XliffFileLoader` - to load
  catalogs from Xliff files.
* :class:`Symfony\\Component\\Translation\\Loader\\JsonFileLoader` - to load
  catalogs from JSON files.
* :class:`Symfony\\Component\\Translation\\Loader\\YamlFileLoader` - to load
  catalogs from Yaml files (requires the :doc:`Yaml component</components/yaml/introduction>`).

All file loaders require the :doc:`Config component </components/config/index>`.

You can also :doc:`create your own Loader </components/translation/custom_formats>`,
in case the format is not already supported by one of the default loaders.

At first, you should add one or more loaders to the ``Translator``::

    // ...
    $translator->addLoader('array', new ArrayLoader());

The first argument is the name to which you can refer the loader in the
translator and the second argument is an instance of the loader itself. After
this, you can add your resources using the correct loader.

Loading Messages with the ``ArrayLoader``
.........................................

Loading messages can be done by calling
:method:`Symfony\\Component\\Translation\\Translator::addResource`. The first
argument is the loader name (this was the first argument of the ``addLoader``
method), the second is the resource and the third argument is the locale::

    // ...
    $translator->addResource('array', array(
        'Hello World!' => 'Bonjour',
    ), 'fr_FR');

Loading Messages with the File Loaders
......................................

If you use one of the file loaders, you should also use the ``addResource``
method. The only difference is that you should put the file name to the resource
file as the second argument, instead of an array::

    // ...
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', 'path/to/messages.fr.yml', 'fr_FR');

The Translation Process
-----------------------

To actually translate the message, the Translator uses a simple process:

* A catalog of translated messages is loaded from translation resources defined
  for the ``locale`` (e.g. ``fr_FR``). Messages from the
  :ref:`components-fallback-locales` are also loaded and added to the
  catalog, if they don't already exist. The end result is a large "dictionary"
  of translations;

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

You start this process by calling
:method:`Symfony\\Component\\Translation\\Translator::trans` or
:method:`Symfony\\Component\\Translation\\Translator::transChoice`. Then, the
Translator looks for the exact string inside the appropriate message catalog
and returns it (if it exists).

.. _components-fallback-locales:

Fallback Locales
~~~~~~~~~~~~~~~~

If the message is not located in the catalog of the specific locale, the
translator will look into the catalog of one or more fallback locales. For
example, assume you're trying to translate into the ``fr_FR`` locale:

#. First, the translator looks for the translation in the ``fr_FR`` locale;

#. If it wasn't found, the translator looks for the translation in the ``fr``
   locale;

#. If the translation still isn't found, the translator uses the one or more
   fallback locales set explicitly on the translator.

For (3), the fallback locales can be set by calling
:method:`Symfony\\Component\\Translation\\Translator::setFallbackLocales`::

    // ...
    $translator->setFallbackLocales(array('en'));

.. _using-message-domains:

Using Message Domains
---------------------

As you've seen, message files are organized into the different locales that
they translate. The message files can also be organized further into "domains".

The domain is specified in the fourth argument of the ``addResource()``
method. The default domain is ``messages``. For example, suppose that, for
organization, translations were split into three different domains:
``messages``, ``admin`` and ``navigation``. The French translation would be
loaded like this::

    // ...
    $translator->addLoader('xlf', new XliffFileLoader());

    $translator->addResource('xlf', 'messages.fr.xlf', 'fr_FR');
    $translator->addResource('xlf', 'admin.fr.xlf', 'fr_FR', 'admin');
    $translator->addResource(
        'xlf',
        'navigation.fr.xlf',
        'fr_FR',
        'navigation'
    );

When translating strings that are not in the default domain (``messages``),
you must specify the domain as the third argument of ``trans()``::

    $translator->trans('Symfony is great', array(), 'admin');

Symfony will now look for the message in the ``admin`` domain of the
specified locale.

Usage
-----

Read how to use the Translation component in :doc:`/components/translation/usage`.

.. _Packagist: https://packagist.org/packages/symfony/translation
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
