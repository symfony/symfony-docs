LanguageType Field
==================

The ``LanguageType`` is a subset of the ``ChoiceType`` that allows the
user to select from a large list of languages. As an added bonus, the language
names are displayed in the language of the user.

The "value" for each language is the *Unicode language identifier* used
in the `International Components for Unicode`_ (e.g. ``fr`` or ``zh_Hant``).

.. note::

    The locale of your user is guessed using :phpmethod:`Locale::getDefault`,
    which requires the ``intl`` PHP extension to be installed and enabled.

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses a large list of languages. You *can* specify the option
manually, but then you should just use the ``ChoiceType`` directly.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | Please select a valid language.                                        |
+---------------------------+------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                    |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`ChoiceType </reference/forms/types/choice>`                      |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

alpha3
~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is ``true``, the choice values use the `ISO 639-2 alpha-3 (2T)`_
three-letter codes (e.g. French = ``fra``) instead of the default
`ISO 639-1 alpha-2`_ two-letter codes (e.g. French = ``fr``).

choice_self_translation
~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 5.1

    The ``choice_self_translation`` option was introduced in Symfony 5.1.

By default, language names are translated into the current locale of the
application. For example, when browsing the application in English, you'll get
an array like ``[..., 'cs' => 'Czech', ..., 'es' => 'Spanish', ..., 'zh' => 'Chinese']``
and when browsing it in French, you'll get the following array:
``[..., 'cs' => 'tchèque', ..., 'es' => 'espagnol', ..., 'zh' => 'chinois']``.

If this option is ``true``, each language is translated into its own language,
regardless of the current application locale:
``[..., 'cs' => 'čeština', ..., 'es' => 'español', ..., 'zh' => '中文']``.

.. include:: /reference/forms/types/options/choice_translation_locale.rst.inc

Overridden Options
------------------

``choices``
~~~~~~~~~~~

**default**: ``Symfony\Component\Intl\Languages::getNames()``.

The choices option defaults to all languages.
The default locale is used to translate the languages names.

.. caution::

    If you want to override the built-in choices of the language type, you
    will also have to set the ``choice_loader`` option to ``null``.

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/choice_type_trim.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``[]`` (empty array).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`ISO 639-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_639-1
.. _`ISO 639-2 alpha-3 (2T)`: https://en.wikipedia.org/wiki/ISO_639-2
.. _`International Components for Unicode`: https://icu.unicode.org/
