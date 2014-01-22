.. index::
   single: Forms; Fields; language

language Field Type
===================

The ``language`` type is a subset of the ``ChoiceType`` that allows the user
to select from a large list of languages. As an added bonus, the language names
are displayed in the language of the user.

The "value" for each language is the *Unicode language identifier*
(e.g. ``fr`` or ``zh-Hant``).

.. note::

   The locale of your user is guessed using :phpmethod:`Locale::getDefault`

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses a large list
of languages. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

+-------------+------------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `choices`_                                                           |
| Options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `multiple`_                                                          |
| options     | - `expanded`_                                                          |
|             | - `preferred_choices`_                                                 |
|             | - `empty_value`_                                                       |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `empty_data`_                                                        |
|             | - `required`_                                                          |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `data`_                                                              |
|             | - `read_only`_                                                         |
|             | - `disabled`_                                                          |
|             | - `mapped`_                                                            |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`choice </reference/forms/types/choice>`                          |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\LanguageType` |
+-------------+------------------------------------------------------------------------+

Overridden Options
------------------

choices
~~~~~~~

**default**: ``Symfony\Component\Intl\Intl::getLanguageBundle()->getLanguageNames()``.

The choices option defaults to all languages.
The default locale is used to translate the languages names.

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
