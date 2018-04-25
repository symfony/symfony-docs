.. index::
    single: Forms; Fields; currency

CurrencyType Field
==================

The ``CurrencyType`` is a subset of the :doc:`ChoiceType </reference/forms/types/choice>`
that allows the user to select from a large list of `3-letter ISO 4217`_ currencies.

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses a large list of currencies. You *can* specify the option
manually, but then you should just use the ``ChoiceType`` directly.

+-------------+------------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `choices`_                                                           |
| options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | from the :doc:`ChoiceType </reference/forms/types/choice>`             |
| options     |                                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `expanded`_                                                          |
|             | - `multiple`_                                                          |
|             | - `placeholder`_                                                       |
|             | - `preferred_choices`_                                                 |
|             |                                                                        |
|             | from the :doc:`FormType </reference/forms/types/form>` type            |
|             |                                                                        |
|             | - `data`_                                                              |
|             | - `disabled`_                                                          |
|             | - `empty_data`_                                                        |
|             | - `help`_                                                              |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `label_format`_                                                      |
|             | - `mapped`_                                                            |
|             | - `required`_                                                          |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`ChoiceType </reference/forms/types/choice>`                      |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CurrencyType` |
+-------------+------------------------------------------------------------------------+

Overridden Options
------------------

choices
~~~~~~~

**default**: ``Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencyNames()``

The choices option defaults to all currencies.

.. caution::

    If you want to override the built-in choices of the currency type, you
    will also have to set the ``choice_loader`` option to ``null``.

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``array()`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. _`3-letter ISO 4217`: https://en.wikipedia.org/wiki/ISO_4217
