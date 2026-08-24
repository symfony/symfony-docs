DateTimeType Field
==================

This field type allows the user to modify data that represents a specific
date and time (e.g. ``1984-06-05 12:15:30``).

Can be rendered as a text input or select tags. The underlying format of
the data can be a ``DateTime`` object, a string, a timestamp or an array.

+---------------------------+-----------------------------------------------------------------------------+
| Underlying Data Type      | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+---------------------------+-----------------------------------------------------------------------------+
| Rendered as               | single text box or five select fields                                       |
+---------------------------+-----------------------------------------------------------------------------+
| Default invalid message   | Please enter a valid date and time.                                         |
+---------------------------+-----------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                               |
+---------------------------+-----------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType`      |
+---------------------------+-----------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/choice_translation_domain.rst.inc

date_format
~~~~~~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Defines the ``format`` option that will be passed down to the date field.
See the :ref:`DateType's format option <reference-forms-type-date-format>`
for more details.

date_label
~~~~~~~~~~

**type**: ``string`` | ``null`` **default**: The label is "guessed" from the field name

Sets the label that will be used when rendering the date widget. Setting it to
``false`` will suppress the label::

    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    $builder->add('startDateTime', DateTimeType::class, [
        'date_label' => 'Starts On',
    ]);

date_widget
~~~~~~~~~~~

.. include:: /reference/forms/types/options/date_widget_description.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

placeholder
~~~~~~~~~~~

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    $builder->add('startDateTime', DateTimeType::class, [
        'placeholder' => 'Select a value',
    ]);

Alternatively, you can use an array that configures different placeholder
values for the year, month, day, hour, minute and second fields::

    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    $builder->add('startDateTime', DateTimeType::class, [
        'placeholder' => [
            'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
            'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
        ],
    ]);

format
~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\DateTimeType::HTML5_FORMAT``

If the ``widget`` option is set to ``single_text``, this option specifies
the format of the input, i.e. how Symfony will interpret the given input
as a datetime string. It defaults to the `datetime local`_ format which is
used by the HTML5 ``datetime-local`` field. Keeping the default value will
cause the field to be rendered as an ``input`` field with ``type="datetime-local"``.
For more information on valid formats, see `Date/Time Format Syntax`_.

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/html5.rst.inc

input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored
on your underlying object. Valid values are:

* ``string`` (e.g. ``2011-06-05 12:15:00``)
* ``datetime`` (a ``DateTime`` object)
* ``datetime_immutable`` (a ``DateTimeImmutable`` object)
* ``date_point`` (a :ref:`DatePoint <clock_date-point>` object)
* ``array`` (e.g. ``[2011, 06, 05, 12, 15, 0]``)
* ``timestamp`` (e.g. ``1307276100``)

The value that comes back from the form will also be normalized back into
this format.

.. include:: /reference/forms/types/options/_date_limitation.rst.inc

input_format
~~~~~~~~~~~~

**type**: ``string`` **default**: ``Y-m-d H:i:s``

.. include:: /reference/forms/types/options/date_input_format_description.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

time_label
~~~~~~~~~~

**type**: ``string`` | ``null`` **default**: The label is "guessed" from the field name

Sets the label that will be used when rendering the time widget. Setting it to
``false`` will suppress the label::

    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

    $builder->add('startDateTime', DateTimeType::class, [
        'time_label' => 'Starts On',
    ]);

time_widget
~~~~~~~~~~~

**type**: ``string`` **default**: ``choice``

Defines the ``widget`` option for the :doc:`TimeType </reference/forms/types/time>`.

.. include:: /reference/forms/types/options/view_timezone.rst.inc

widget
~~~~~~

**type**: ``string`` **default**: ``null``

Defines the ``widget`` option for both the :doc:`DateType </reference/forms/types/date>`
and :doc:`TimeType </reference/forms/types/time>`. This can be overridden
with the `date_widget`_ and `time_widget`_ options.

.. include:: /reference/forms/types/options/with_minutes.rst.inc

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

Overridden Options
------------------

by_reference
~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

.. include:: /reference/forms/types/options/compound_type.rst.inc

.. include:: /reference/forms/types/options/data_class_date.rst.inc

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

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

Field Variables
---------------

+----------+------------+----------------------------------------------------------------------+
| Variable | Type       | Usage                                                                |
+==========+============+======================================================================+
| widget   | ``mixed``  | The value of the `widget`_ option.                                   |
+----------+------------+----------------------------------------------------------------------+
| type     | ``string`` | Only present when widget is ``single_text`` and HTML5 is activated,  |
|          |            | contains the input type to use (``datetime``, ``date`` or ``time``). |
+----------+------------+----------------------------------------------------------------------+

.. _`datetime local`: https://html.spec.whatwg.org/multipage/input.html#local-date-and-time-state-(type=datetime-local)
.. _`Date/Time Format Syntax`: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
