TimezoneType Field
==================

The ``TimezoneType`` is a subset of the ``ChoiceType`` that allows the
user to select from all possible timezones.

The "value" for each timezone is the full timezone name, such as ``America/Chicago``
or ``Europe/Istanbul``.

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses a large list of timezones. You *can* specify the option
manually, but then you should just use the ``ChoiceType`` directly.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | Please select a valid timezone.                                        |
+---------------------------+------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                    |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`ChoiceType </reference/forms/types/choice>`                      |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimezoneType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

``input``
~~~~~~~~~

**type**: ``string`` **default**: ``string``

The format of the *input* data - i.e. the format that the timezone is stored
on your underlying object. Valid values are:

* ``datetimezone`` (a ``\DateTimeZone`` object)
* ``intltimezone`` (an ``\IntlTimeZone`` object)
* ``string`` (e.g. ``America/New_York``)

intl
~~~~

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the timezone selector will display the
timezones from the `ICU Project`_ via the :doc:`Intl component </components/intl>`
instead of the regular PHP timezones.

Although both sets of timezones are pretty similar, only the ones from the Intl
component can be translated to any language. To do so, set the desired locale
with the ``choice_translation_locale`` option.

.. note::

    The :doc:`Timezone constraint </reference/constraints/Timezone>` can validate
    both timezone sets and adapts to the selected set automatically.

Overridden Options
------------------

``choices``
~~~~~~~~~~~

**default**: An array of timezones.

The Timezone type defaults the choices to all timezones returned by
:phpmethod:`DateTimeZone::listIdentifiers`, broken down by continent.

.. caution::

    If you want to override the built-in choices of the timezone type, you
    will also have to set the ``choice_loader`` option to ``null``.

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/duplicate_preferred_choices.rst.inc

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

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`ICU Project`: https://icu.unicode.org/
