.. index::
   single: Forms; Fields; country

country Field Type
==================

The ``country`` type is a subset of the ``ChoiceType`` that displays countries
of the world. As an added bonus, the country names are displayed in the language
of the user.

The "value" for each country is the two-letter country code.

.. note::

   The locale of your user is guessed using `Locale::getDefault()`_

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses all of the countries
of the world. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

+-------------+-----------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)          |
+-------------+-----------------------------------------------------------------------+
| Inherited   | - `multiple`_                                                         |
| options     | - `expanded`_                                                         |
|             | - `preferred_choices`_                                                |
|             | - `error_bubbling`_                                                   |
|             | - `required`_                                                         |
|             | - `label`_                                                            |
|             | - `read_only`_                                                        |
+-------------+-----------------------------------------------------------------------+
| Parent type | :doc:`choice</reference/forms/types/choice>`                          |
+-------------+-----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CountryType` |
+-------------+-----------------------------------------------------------------------+

Inherited options
-----------------

These options inherit from the :doc:`choice</reference/forms/types/choice>` type:

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. _`Locale::getDefault()`: http://php.net/manual/en/locale.getdefault.php
