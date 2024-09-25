BirthdayType Field
==================

A :doc:`DateType </reference/forms/types/date>` field that specializes in handling
birth date data.

Can be rendered as a single text box, three text boxes (month, day and year),
or three select boxes.

This type is essentially the same as the :doc:`DateType </reference/forms/types/date>`
type, but with a more appropriate default for the `years`_ option. The `years`_
option defaults to 120 years ago to the current year.

+---------------------------+-------------------------------------------------------------------------------+
| Underlying Data Type      | can be ``DateTime``, ``string``, ``timestamp``, or ``array``                  |
|                           | (see the :ref:`input option <form-reference-date-input>`)                     |
+---------------------------+-------------------------------------------------------------------------------+
| Rendered as               | can be three select boxes or 1 or 3 text boxes, based on the `widget`_ option |
+---------------------------+-------------------------------------------------------------------------------+
| Default invalid message   | Please enter a valid birthdate.                                               |
+---------------------------+-------------------------------------------------------------------------------+
| Parent type               | :doc:`DateType </reference/forms/types/date>`                                 |
+---------------------------+-------------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType`        |
+---------------------------+-------------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

``years``
~~~~~~~~~

**type**: ``array`` **default**: 120 years ago to the current year

List of years available to the year field type. This option is only
relevant when the ``widget`` option is set to ``choice``.

Inherited Options
-----------------

These options inherit from the :doc:`DateType </reference/forms/types/date>`:

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

``placeholder``
~~~~~~~~~~~~~~~

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    $builder->add('birthdate', BirthdayType::class, [
        'placeholder' => 'Select a value',
    ]);

Alternatively, you can use an array that configures different placeholder
values for the year, month and day fields::

    $builder->add('birthdate', BirthdayType::class, [
        'placeholder' => [
            'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
        ],
    ]);

.. include:: /reference/forms/types/options/date_format.rst.inc

.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/date_input_format.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

.. include:: /reference/forms/types/options/date_widget.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
