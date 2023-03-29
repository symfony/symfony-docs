TimeType Field
==============

A field to capture time input.

This can be rendered as a text field, a series of text fields (e.g. hour,
minute, second) or a series of select fields. The underlying data can be
stored as a ``DateTime`` object, a string, a timestamp or an array.

+---------------------------+-----------------------------------------------------------------------------+
| Underlying Data Type      | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+---------------------------+-----------------------------------------------------------------------------+
| Rendered as               | can be various tags (see below)                                             |
+---------------------------+-----------------------------------------------------------------------------+
| Default invalid message   | Please enter a valid time.                                                  |
+---------------------------+-----------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                         |
+---------------------------+-----------------------------------------------------------------------------+
| Parent type               | FormType                                                                    |
+---------------------------+-----------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType`          |
+---------------------------+-----------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Basic Usage
-----------

The most important options are ``input`` and ``widget``.

Suppose that you have a ``startTime`` field whose underlying time data is
a ``DateTime`` object. The following configures the ``TimeType`` for that
field as two different choice fields::

    use Symfony\Component\Form\Extension\Core\Type\TimeType;
    // ...

    $builder->add('startTime', TimeType::class, [
        'input'  => 'datetime',
        'widget' => 'choice',
    ]);

The ``input`` option *must* be changed to match the type of the underlying
date data. For example, if the ``startTime`` field's data were a unix timestamp,
you'd need to set ``input`` to ``timestamp``::

    use Symfony\Component\Form\Extension\Core\Type\TimeType;
    // ...

    $builder->add('startTime', TimeType::class, [
        'input'  => 'timestamp',
        'widget' => 'choice',
    ]);

The field also supports an ``array`` and ``string`` as valid ``input`` option
values.

Field Options
-------------

.. include:: /reference/forms/types/options/choice_translation_domain_disabled.rst.inc

placeholder
~~~~~~~~~~~

**type**: ``string`` | ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. When the placeholder value is a string,
it will be used as the **blank value** of all select boxes::

    $builder->add('startTime', 'time', [
        'placeholder' => 'Select a value',
    ]);

Alternatively, you can use an array that configures different placeholder
values for the hour, minute and second fields::

    $builder->add('startTime', 'time', [
        'placeholder' => [
            'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
        ],
    ]);

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/html5.rst.inc

input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored
on your underlying object. Valid values are:

* ``string`` (e.g. ``12:17:26``)
* ``datetime`` (a ``DateTime`` object)
* ``datetime_immutable`` (a ``DateTimeImmutable`` object)
* ``array`` (e.g. ``['hour' => 12, 'minute' => 17, 'second' => 26]``)
* ``timestamp`` (e.g. ``1307232000``)

The value that comes back from the form will also be normalized back into
this format.

input_format
~~~~~~~~~~~~

**type**: ``string`` **default**: ``H:i:s``

If the ``input`` option is set to ``string``, this option specifies the format
of the time. This must be a valid `PHP time format`_.

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. caution::

    When using different values for ``model_timezone`` and `view_timezone`_,
    a `reference_date`_ must be configured.

reference_date
~~~~~~~~~~~~~~

**type**: ``DateTimeInterface`` **default**: ``null``

Configuring a reference date is required when the `model_timezone`_ and
`view_timezone`_ are different. Timezone conversions will be calculated
based on this date.

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

When no `reference_date`_ is set the ``view_timezone`` defaults to the
configured `model_timezone`_.

.. caution::

    When using different values for `model_timezone`_ and ``view_timezone``,
    a `reference_date`_ must be configured.

widget
~~~~~~

**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the
following:

* ``choice``: renders one, two (default) or three select inputs (hour, minute,
  second), depending on the `with_minutes`_ and `with_seconds`_ options.

* ``text``: renders one, two (default) or three text inputs (hour, minute,
  second), depending on the `with_minutes`_ and `with_seconds`_ options.

* ``single_text``: renders a single input of type ``time``. User's input
  will be validated against the form ``hh:mm`` (or ``hh:mm:ss`` if using
  seconds).

.. caution::

    Combining the widget type ``single_text`` and the `with_minutes`_ option
    set to ``false`` can cause unexpected behavior in the client as the
    input type ``time`` might not support selecting an hour only.

.. include:: /reference/forms/types/options/with_minutes.rst.inc

.. include:: /reference/forms/types/options/with_seconds.rst.inc

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

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Form Variables
--------------

+--------------+-------------+----------------------------------------------------------------------+
| Variable     | Type        | Usage                                                                |
+==============+=============+======================================================================+
| widget       | ``mixed``   | The value of the `widget`_ option.                                   |
+--------------+-------------+----------------------------------------------------------------------+
| with_minutes | ``boolean`` | The value of the `with_minutes`_ option.                             |
+--------------+-------------+----------------------------------------------------------------------+
| with_seconds | ``boolean`` | The value of the `with_seconds`_ option.                             |
+--------------+-------------+----------------------------------------------------------------------+
| type         | ``string``  | Only present when widget is ``single_text`` and HTML5 is activated,  |
|              |             | contains the input type to use (``datetime``, ``date`` or ``time``). |
+--------------+-------------+----------------------------------------------------------------------+

.. _`PHP time format`: https://php.net/manual/en/function.date.php
