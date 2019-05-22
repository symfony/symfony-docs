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

Accessing ICU Data
------------------

This component provides the following ICU data:

* `Language and Script Names`_
* `Country Names`_
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

Country Names
~~~~~~~~~~~~~

The ``Countries`` class provides access to the name of all countries according
to the `ISO 3166-1 alpha-2`_ list of officially recognized countries and
territories::

    use Symfony\Component\Intl\Countries;

    \Locale::setDefault('en');

    $countries = Countries::getNames();
    // ('countryCode' => 'countryName')
    // => ['AF' => 'Afghanistan', 'AX' => 'Åland Islands', ...]

    $country = Countries::getName('GB');
    // => 'United Kingdom'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $countries = Countries::getNames('de');
    // => ['AF' => 'Afghanistan', 'EG' => 'Ägypten', ...]

    $country = Countries::getName('GB', 'de');
    // => 'Vereinigtes Königreich'

You can also check if a given country code is valid::

    $isValidCountry = Countries::exists($countryCode);

.. versionadded:: 4.3

    The ``Countries`` class was introduced in Symfony 4.3.

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
    // ('timezoneID' => 'timezoneValue')
    // => ['America/Eirunepe' => 'Acre Time (Eirunepe)', 'America/Rio_Branco' => 'Acre Time (Rio Branco)', ...]

    $timezone = Timezones::getName('Africa/Nairobi');
    // => 'East Africa Time (Nairobi)'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $timezones = Timezones::getNames('de');
    // => ['America/Eirunepe' => 'Acre-Zeit (Eirunepe)', 'America/Rio_Branco' => 'Acre-Zeit (Rio Branco)', ...]

    $timezone = Timezones::getName('Africa/Nairobi', 'de');
    // => 'Ostafrikanische Zeit (Nairobi)'

You can also check if a given timezone ID is valid::

    $isValidTimezone = Timezones::exists($timezoneId);

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
    /reference/forms/types/timezone

.. _Packagist: https://packagist.org/packages/symfony/intl
.. _Icu component: https://packagist.org/packages/symfony/icu
.. _intl extension: https://php.net/manual/en/book.intl.php
.. _install the intl extension: https://php.net/manual/en/intl.setup.php
.. _ICU library: http://site.icu-project.org/
.. _`Unicode ISO 15924 Registry`: https://www.unicode.org/iso15924/iso15924-codes.html
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
