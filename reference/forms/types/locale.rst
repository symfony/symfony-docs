.. index::
   single: Forms; Fields; locale

locale Field Type
=================

The ``locale`` type is a subset of the ``ChoiceType`` that allows the user
to select from a large list of locales (language+country). As an added bonus,
the locale names are displayed in the language of the user.

The "value" for each locale is either the two letter `ISO 639-1`_ *language*
code (e.g. ``fr``), or the language code followed by an underscore (``_``),
then the `ISO 3166-1 alpha-2`_ *country* code (e.g. ``fr_FR``
for French/France).

.. note::

   The locale of your user is guessed using :phpmethod:`Locale::getDefault`

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses a large list
of locales. You *can* specify either of these options manually, but then
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
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\LocaleType`   |
+-------------+------------------------------------------------------------------------+

Overridden Options
------------------

choices
~~~~~~~

**default**: ``Symfony\Component\Intl\Intl::getLocaleBundle()->getLocaleNames()``

The choices option defaults to all locales. It uses the default locale to
specify the language.

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

.. _`ISO 639-1`: http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`ISO 3166-1 alpha-2`: http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
