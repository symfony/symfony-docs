CountryType Field
=================

The ``CountryType`` is a subset of the ``ChoiceType`` that displays countries
of the world. As an added bonus, the country names are displayed in the
language of the user.

The "value" for each country is the two-letter country code.

.. note::

    The locale of your user is guessed using :phpmethod:`Locale::getDefault`

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses all of the countries of the world. You *can* specify
the option manually, but then you should just use the ``ChoiceType`` directly.

+---------------------------+-----------------------------------------------------------------------+
| Rendered as               | can be various tags (see :ref:`forms-reference-choice-tags`)          |
+---------------------------+-----------------------------------------------------------------------+
| Default invalid message   | Please select a valid country.                                        |
+---------------------------+-----------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                   |
+---------------------------+-----------------------------------------------------------------------+
| Parent type               | :doc:`ChoiceType </reference/forms/types/choice>`                     |
+---------------------------+-----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CountryType` |
+---------------------------+-----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

alpha3
~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is ``true``, the choice values use the `ISO 3166-1 alpha-3`_
three-letter codes (e.g. New Zealand = ``NZL``) instead of the default
`ISO 3166-1 alpha-2`_ two-letter codes (e.g. New Zealand = ``NZ``).

.. include:: /reference/forms/types/options/choice_translation_locale.rst.inc

Overridden Options
------------------

``choices``
~~~~~~~~~~~

**default**: ``Symfony\Component\Intl\Countries::getNames()``

The country type defaults the ``choices`` option to the whole list of countries.
The locale is used to translate the countries names.

.. caution::

    If you want to override the built-in choices of the country type, you
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

.. include:: /reference/forms/types/options/placeholder_attr.rst.inc

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

.. _`ISO 3166-1 alpha-2`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
.. _`ISO 3166-1 alpha-3`: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
