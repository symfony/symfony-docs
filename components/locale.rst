.. index::
   single: Locale
   single: Components; Locale

The Locale Component
====================

    Locale component provides fallback code to handle cases when the ``intl`` extension is missing.
    Additionally it extends the implementation of a native :phpclass:`Locale` class with several handy methods.

Replacement for the following functions and classes is provided:

* :phpfunction:`intl_is_failure`
* :phpfunction:`intl_get_error_code`
* :phpfunction:`intl_get_error_message`
* :phpclass:`Collator`
* :phpclass:`IntlDateFormatter`
* :phpclass:`Locale`
* :phpclass:`NumberFormatter`

.. note::

     Stub implementation only supports the ``en`` locale.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Locale);
* Install it via PEAR ( `pear.symfony.com/Locale`);
* Install it via Composer (`symfony/locale` on Packagist).

Usage
-----

Taking advantage of the fallback code includes requiring function stubs and adding class stubs to the autoloader.

When using the ClassLoader component following code is sufficient to supplement missing ``intl`` extension:

.. code-block:: php

    if (!function_exists('intl_get_error_code')) {
        require __DIR__.'/path/to/src/Symfony/Component/Locale/Resources/stubs/functions.php';

        $loader->registerPrefixFallbacks(array(__DIR__.'/path/to/src/Symfony/Component/Locale/Resources/stubs'));
    }

:class:`Symfony\\Component\\Locale\\Locale` class enriches native :phpclass:`Locale` class with additional features:

.. code-block:: php

    use Symfony\Component\Locale\Locale;

    // Get the country names for a locale or get all country codes
    $countries = Locale::getDisplayCountries('pl');
    $countryCodes = Locale::getCountries();

    // Get the language names for a locale or get all language codes
    $languages = Locale::getDisplayLanguages('fr');
    $languageCodes = Locale::getLanguages();

    // Get the locale names for a given code or get all locale codes
    $locales = Locale::getDisplayLocales('en');
    $localeCodes = Locale::getLocales();

    // Get ICU versions
    $icuVersion = Locale::getIcuVersion();
    $icuDataVersion = Locale::getIcuDataVersion();

