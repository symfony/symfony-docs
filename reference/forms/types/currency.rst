CurrencyType Field
==================

The ``CurrencyType`` is a subset of the :doc:`ChoiceType </reference/forms/types/choice>`
that allows the user to select from a large list of `3-letter ISO 4217`_ currencies.

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses a large list of currencies. You *can* specify the option
manually, but then you should just use the ``ChoiceType`` directly.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | Please select a valid currency.                                        |
+---------------------------+------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                    |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`ChoiceType </reference/forms/types/choice>`                      |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CurrencyType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/choice_translation_locale.rst.inc

Overridden Options
------------------

``choices``
~~~~~~~~~~~

**default**: ``Symfony\Component\Intl\Currencies::getNames()``

The choices option defaults to all currencies.

.. caution::

    If you want to override the built-in choices of the currency type, you
    will also have to set the ``choice_loader`` option to ``null``.

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/duplicate_preferred_choices.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

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

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`3-letter ISO 4217`: https://en.wikipedia.org/wiki/ISO_4217
