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

Usage
-----

The :class:`Symfony\\Component\\Translation\\Translator` class is the main
entry point of the Translation component.

.. code-block:: php

    use Symfony\Component\Translation\Translator;
    use Symfony\Component\Translation\MessageSelector;
    use Symfony\Component\Translation\Loader\ArrayLoader;

    $translator = new Translator('fr_FR', new MessageSelector());
    $translator->addLoader('array', new ArrayLoader());
    $translator->addResource('array', array(
        'Hello World!' => 'Bonjour',
    ), 'fr_FR');
    
    echo $translator->trans('Hello World!');

Constructing the Translator
---------------------------

Before you can use the Translator, you need to configure it and load the
message catalogues.

Configuration
~~~~~~~~~~~~~

The constructor of the ``Translator`` class needs to arguments: The locale and
a :class:`Symfony\\Component\\Translation\\MessageSelector` to use when using
pluralization (more about that later)::

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
  catalogues from Csv files.
* :class:`Symfony\\Component\\Translation\\Loader\\PhpFileLoader` - to load
  catalogues from Php files.
* :class:`Symfony\\Component\\Translation\\Loader\\XliffFileLoader` - to load
  catalogues from Xliff files.
* :class:`Symfony\\Component\\Translation\\Loader\\YamlFileLoader` - to load
  catalogues from Yaml files (requires the :doc:`Yaml component</components/yaml>`).

All loaders, except the ``ArrayLoader``, requires the
:doc:`Config component</components/config/index>`.

At first, you should add a loader to the ``Translator``::

    // ...
    $translator->addLoader('array', new ArrayLoader());

The first argument is the key to which we can refer the loader in the translator
and the second argument is an instance of the loader itself. After this, you
can add your resources using the correct loader.

Loading Messages with the ``ArrayLoader``
.........................................

Loading messages can be done by calling
:method:`Symfony\\Component\\Translation\\Translator::addResource`. The first
argument is the loader name (the first argument of the ``addLoader``
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

.. _Packagist: https://packagist.org/packages/symfony/translation
