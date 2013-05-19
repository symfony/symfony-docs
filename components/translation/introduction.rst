.. index::
    single: Translation
    single: Components; Translation

The Translation Component
=========================

    The Translation component provides tools to internationalize your
    application.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Translation);
* :doc:`Install it via Composer</components/using_components>` (``symfony/translation`` on `Packagist`_).

Constructing the Translator
---------------------------

The main access point of the Translation Component is
:class:`Symfony\\Component\\Translation\\Translator`. Before you can use it,
you need to configure it and load the messages to translate (called *message
catalogues*).

Configuration
~~~~~~~~~~~~~

The constructor of the ``Translator`` class needs two arguments: The locale
and the :class:`Symfony\\Component\\Translation\\MessageSelector` to use when
using pluralization (more about that later)::

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;

    $translator = new Translator('fr_FR', new MessageSelector());

.. note::

    The locale set here is the default locale to use. You can override this
    locale when translating strings.

Loading Message Catalogues
~~~~~~~~~~~~~~~~~~~~~~~~~~

The messages are stored in message catalogues inside the ``Translator``
class. A message catalogue is like a dictionary of translations for a specific 
locale.

The Translation component uses Loader classes to load catalogues. You can load
multiple resources for the same locale, it will be combined into one
catalogue.

The component comes with some default Loaders and you can create your own
Loader too. The default loaders are:

* :class:`Symfony\\Component\\Translation\\Loader\\ArrayLoader` - to load
  catalogues from PHP arrays.
* :class:`Symfony\\Component\\Translation\\Loader\\CsvFileLoader` - to load
  catalogues from CSV files.
* :class:`Symfony\\Component\\Translation\\Loader\\PhpFileLoader` - to load
  catalogues from PHP files.
* :class:`Symfony\\Component\\Translation\\Loader\\XliffFileLoader` - to load
  catalogues from Xliff files.
* :class:`Symfony\\Component\\Translation\\Loader\\YamlFileLoader` - to load
  catalogues from Yaml files (requires the :doc:`Yaml component</components/yaml>`).

All loaders, except the ``ArrayLoader``, requires the
:doc:`Config component</components/config/index>`.

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

If you use one of the file loaders, you also use the ``addResource`` method.
The only difference is that you put the file name as the second argument,
instead of an array::

    // ...
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', 'path/to/messages.fr.yml', 'fr_FR');

The Translation Process
-----------------------

To actually translate the message, the Translator uses a simple process:

* A catalog of translated messages is loaded from translation resources defined
  for the ``locale`` (e.g. ``fr_FR``). Messages from the
  :ref:`fallback locale <Fallback Locale>` are also loaded and added to the
  catalog if they don't already exist. The end result is a large "dictionary"
  of translations;

* If the message is located in the catalog, the translation is returned. If
  not, the translator returns the original message.

You start this process by calling
:method:`Symfony\\Component\\Translation\\Translator::trans` or
:method:`Symfony\\Component\\Translation\\Translator::transChoice`. Then, the
Translator looks for the exact string inside the appropriate message catalog
and returns it (if it exists).

.. tip::

    When a translation does not exist for a locale, the translator first tries
    to find the translation for the language (e.g. ``fr`` if the locale is
    ``fr_FR``). If this also fails, it looks for a translation using the
    fallback locale.

Fallback Locale
~~~~~~~~~~~~~~~

If the message is not located in the catalogue of the specific locale, the
translator will look into the catalogue of the fallback locale. You can set
this fallback locale by calling
:method:`Symfony\\Component\\Translation\\Translator::setFallbackLocale`::

    // ...
    $translator->setFallbackLocale('en_EN');

Usage
-----

Read how to use the Translation components in ":doc:`/components/translation/usage`"

.. _Packagist: https://packagist.org/packages/symfony/translation
