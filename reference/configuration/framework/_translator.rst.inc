translator
~~~~~~~~~~

cache_dir
.........

**type**: ``string`` | ``null`` **default**: ``%kernel.cache_dir%/translations``

Defines the directory where the translation cache is stored. Use ``null`` to
disable this cache.

.. _reference-translator-default_path:

default_path
............

**type**: ``string`` **default**: ``%kernel.project_dir%/translations``

This option allows you to define the path where the application translations files
are stored.

.. _reference-translator-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable the ``translator`` service in the service container.

.. _fallback:

fallbacks
.........

**type**: ``string|array`` **default**: value of `default_locale`_

This option is used when the translation key for the current locale wasn't
found.

.. seealso::

    For more details, see :doc:`/translation`.

.. _reference-framework-translator-formatter:

formatter
.........

**type**: ``string`` **default**: ``translator.formatter.default``

The ID of the service used to format translation messages. The service class
must implement the :class:`Symfony\\Component\\Translation\\Formatter\\MessageFormatterInterface`.

globals
.......

**type**: ``array``

Global parameters available in all translations. Each global parameter can be
either a simple value or an array with the following options:

* ``value`` (**type**: ``mixed``) the direct value of the parameter.
* ``message`` (**type**: ``string``) a translation message key.
* ``parameters`` (**type**: ``array``) the translation parameters.
* ``domain`` (**type**: ``string``) the translation domain.

Each global parameter must define either ``value`` or ``message``, but not both.

.. _reference-framework-translator-logging:

logging
.......

**default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

When ``true``, a log entry is made whenever the translator cannot find a translation
for a given key. The logs are made to the ``translation`` channel at the
``debug`` level for keys where there is a translation in the fallback
locale, and the ``warning`` level if there is no translation to use at all.

.. _reference-translator-paths:

paths
.....

**type**: ``array`` **default**: ``[]``

This option allows you to define an array of paths where the component will look
for translation files. The later a path is added, the more priority it has
(translations from later paths overwrite earlier ones). Translations from the
:ref:`default_path <reference-translator-default_path>` have more priority than
translations from all these paths.

.. _reference-translator-providers:

providers
.........

**type**: ``array`` **default**: ``[]``

This option enables and configures :ref:`translation providers <translation-providers>`
to push and pull your translations to/from third party translation services.

pseudo_localization
...................

**type**: ``array``

This option enables and configures the
:ref:`pseudolocalization <translation-pseudo-localization>` of translations,
which is a testing method that replaces the text elements of the application
with altered versions of the original language (e.g. replacing ``a`` with ``à``,
wrapping strings with brackets, etc.):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            translator:
                pseudo_localization:
                    enabled: true
                    accents: true
                    brackets: true
                    expansion_factor: 1.0
                    parse_html: false
                    localizable_html_attributes: []

    .. code-block:: php

        // config/packages/translation.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->translator()
                ->pseudoLocalization()
                    ->enabled(true)
                    ->accents(true)
                    ->brackets(true)
                    ->expansionFactor(1.0)
                    ->parseHtml(false)
                    ->localizableHtmlAttributes([])
            ;
        };

enabled
"""""""

**type**: ``boolean`` **default**: ``false``

Whether or not to enable pseudolocalization.

accents
"""""""

**type**: ``boolean`` **default**: ``true``

When ``true``, replaces characters with accented versions of themselves (e.g.
``a`` is replaced by ``à``, ``c`` by ``ç``, etc.). This helps test the
application with special and accented characters.

brackets
""""""""

**type**: ``boolean`` **default**: ``true``

When ``true``, wraps each translated string with brackets (e.g. ``[!!! ... !!!]``).
This helps detect strings that are not being processed by the translator.

expansion_factor
""""""""""""""""

**type**: ``float`` **default**: ``1.0``

Controls the number of extra characters added to translated strings to simulate
the expansion of text when translating to more verbose languages. The minimum
value is ``1.0`` (no expansion). For example, a value of ``1.4`` means that
strings will be approximately 40% longer than the original text.

parse_html
""""""""""

**type**: ``boolean`` **default**: ``false``

When ``true``, HTML tags within translated content are preserved and only the
text content is pseudo-localized. This prevents HTML from being broken by the
pseudolocalization process.

localizable_html_attributes
"""""""""""""""""""""""""""

**type**: ``array`` **default**: ``[]``

Defines which HTML attributes should also be pseudo-localized (e.g.
``['title', 'alt']``). This option is only relevant when ``parse_html`` is
set to ``true``.
