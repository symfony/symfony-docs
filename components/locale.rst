.. index::
   single: Locale

The Locale Component
====================

    Locale component provides fallback code to handle cases when the ``intl`` extension is missing.

Replacement for the following functions and classes is provided:

* :phpfunction:`intl_is_failure()`
* :phpfunction:`intl_get_error_code()`
* :phpfunction:`intl_get_error_message()`
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

