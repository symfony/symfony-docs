.. index::
   single: Intl
   single: Components; Intl

The Intl Component
==================

    A PHP replacement layer for the C `intl extension`_ that also provides
    access to the localization data of the `ICU library`_.

.. caution::

    The replacement layer is limited to the locale "en". If you want to use
    other locales, you should `install the intl extension`_ instead.

.. seealso::

    This article explains how to use the Intl features as an independent component
    in any PHP application. Read the :doc:`/translation` article to learn about
    how to internationalize and manage the user locale in Symfony applications.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/intl

.. include:: /components/require_autoload.rst.inc

If you install the component via Composer, the following classes and functions
of the intl extension will be automatically provided if the intl extension is
not loaded:

* :phpclass:`Collator`
* :phpclass:`IntlDateFormatter`
* :phpclass:`Locale`
* :phpclass:`NumberFormatter`
* :phpfunction:`intl_error_name`
* :phpfunction:`intl_is_failure`
* :phpfunction:`intl_get_error_code`
* :phpfunction:`intl_get_error_message`

When the intl extension is not available, the following classes are used to
replace the intl classes:

* :class:`Symfony\\Component\\Intl\\Collator\\Collator`
* :class:`Symfony\\Component\\Intl\\DateFormatter\\IntlDateFormatter`
* :class:`Symfony\\Component\\Intl\\Locale\\Locale`
* :class:`Symfony\\Component\\Intl\\NumberFormatter\\NumberFormatter`
* :class:`Symfony\\Component\\Intl\\Globals\\IntlGlobals`

Composer automatically exposes these classes in the global namespace.

Writing and Reading Resource Bundles
------------------------------------

The :phpclass:`ResourceBundle` class is not currently supported by this component.
Instead, it includes a set of readers and writers for reading and writing
arrays (or array-like objects) from/to resource bundle files. The following
classes are supported:

* `TextBundleWriter`_
* `PhpBundleWriter`_
* `BinaryBundleReader`_
* `PhpBundleReader`_
* `BufferedBundleReader`_
* `StructuredBundleReader`_

Continue reading if you are interested in how to use these classes. Otherwise
skip this section and jump to `Accessing ICU Data`_.

TextBundleWriter
~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Writer\\TextBundleWriter`
writes an array or an array-like object to a plain-text resource bundle. The
resulting .txt file can be converted to a binary .res file with the
:class:`Symfony\\Component\\Intl\\ResourceBundle\\Compiler\\BundleCompiler`
class::

    use Symfony\Component\Intl\ResourceBundle\Compiler\BundleCompiler;
    use Symfony\Component\Intl\ResourceBundle\Writer\TextBundleWriter;

    $writer = new TextBundleWriter();
    $writer->write('/path/to/bundle', 'en', [
        'Data' => [
            'entry1',
            'entry2',
            // ...
        ],
    ]);

    $compiler = new BundleCompiler();
    $compiler->compile('/path/to/bundle', '/path/to/binary/bundle');

The command "genrb" must be available for the
:class:`Symfony\\Component\\Intl\\ResourceBundle\\Compiler\\BundleCompiler` to
work. If the command is located in a non-standard location, you can pass its
path to the
:class:`Symfony\\Component\\Intl\\ResourceBundle\\Compiler\\BundleCompiler`
constructor.

PhpBundleWriter
~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Writer\\PhpBundleWriter`
writes an array or an array-like object to a .php resource bundle::

    use Symfony\Component\Intl\ResourceBundle\Writer\PhpBundleWriter;

    $writer = new PhpBundleWriter();
    $writer->write('/path/to/bundle', 'en', [
        'Data' => [
            'entry1',
            'entry2',
            // ...
        ],
    ]);

BinaryBundleReader
~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\BinaryBundleReader`
reads binary resource bundle files and returns an array or an array-like object.
This class currently only works with the `intl extension`_ installed::

    use Symfony\Component\Intl\ResourceBundle\Reader\BinaryBundleReader;

    $reader = new BinaryBundleReader();
    $data = $reader->read('/path/to/bundle', 'en');

    var_dump($data['Data']['entry1']);

PhpBundleReader
~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\PhpBundleReader`
reads resource bundles from .php files and returns an array or an array-like
object::

    use Symfony\Component\Intl\ResourceBundle\Reader\PhpBundleReader;

    $reader = new PhpBundleReader();
    $data = $reader->read('/path/to/bundle', 'en');

    var_dump($data['Data']['entry1']);

BufferedBundleReader
~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\BufferedBundleReader`
wraps another reader, but keeps the last N reads in a buffer, where N is a
buffer size passed to the constructor::

    use Symfony\Component\Intl\ResourceBundle\Reader\BinaryBundleReader;
    use Symfony\Component\Intl\ResourceBundle\Reader\BufferedBundleReader;

    $reader = new BufferedBundleReader(new BinaryBundleReader(), 10);

    // actually reads the file
    $data = $reader->read('/path/to/bundle', 'en');

    // returns data from the buffer
    $data = $reader->read('/path/to/bundle', 'en');

    // actually reads the file
    $data = $reader->read('/path/to/bundle', 'fr');

StructuredBundleReader
~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\StructuredBundleReader`
wraps another reader and offers a
:method:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\StructuredBundleReaderInterface::readEntry`
method for reading an entry of the resource bundle without having to worry
whether array keys are set or not. If a path cannot be resolved, ``null`` is
returned::

    use Symfony\Component\Intl\ResourceBundle\Reader\BinaryBundleReader;
    use Symfony\Component\Intl\ResourceBundle\Reader\StructuredBundleReader;

    $reader = new StructuredBundleReader(new BinaryBundleReader());

    $data = $reader->read('/path/to/bundle', 'en');

    // produces an error if the key "Data" does not exist
    var_dump($data['Data']['entry1']);

    // returns null if the key "Data" does not exist
    var_dump($reader->readEntry('/path/to/bundle', 'en', ['Data', 'entry1']));

Additionally, the
:method:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\StructuredBundleReaderInterface::readEntry`
method resolves fallback locales. For example, the fallback locale of "en_GB" is
"en". For single-valued entries (strings, numbers etc.), the entry will be read
from the fallback locale if it cannot be found in the more specific locale. For
multi-valued entries (arrays), the values of the more specific and the fallback
locale will be merged. In order to suppress this behavior, the last parameter
``$fallback`` can be set to ``false``::

    var_dump($reader->readEntry(
        '/path/to/bundle',
        'en',
        ['Data', 'entry1'],
        false
    ));

Accessing ICU Data
------------------

This component provides the following ICU data:

* `Language and Script Names`_
* `Country and Region Names`_
* `Locales`_
* `Currencies`_
* `Timezones`_

Language and Script Names
~~~~~~~~~~~~~~~~~~~~~~~~~

The ``Languages`` class provides access to the name of all languages::

    use Symfony\Component\Intl\Languages;

    \Locale::setDefault('en');

    $languages = Languages::getNames();
    // ('languageCode' => 'languageName')
    // => ['ab' => 'Abkhazian', 'ace' => 'Achinese', ...]

    $language = Languages::getName('fr');
    // => 'French'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $languages = Languages::getNames('de');
    // => ['ab' => 'Abchasisch', 'ace' => 'Aceh', ...]

    $language = Languages::getName('fr', 'de');
    // => 'Französisch'

You can also check if a given language code is valid::

    $isValidLanguage = Languages::exists($languageCode);

.. versionadded:: 4.3

    The ``Languages`` class was introduced in Symfony 4.3.

The ``Scripts`` class provides access to the optional four-letter script code
that can follow the language code according to the `Unicode ISO 15924 Registry`_
(e.g. ``HANS`` in ``zh_HANS`` for simplified Chinese and ``HANT`` in ``zh_HANT``
for traditional Chinese)::

    use Symfony\Component\Intl\Scripts;

    \Locale::setDefault('en');

    $scripts = Scripts::getNames();
    // ('scriptCode' => 'scriptName')
    // => ['Adlm' => 'Adlam', 'Afak' => 'Afaka', ...]

    $script = Scripts::getName('Hans');
    // => 'Simplified'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $languages = Scripts::getNames('de');
    // => ['Adlm' => 'Adlam', 'Afak' => 'Afaka', ...]

    $language = Scripts::getName('Hans', 'de');
    // => 'Vereinfacht'

You can also check if a given script code is valid::

    $isValidScript = Scripts::exists($scriptCode);

.. versionadded:: 4.3

    The ``Scripts`` class was introduced in Symfony 4.3.

.. _country-names:

Country and Region Names
~~~~~~~~~~~~~~~~~~~~~~~~

In the world there are some territorial disputes that make it hard to define
what a country is. That's why the Intl component provides a ``Regions`` class
instead of a ``Countries`` class::

    use Symfony\Component\Intl\Regions;

    \Locale::setDefault('en');

    $countries = Regions::getNames();
    // ('regionCode' => 'regionName')
    // => ['AF' => 'Afghanistan', 'AX' => 'Åland Islands', ...]

    $country = Regions::getName('GB');
    // => 'United Kingdom'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $countries = Regions::getNames('de');
    // => ['AF' => 'Afghanistan', 'EG' => 'Ägypten', ...]

    $country = Regions::getName('GB', 'de');
    // => 'Vereinigtes Königreich'

You can also check if a given region code is valid::

    $isValidRegion = Regions::exists($regionCode);

.. versionadded:: 4.3

    The ``Regions`` class was introduced in Symfony 4.3.

Locales
~~~~~~~

A locale is the combination of a language and a region. For example, "Chinese"
is the language and ``zh_Hans_MO`` is the locale for "Chinese" (language) +
"Simplified" (script) + "Macau SAR China" (region). The ``Locales`` class
provides access to the name of all locales::

    use Symfony\Component\Intl\Locales;

    \Locale::setDefault('en');

    $locales = Locales::getNames();
    // ('localeCode' => 'localeName')
    // => ['af' => 'Afrikaans', 'af_NA' => 'Afrikaans (Namibia)', ...]

    $locale = Locales::getName('zh_Hans_MO');
    // => 'Chinese (Simplified, Macau SAR China)'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $locales = Locales::getNames('de');
    // => ['af' => 'Afrikaans', 'af_NA' => 'Afrikaans (Namibia)', ...]

    $locale = Locales::getName('zh_Hans_MO', 'de');
    // => 'Chinesisch (Vereinfacht, Sonderverwaltungsregion Macau)'

You can also check if a given locale code is valid::

    $isValidLocale = Locales::exists($localeCode);

.. versionadded:: 4.3

    The ``Locales`` class was introduced in Symfony 4.3.

Currencies
~~~~~~~~~~

The ``Currencies`` class provides access to the name of all currencies as well
as some of their information (symbol, fraction digits, etc.)::

    use Symfony\Component\Intl\Currencies;

    \Locale::setDefault('en');

    $currencies = Currencies::getNames();
    // ('currencyCode' => 'currencyName')
    // => ['AFN' => 'Afghan Afghani', 'ALL' => 'Albanian Lek', ...]

    $currency = Currencies::getName('INR');
    // => 'Indian Rupee'

    $symbol = Currencies::getSymbol('INR');
    // => '₹'

    $fractionDigits = Currencies::getFractionDigits('INR');
    // => 2

    $roundingIncrement = Currencies::getRoundingIncrement('INR');
    // => 0

All methods (except for ``getFractionDigits()`` and ``getRoundingIncrement()``)
accept the translation locale as the last, optional parameter, which defaults to
the current default locale::

    $currencies = Currencies::getNames('de');
    // => ['AFN' => 'Afghanischer Afghani', 'EGP' => 'Ägyptisches Pfund', ...]

    $currency = Currencies::getName('INR', 'de');
    // => 'Indische Rupie'

You can also check if a given currency code is valid::

    $isValidCurrency = Currencies::exists($currencyCode);

.. versionadded:: 4.3

    The ``Currencies`` class was introduced in Symfony 4.3.

Timezones
~~~~~~~~~

The ``Timezones`` class provides access to the name and values of all timezones::

    use Symfony\Component\Intl\Timezones;

    \Locale::setDefault('en');

    $timezones = Timezones::getNames();
    // ('timezoneName' => 'timezoneValue')
    // => ['America/Eirunepe' => 'Acre Time (Eirunepe)', 'America/Rio_Branco' => 'Acre Time (Rio Branco)', ...]

    $timezone = Timezones::getName('Africa/Nairobi');
    // => 'East Africa Time (Nairobi)'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $timezones = Timezones::getNames('de');
    // => ['America/Eirunepe' => 'Acre-Zeit (Eirunepe)', 'America/Rio_Branco' => 'Acre-Zeit (Rio Branco)', ...]

    $timezone = Timezones::getName('Africa/Nairobi', 'de');
    // => 'Ostafrikanische Zeit (Nairobi)'

You can also check if a given timezone name is valid::

    $isValidTimezone = Timezones::exists($timezoneName);

.. versionadded:: 4.3

    The ``Timezones`` class was introduced in Symfony 4.3.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /reference/forms/types/country
    /reference/forms/types/currency
    /reference/forms/types/language
    /reference/forms/types/locale

.. _Packagist: https://packagist.org/packages/symfony/intl
.. _Icu component: https://packagist.org/packages/symfony/icu
.. _intl extension: https://php.net/manual/en/book.intl.php
.. _install the intl extension: https://php.net/manual/en/intl.setup.php
.. _ICU library: http://site.icu-project.org/
.. _`Unicode ISO 15924 Registry`: https://www.unicode.org/iso15924/iso15924-codes.html
