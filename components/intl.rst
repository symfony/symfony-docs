.. index::
   single: Intl
   single: Components; Intl

The Intl Component
==================

    This component provides access to the localization data of the `ICU library`_.

.. caution::

    The replacement layer is limited to the ``en`` locale. If you want to use
    other locales, you should `install the intl extension`_. There is no conflict
    between the two because, even if you use the extension, this package can still
    be useful to access the ICU data.

.. seealso::

    This article explains how to use the Intl features as an independent component
    in any PHP application. Read the :doc:`/translation` article to learn about
    how to internationalize and manage the user locale in Symfony applications.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/intl

.. include:: /components/require_autoload.rst.inc

Accessing ICU Data
------------------

This component provides the following ICU data:

* `Language and Script Names`_
* `Country Names`_
* `Locales`_
* `Currencies`_
* `Timezones`_
* `Emoji Transliteration`_

Language and Script Names
~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\Languages` class provides access to the name of all languages
according to the `ISO 639-1 alpha-2`_ list and the `ISO 639-2 alpha-3 (2T)`_ list::

    use Symfony\Component\Intl\Languages;

    \Locale::setDefault('en');

    $languages = Languages::getNames();
    // ('languageCode' => 'languageName')
    // => ['ab' => 'Abkhazian', 'ace' => 'Achinese', ...]

    $languages = Languages::getAlpha3Names();
    // ('languageCode' => 'languageName')
    // => ['abk' => 'Abkhazian', 'ace' => 'Achinese', ...]

    $language = Languages::getName('fr');
    // => 'French'

    $language = Languages::getAlpha3Name('fra');
    // => 'French'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $languages = Languages::getNames('de');
    // => ['ab' => 'Abchasisch', 'ace' => 'Aceh', ...]

    $languages = Languages::getAlpha3Names('de');
    // => ['abk' => 'Abchasisch', 'ace' => 'Aceh', ...]

    $language = Languages::getName('fr', 'de');
    // => 'Französisch'

    $language = Languages::getAlpha3Name('fra', 'de');
    // => 'Französisch'

If the given locale doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given language code is valid::

    $isValidLanguage = Languages::exists($languageCode);

Or if you have an alpha3 language code you want to check::

    $isValidLanguage = Languages::alpha3CodeExists($alpha3Code);

You may convert codes between two-letter alpha2 and three-letter alpha3 codes::

    $alpha3Code = Languages::getAlpha3Code($alpha2Code);

    $alpha2Code = Languages::getAlpha2Code($alpha3Code);

The :class:`Symfony\\Component\\Intl\\Scripts` class provides access to the optional four-letter script code
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

    $scripts = Scripts::getNames('de');
    // => ['Adlm' => 'Adlam', 'Afak' => 'Afaka', ...]

    $script = Scripts::getName('Hans', 'de');
    // => 'Vereinfacht'

If the given script code doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given script code is valid::

    $isValidScript = Scripts::exists($scriptCode);

Country Names
~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\Countries` class provides access to the
name of all countries according to the `ISO 3166-1 alpha-2`_ list and the
`ISO 3166-1 alpha-3`_ list of officially recognized countries and territories::

    use Symfony\Component\Intl\Countries;

    \Locale::setDefault('en');

    $countries = Countries::getNames();
    // ('alpha2Code' => 'countryName')
    // => ['AF' => 'Afghanistan', 'AX' => 'Åland Islands', ...]

    $countries = Countries::getAlpha3Names();
    // ('alpha3Code' => 'countryName')
    // => ['AFG' => 'Afghanistan', 'ALA' => 'Åland Islands', ...]

    $country = Countries::getName('GB');
    // => 'United Kingdom'

    $country = Countries::getAlpha3Name('NOR');
    // => 'Norway'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $countries = Countries::getNames('de');
    // => ['AF' => 'Afghanistan', 'EG' => 'Ägypten', ...]

    $countries = Countries::getAlpha3Names('de');
    // => ['AFG' => 'Afghanistan', 'EGY' => 'Ägypten', ...]

    $country = Countries::getName('GB', 'de');
    // => 'Vereinigtes Königreich'

    $country = Countries::getAlpha3Name('GBR', 'de');
    // => 'Vereinigtes Königreich'

If the given country code doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given country code is valid::

    $isValidCountry = Countries::exists($alpha2Code);

Or if you have an alpha3 country code you want to check::

    $isValidCountry = Countries::alpha3CodeExists($alpha3Code);

You may convert codes between two-letter alpha2 and three-letter alpha3 codes::

    $alpha3Code = Countries::getAlpha3Code($alpha2Code);

    $alpha2Code = Countries::getAlpha2Code($alpha3Code);

Locales
~~~~~~~

A locale is the combination of a language, a region and some parameters that
define the interface preferences of the user. For example, "Chinese" is the
language and ``zh_Hans_MO`` is the locale for "Chinese" (language) + "Simplified"
(script) + "Macau SAR China" (region). The :class:`Symfony\\Component\\Intl\\Locales`
class provides access to the name of all locales::

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

If the given locale code doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given locale code is valid::

    $isValidLocale = Locales::exists($localeCode);

Currencies
~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\Currencies` class provides access to the name
of all currencies as well as some of their information (symbol, fraction digits, etc.)::

    use Symfony\Component\Intl\Currencies;

    \Locale::setDefault('en');

    $currencies = Currencies::getNames();
    // ('currencyCode' => 'currencyName')
    // => ['AFN' => 'Afghan Afghani', 'ALL' => 'Albanian Lek', ...]

    $currency = Currencies::getName('INR');
    // => 'Indian Rupee'

    $symbol = Currencies::getSymbol('INR');
    // => '₹'

The fraction digits methods return the number of decimal digits to display when
formatting numbers with this currency. Depending on the currency, this value
can change if the number is used in cash transactions or in other scenarios
(e.g. accounting)::

    // Indian rupee defines the same value for both
    $fractionDigits = Currencies::getFractionDigits('INR');         // returns: 2
    $cashFractionDigits = Currencies::getCashFractionDigits('INR'); // returns: 2

    // Swedish krona defines different values
    $fractionDigits = Currencies::getFractionDigits('SEK');         // returns: 2
    $cashFractionDigits = Currencies::getCashFractionDigits('SEK'); // returns: 0

Some currencies require to round numbers to the nearest increment of some value
(e.g. 5 cents). This increment might be different if numbers are formatted for
cash transactions or other scenarios (e.g. accounting)::

    // Indian rupee defines the same value for both
    $roundingIncrement = Currencies::getRoundingIncrement('INR');         // returns: 0
    $cashRoundingIncrement = Currencies::getCashRoundingIncrement('INR'); // returns: 0

    // Canadian dollar defines different values because they have eliminated
    // the smaller coins (1-cent and 2-cent) and prices in cash must be rounded to
    // 5 cents (e.g. if price is 7.42 you pay 7.40; if price is 7.48 you pay 7.50)
    $roundingIncrement = Currencies::getRoundingIncrement('CAD');         // returns: 0
    $cashRoundingIncrement = Currencies::getCashRoundingIncrement('CAD'); // returns: 5

All methods (except for ``getFractionDigits()``, ``getCashFractionDigits()``,
``getRoundingIncrement()`` and ``getCashRoundingIncrement()``) accept the
translation locale as the last, optional parameter, which defaults to the
current default locale::

    $currencies = Currencies::getNames('de');
    // => ['AFN' => 'Afghanischer Afghani', 'EGP' => 'Ägyptisches Pfund', ...]

    $currency = Currencies::getName('INR', 'de');
    // => 'Indische Rupie'

If the given currency code doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given currency code is valid::

    $isValidCurrency = Currencies::exists($currencyCode);

.. _component-intl-timezones:

Timezones
~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\Timezones` class provides several utilities
related to timezones. First, you can get the name and values of all timezones in
all languages::

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

You can also get all the timezones that exist in a given country. The
``forCountryCode()`` method returns one or more timezone IDs, which you can
translate into any locale with the ``getName()`` method shown earlier::

    // unlike language codes, country codes are always uppercase (CL = Chile)
    $timezones = Timezones::forCountryCode('CL');
    // => ['America/Punta_Arenas', 'America/Santiago', 'Pacific/Easter']

The reverse lookup is also possible thanks to the ``getCountryCode()`` method,
which returns the code of the country where the given timezone ID belongs to::

    $countryCode = Timezones::getCountryCode('America/Vancouver');
    // => $countryCode = 'CA' (CA = Canada)

The `UTC/GMT time offsets`_ of all timezones are provided by ``getRawOffset()``
(which returns an integer representing the offset in seconds) and
``getGmtOffset()`` (which returns a string representation of the offset to
display it to users)::

    $offset = Timezones::getRawOffset('Etc/UTC');              // $offset = 0
    $offset = Timezones::getRawOffset('America/Buenos_Aires'); // $offset = -10800
    $offset = Timezones::getRawOffset('Asia/Katmandu');        // $offset = 20700

    $offset = Timezones::getGmtOffset('Etc/UTC');              // $offset = 'GMT+00:00'
    $offset = Timezones::getGmtOffset('America/Buenos_Aires'); // $offset = 'GMT-03:00'
    $offset = Timezones::getGmtOffset('Asia/Katmandu');        // $offset = 'GMT+05:45'

The timezone offset can vary in time because of the `daylight saving time (DST)`_
practice. By default these methods use the ``time()`` PHP function to get the
current timezone offset value, but you can pass a timestamp as their second
arguments to get the offset at any given point in time::

    // In 2019, the DST period in Madrid (Spain) went from March 31 to October 27
    $offset = Timezones::getRawOffset('Europe/Madrid', strtotime('March 31, 2019'));   // $offset = 3600
    $offset = Timezones::getRawOffset('Europe/Madrid', strtotime('April 1, 2019'));    // $offset = 7200
    $offset = Timezones::getGmtOffset('Europe/Madrid', strtotime('October 27, 2019')); // $offset = 'GMT+02:00'
    $offset = Timezones::getGmtOffset('Europe/Madrid', strtotime('October 28, 2019')); // $offset = 'GMT+01:00'

The string representation of the GMT offset can vary depending on the locale, so
you can pass the locale as the third optional argument::

    $offset = Timezones::getGmtOffset('Europe/Madrid', strtotime('October 28, 2019'), 'ar'); // $offset = 'غرينتش+01:00'
    $offset = Timezones::getGmtOffset('Europe/Madrid', strtotime('October 28, 2019'), 'dz'); // $offset = 'ཇི་ཨེམ་ཏི་+01:00'

If the given timezone ID doesn't exist, the methods trigger a
:class:`Symfony\\Component\\Intl\\Exception\\MissingResourceException`. In addition
to catching the exception, you can also check if a given timezone ID is valid::

    $isValidTimezone = Timezones::exists($timezoneId);

.. _component-intl-emoji-transliteration:

Emoji Transliteration
~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.2

    The Emoji transliteration feature was introduced in Symfony 6.2.

The ``EmojiTransliterator`` class provides a utility to translate emojis into
their textual representation in all languages based on the `Unicode CLDR dataset`_::

    use Symfony\Component\Intl\Transliterator\EmojiTransliterator;

    // describe emojis in English
    $transliterator = EmojiTransliterator::create('en');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with pizza or spaghetti'

    // describe emojis in Ukrainian
    $transliterator = EmojiTransliterator::create('uk');
    $transliterator->transliterate('Menus with 🍕 or 🍝');
    // => 'Menus with піца or спагеті'

The ``EmojiTransliterator`` class also provides two extra catalogues: ``github``
and ``slack`` that converts any emojis to the corresponding short code in those
platforms::

    use Symfony\Component\Intl\Transliterator\EmojiTransliterator;

    // describe emojis in Slack short code
    $transliterator = EmojiTransliterator::create('slack');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

    // describe emojis in Github short code
    $transliterator = EmojiTransliterator::create('github');
    $transliterator->transliterate('Menus with 🥗 or 🧆');
    // => 'Menus with :green_salad: or :falafel:'

.. tip::

    Combine this emoji transliterator with the :ref:`Symfony String slugger <string-slugger-emoji>`
    to improve the slugs of contents that include emojis (e.g. for URLs).

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

.. _install the intl extension: https://www.php.net/manual/en/intl.setup.php
.. _ICU library: http://site.icu-project.org/
.. _`Unicode ISO 15924 Registry`: https://www.unicode.org/iso15924/iso15924-codes.html
.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
.. _`ISO 3166-1 alpha-3`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
.. _`UTC/GMT time offsets`: https://en.wikipedia.org/wiki/List_of_UTC_time_offsets
.. _`daylight saving time (DST)`: https://en.wikipedia.org/wiki/Daylight_saving_time
.. _`ISO 639-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_639-1
.. _`ISO 639-2 alpha-3 (2T)`: https://en.wikipedia.org/wiki/ISO_639-2
.. _`Unicode CLDR dataset`: https://github.com/unicode-org/cldr
