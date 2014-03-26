.. index::
   single: Intl
   single: Components; Intl

The Intl Component
==================

    A PHP replacement layer for the C `intl extension`_ that also provides
    access to the localization data of the `ICU library`_.

.. versionadded:: 2.3
    The Intl component was introduced in Symfony 2.3. In earlier versions of Symfony,
    you should use the Locale component instead.

.. caution::

    The replacement layer is limited to the locale "en". If you want to use
    other locales, you should `install the intl extension`_ instead.

Installation
------------

You can install the component in two different ways:

* Using the official Git repository (https://github.com/symfony/Intl);
* :doc:`Install it via Composer</components/using_components>` (``symfony/intl`` on `Packagist`_).

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

If you don't use Composer but the
:doc:`Symfony ClassLoader component </components/class_loader/introduction>`,
you need to expose them manually by adding the following lines to your autoload
code::

    if (!function_exists('intl_is_failure')) {
        require '/path/to/Icu/Resources/stubs/functions.php';

        $loader->registerPrefixFallback('/path/to/Icu/Resources/stubs');
    }

.. sidebar:: ICU and Deployment Problems

    The intl extension internally uses the `ICU library`_ to obtain localization
    data such as number formats in different languages, country names and more.
    To make this data accessible to userland PHP libraries, Symfony2 ships a copy
    in the `Icu component`_.

    Depending on the ICU version compiled with your intl extension, a matching
    version of that component needs to be installed. It sounds complicated,
    but usually Composer does this for you automatically:

    * 1.0.*: when the intl extension is not available
    * 1.1.*: when intl is compiled with ICU 3.8 or higher
    * 1.2.*: when intl is compiled with ICU 4.4 or higher

    These versions are important when you deploy your application to a **server with
    a lower ICU version** than your development machines, because deployment will
    fail if:

    * the development machines are compiled with ICU 4.4 or higher, but the
      server is compiled with a lower ICU version than 4.4;
    * the intl extension is available on the development machines but not on
      the server.

    For example, consider that your development machines ship ICU 4.8 and the server
    ICU 4.2. When you run ``php composer.phar update`` on the development machine, version
    1.2.* of the Icu component will be installed. But after deploying the
    application, ``php composer.phar install`` will fail with the following error:

    .. code-block:: bash

        $ php composer.phar install
        Loading composer repositories with package information
        Installing dependencies from lock file
        Your requirements could not be resolved to an installable set of packages.

          Problem 1
            - symfony/icu 1.2.x requires lib-icu >=4.4 -> the requested linked
              library icu has the wrong version installed or is missing from your
              system, make sure to have the extension providing it.

    The error tells you that the requested version of the Icu component, version
    1.2, is not compatible with PHP's ICU version 4.2.

    One solution to this problem is to run ``php composer.phar update`` instead of
    ``php composer.phar install``. It is highly recommended **not** to do this. The
    ``update`` command will install the latest versions of each Composer dependency
    to your production server and potentially break the application.

    A better solution is to fix your composer.json to the version required by the
    production server. First, determine the ICU version on the server:

    .. code-block:: bash

        $ php -i | grep ICU
        ICU version => 4.2.1

    Then fix the Icu component in your ``composer.json`` file to a matching version:

    .. code-block:: json

        "require: {
            "symfony/icu": "1.1.*"
        }

    Set the version to

    * "1.0.*" if the server does not have the intl extension installed;
    * "1.1.*" if the server is compiled with ICU 4.2 or lower.

    Finally, run ``php composer.phar update symfony/icu`` on your development machine, test
    extensively and deploy again. The installation of the dependencies will now
    succeed.

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

    use Symfony\Component\Intl\ResourceBundle\Writer\TextBundleWriter;
    use Symfony\Component\Intl\ResourceBundle\Compiler\BundleCompiler;

    $writer = new TextBundleWriter();
    $writer->write('/path/to/bundle', 'en', array(
        'Data' => array(
            'entry1',
            'entry2',
            // ...
        ),
    ));

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
    $writer->write('/path/to/bundle', 'en', array(
        'Data' => array(
            'entry1',
            'entry2',
            // ...
        ),
    ));

BinaryBundleReader
~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\BinaryBundleReader`
reads binary resource bundle files and returns an array or an array-like object.
This class currently only works with the `intl extension`_ installed::

    use Symfony\Component\Intl\ResourceBundle\Reader\BinaryBundleReader;

    $reader = new BinaryBundleReader();
    $data = $reader->read('/path/to/bundle', 'en');

    echo $data['Data']['entry1'];

PhpBundleReader
~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\PhpBundleReader`
reads resource bundles from .php files and returns an array or an array-like
object::

    use Symfony\Component\Intl\ResourceBundle\Reader\PhpBundleReader;

    $reader = new PhpBundleReader();
    $data = $reader->read('/path/to/bundle', 'en');

    echo $data['Data']['entry1'];

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

    // Produces an error if the key "Data" does not exist
    echo $data['Data']['entry1'];

    // Returns null if the key "Data" does not exist
    echo $reader->readEntry('/path/to/bundle', 'en', array('Data', 'entry1'));

Additionally, the
:method:`Symfony\\Component\\Intl\\ResourceBundle\\Reader\\StructuredBundleReaderInterface::readEntry`
method resolves fallback locales. For example, the fallback locale of "en_GB" is
"en". For single-valued entries (strings, numbers etc.), the entry will be read
from the fallback locale if it cannot be found in the more specific locale. For
multi-valued entries (arrays), the values of the more specific and the fallback
locale will be merged. In order to suppress this behavior, the last parameter
``$fallback`` can be set to ``false``::

    echo $reader->readEntry(
        '/path/to/bundle',
        'en',
        array('Data', 'entry1'),
        false
    );

Accessing ICU Data
------------------

The ICU data is located in several "resource bundles". You can access a PHP
wrapper of these bundles through the static
:class:`Symfony\\Component\\Intl\\Intl` class. At the moment, the following
data is supported:

* `Language and Script Names`_
* `Country Names`_
* `Locales`_
* `Currencies`_

Language and Script Names
~~~~~~~~~~~~~~~~~~~~~~~~~

The translations of language and script names can be found in the language
bundle::

    use Symfony\Component\Intl\Intl;

    \Locale::setDefault('en');

    $languages = Intl::getLanguageBundle()->getLanguageNames();
    // => array('ab' => 'Abkhazian', ...)

    $language = Intl::getLanguageBundle()->getLanguageName('de');
    // => 'German'

    $language = Intl::getLanguageBundle()->getLanguageName('de', 'AT');
    // => 'Austrian German'

    $scripts = Intl::getLanguageBundle()->getScriptNames();
    // => array('Arab' => 'Arabic', ...)

    $script = Intl::getLanguageBundle()->getScriptName('Hans');
    // => 'Simplified'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $languages = Intl::getLanguageBundle()->getLanguageNames('de');
    // => array('ab' => 'Abchasisch', ...)

Country Names
~~~~~~~~~~~~~

The translations of country names can be found in the region bundle::

    use Symfony\Component\Intl\Intl;

    \Locale::setDefault('en');

    $countries = Intl::getRegionBundle()->getCountryNames();
    // => array('AF' => 'Afghanistan', ...)

    $country = Intl::getRegionBundle()->getCountryName('GB');
    // => 'United Kingdom'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $countries = Intl::getRegionBundle()->getCountryNames('de');
    // => array('AF' => 'Afghanistan', ...)

Locales
~~~~~~~

The translations of locale names can be found in the locale bundle::

    use Symfony\Component\Intl\Intl;

    \Locale::setDefault('en');

    $locales = Intl::getLocaleBundle()->getLocaleNames();
    // => array('af' => 'Afrikaans', ...)

    $locale = Intl::getLocaleBundle()->getLocaleName('zh_Hans_MO');
    // => 'Chinese (Simplified, Macau SAR China)'

All methods accept the translation locale as the last, optional parameter,
which defaults to the current default locale::

    $locales = Intl::getLocaleBundle()->getLocaleNames('de');
    // => array('af' => 'Afrikaans', ...)

Currencies
~~~~~~~~~~

The translations of currency names and other currency-related information can
be found in the currency bundle::

    use Symfony\Component\Intl\Intl;

    \Locale::setDefault('en');

    $currencies = Intl::getCurrencyBundle()->getCurrencyNames();
    // => array('AFN' => 'Afghan Afghani', ...)

    $currency = Intl::getCurrencyBundle()->getCurrencyName('INR');
    // => 'Indian Rupee'

    $symbol = Intl::getCurrencyBundle()->getCurrencySymbol('INR');
    // => '₹'

    $fractionDigits = Intl::getCurrencyBundle()->getFractionDigits('INR');
    // => 2

    $roundingIncrement = Intl::getCurrencyBundle()->getRoundingIncrement('INR');
    // => 0

All methods (except for
:method:`Symfony\\Component\\Intl\\ResourceBundle\\CurrencyBundleInterface::getFractionDigits`
and
:method:`Symfony\\Component\\Intl\\ResourceBundle\\CurrencyBundleInterface::getRoundingIncrement`)
accept the translation locale as the last, optional parameter, which defaults
to the current default locale::

    $currencies = Intl::getCurrencyBundle()->getCurrencyNames('de');
    // => array('AFN' => 'Afghanische Afghani', ...)

That's all you need to know for now. Have fun coding!

.. _Packagist: https://packagist.org/packages/symfony/intl
.. _Icu component: https://packagist.org/packages/symfony/icu
.. _intl extension: http://www.php.net/manual/en/book.intl.php
.. _install the intl extension: http://www.php.net/manual/en/intl.setup.php
.. _ICU library: http://site.icu-project.org/
