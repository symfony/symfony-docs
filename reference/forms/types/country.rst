.. index::
   single: Forms; Fields; country

country Field Type
==================

The ``country`` type is a subset of the ``ChoiceType`` that displays countries
of the world. As an added bonus, the country names are displayed in the language
of the user.

The "value" for each country is the two-letter country code.

.. note::

   The locale of your user is guessed using :phpmethod:`Locale::getDefault`

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses all of the countries
of the world. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

+-------------+-----------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)          |
+-------------+-----------------------------------------------------------------------+
| Overridden  | - `choices`_                                                          |
| Options     |                                                                       |
+-------------+-----------------------------------------------------------------------+
| Inherited   | - `multiple`_                                                         |
| options     | - `expanded`_                                                         |
|             | - `preferred_choices`_                                                |
|             | - `empty_value`_                                                      |
|             | - `error_bubbling`_                                                   |
|             | - `error_mapping`_                                                    |
|             | - `empty_data`_                                                       |
|             | - `required`_                                                         |
|             | - `label`_                                                            |
|             | - `label_attr`_                                                       |
|             | - `data`_                                                             |
|             | - `read_only`_                                                        |
|             | - `disabled`_                                                         |
|             | - `mapped`_                                                           |
+-------------+-----------------------------------------------------------------------+
| Parent type | :doc:`choice </reference/forms/types/choice>`                         |
+-------------+-----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CountryType` |
+-------------+-----------------------------------------------------------------------+

Overridden Options
------------------

choices
~~~~~~~

**default**: ``Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames()``

The country type defaults the ``choices`` option to the whole list of countries.
The locale is used to translate the countries names.

Inherited Options
-----------------

These options inherit from the :doc:`choice </reference/forms/types/choice>` type:

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/empty_value.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
