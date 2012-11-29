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

Message Catalogues
------------------

The messages are stored in message catalogues inside the ``Translator``
class. A Message Catalogue is like a dictionary of translations for a specific 
locale.

Loading catalogues
~~~~~~~~~~~~~~~~~~

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

Translate Strings
-----------------

After you have loaded your Message Catalogues, you can begin to translate your
strings. This is done with the
:method:`Symfony\\Component\\Translation\\Translator::trans` method::

    // ...
    $translator->addResource('array', array(
        'Hello World!' => 'Bonjour',
    ), 'fr_FR');
    $translator->addResource('array', array(
        'Hello World!' => 'Hello World',
    ), 'en_GB');

    echo $translator->trans('Hello World!');
    // >> 'Bonjour'

By default, the ``trans`` method uses the locale that is set in the
constructor of the ``Translator``. If you want to translate another locale,
you can change that by setting the fourth argument to the locale::

    // ...
    echo $translator->trans('Hello World!', array(), 'messages', 'en_GB');
    // >> 'Hello World!'

Learn More
----------

The Translation component can do a lot more things. Read more about the usage
of this component in :ref:`the Translation book article <basic-translation>`.
That article is specific about the Translation component in the Symfony2
Framework, but most of the article is framework independent.

.. _Packagist: https://packagist.org/packages/symfony/translation
