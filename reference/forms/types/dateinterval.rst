.. index::
   single: Forms; Fields; DateIntervalType

DateIntervalType Field
======================

This field allows the user to select an *interval* of time. For example, if you want to
allow the user to choose *how often* they receive a status email, they could use this
field to choose intervals like every "10 minutes" or "3 days".

The field can be rendered in a variety of different ways (see `widget`_) and can be configured to
give you a ``DateInterval`` object, an `ISO 8601`_ duration string (e.g. ``P1DT12H``)
or an array (see `input`_).

+---------------------------+----------------------------------------------------------------------------------+
| Underlying Data Type      | can be ``DateInterval``, string or array (see the ``input`` option)              |
+---------------------------+----------------------------------------------------------------------------------+
| Rendered as               | single text box, multiple text boxes or select fields - see the `widget`_ option |
+---------------------------+----------------------------------------------------------------------------------+
| Options                   | - `days`_                                                                        |
|                           | - `hours`_                                                                       |
|                           | - `minutes`_                                                                     |
|                           | - `months`_                                                                      |
|                           | - `seconds`_                                                                     |
|                           | - `weeks`_                                                                       |
|                           | - `input`_                                                                       |
|                           | - `labels`_                                                                      |
|                           | - `placeholder`_                                                                 |
|                           | - `widget`_                                                                      |
|                           | - `with_days`_                                                                   |
|                           | - `with_hours`_                                                                  |
|                           | - `with_invert`_                                                                 |
|                           | - `with_minutes`_                                                                |
|                           | - `with_months`_                                                                 |
|                           | - `with_seconds`_                                                                |
|                           | - `with_weeks`_                                                                  |
|                           | - `with_years`_                                                                  |
|                           | - `years`_                                                                       |
+---------------------------+----------------------------------------------------------------------------------+
| Overridden options        | - `invalid_message`_                                                             |
+---------------------------+----------------------------------------------------------------------------------+
| Inherited options         | - `attr`_                                                                        |
|                           | - `data`_                                                                        |
|                           | - `disabled`_                                                                    |
|                           | - `help`_                                                                        |
|                           | - `help_attr`_                                                                   |
|                           | - `help_html`_                                                                   |
|                           | - `inherit_data`_                                                                |
|                           | - `invalid_message_parameters`_                                                  |
|                           | - `mapped`_                                                                      |
|                           | - `row_attr`_                                                                    |
+---------------------------+----------------------------------------------------------------------------------+
| Default invalid message   | Please choose a valid date interval.                                             |
+---------------------------+----------------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                              |
+---------------------------+----------------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                                    |
+---------------------------+----------------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateIntervalType`       |
+---------------------------+----------------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Basic Usage
-----------

This field type is highly configurable. The most important options are `input`_ and `widget`_.

You can configure *a lot* of different options, including exactly *which* range
options to show (e.g. don't show "months", but *do* show "days")::

    $builder->add('remindEvery', DateIntervalType::class, [
        'widget'      => 'integer', // render a text field for each part
        // 'input'    => 'string',  // if you want the field to return a ISO 8601 string back to you

        // customize which text boxes are shown
        'with_years'  => false,
        'with_months' => false,
        'with_days'   => true,
        'with_hours'  => true,
    ]);

Field Options
-------------

``days``
~~~~~~~~

**type**: ``array`` **default**: 0 to 31

List of days available to the days field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 30 (both inclusive)
    'days' => range(1, 31),

    // values displayed to users range from 1 to 31 (both inclusive)
    'days' => array_combine(range(1, 31), range(1, 31)),

``placeholder``
~~~~~~~~~~~~~~~

**type**: ``string`` or ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. The ``placeholder`` option can be used to
add a "blank" entry to the top of each select box::

    $builder->add('remindEvery', DateIntervalType::class, [
        'placeholder' => '',
    ]);

Alternatively, you can specify a string to be displayed for the "blank" value::

    $builder->add('remindEvery', DateIntervalType::class, [
        'placeholder' => ['years' => 'Years', 'months' => 'Months', 'days' => 'Days']
    ]);

``hours``
~~~~~~~~~

**type**: ``array`` **default**: 0 to 24

List of hours available to the hours field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 23 (both inclusive)
    'hours' => range(1, 24),

    // values displayed to users range from 1 to 24 (both inclusive)
    'hours' => array_combine(range(1, 24), range(1, 24)),

``input``
~~~~~~~~~

**type**: ``string`` **default**: ``dateinterval``

The format of the *input* data - i.e. the format that the interval is stored on
your underlying object. Valid values are:

* ``string`` (a string formatted with `ISO 8601`_ standard, e.g. ``P7Y6M5DT12H15M30S``)
* ``dateinterval`` (a ``DateInterval`` object)
* ``array`` (e.g. ``['days' => '1', 'hours' => '12',]``)

The value that comes back from the form will also be normalized back into
this format.

``labels``
~~~~~~~~~~

**type**: ``array`` **default**: (see below)

The labels displayed for each of the elements of this type. The default values
are ``null``, so they display the "humanized version" of the child names (``Invert``,
``Years``, etc.)::

    'labels' => [
        'invert' => null,
        'years' => null,
        'months' => null,
        'weeks' => null,
        'days' => null,
        'hours' => null,
        'minutes' => null,
        'seconds' => null,
    ]

``minutes``
~~~~~~~~~~~

**type**: ``array`` **default**: 0 to 60

List of minutes available to the minutes field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 59 (both inclusive)
    'minutes' => range(1, 60),

    // values displayed to users range from 1 to 60 (both inclusive)
    'minutes' => array_combine(range(1, 60), range(1, 60)),

``months``
~~~~~~~~~~

**type**: ``array`` **default**: 0 to 12

List of months available to the months field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 11 (both inclusive)
    'months' => range(1, 12),

    // values displayed to users range from 1 to 12 (both inclusive)
    'months' => array_combine(range(1, 12), range(1, 12)),

``seconds``
~~~~~~~~~~~

**type**: ``array`` **default**: 0 to 60

List of seconds available to the seconds field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 59 (both inclusive)
    'seconds' => range(1, 60),

    // values displayed to users range from 1 to 60 (both inclusive)
    'seconds' => array_combine(range(1, 60), range(1, 60)),

``weeks``
~~~~~~~~~

**type**: ``array`` **default**: 0 to 52

List of weeks available to the weeks field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 51 (both inclusive)
    'weeks' => range(1, 52),

    // values displayed to users range from 1 to 52 (both inclusive)
    'weeks' => array_combine(range(1, 52), range(1, 52)),

``widget``
~~~~~~~~~~

**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the
following:

* ``choice``: renders one to six select inputs for years, months, weeks, days,
  hours, minutes and/or seconds, depending on the `with_years`_, `with_months`_,
  `with_weeks`_, `with_days`_, `with_hours`_, `with_minutes`_ and `with_seconds`_
  options.
  Default: Three fields for years, months and days.

* ``text``: renders one to six text inputs for years, months, weeks, days,
  hours, minutes and/or seconds, depending on the `with_years`_, `with_months`_,
  `with_weeks`_, `with_days`_, `with_hours`_, `with_minutes`_ and `with_seconds`_
  options.
  Default: Three fields for years, months and days.

* ``integer``: renders one to six integer inputs for years, months, weeks, days,
  hours, minutes and/or seconds, depending on the `with_years`_, `with_months`_,
  `with_weeks`_, `with_days`_, `with_hours`_, `with_minutes`_ and `with_seconds`_
  options.
  Default: Three fields for years, months and days.

* ``single_text``: renders a single input of type ``text``. User's input
  will be validated against the form ``PnYnMnDTnHnMnS`` (or ``PnW`` if using
  only weeks).

``with_days``
~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

Whether or not to include days in the input. This will result in an additional
input to capture days.

.. caution::

    This can not be used when `with_weeks`_ is enabled.

``with_hours``
~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

Whether or not to include hours in the input. This will result in an additional
input to capture hours.

``with_invert``
~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

Whether or not to include invert in the input. This will result in an additional
checkbox.
This can not be used when the `widget`_ option is set to ``single_text``.

``with_minutes``
~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

Whether or not to include minutes in the input. This will result in an additional
input to capture minutes.

``with_months``
~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

Whether or not to include months in the input. This will result in an additional
input to capture months.

``with_seconds``
~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

Whether or not to include seconds in the input. This will result in an additional
input to capture seconds.

``with_weeks``
~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``false``

Whether or not to include weeks in the input. This will result in an additional
input to capture weeks.

.. caution::

    This can not be used when `with_days`_ is enabled.

``with_years``
~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

Whether or not to include years in the input. This will result in an additional
input to capture years.

``years``
~~~~~~~~~

**type**: ``array`` **default**: 0 to 100

List of years available to the years field type. This option is only relevant
when the ``widget`` option is set to ``choice``::

    // values displayed to users range from 0 to 99 (both inclusive)
    'years' => range(1, 100),

    // values displayed to users range from 1 to 100 (both inclusive)
    'years' => array_combine(range(1, 100), range(1, 100)),

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

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

================  ===========  ========================================
Variable          Type         Usage
================  ===========  ========================================
``widget``        ``mixed``    The value of the `widget`_ option.
``with_days``     ``Boolean``  The value of the `with_days`_ option.
``with_invert``   ``Boolean``  The value of the `with_invert`_ option.
``with_hours``    ``Boolean``  The value of the `with_hours`_ option.
``with_minutes``  ``Boolean``  The value of the `with_minutes`_ option.
``with_months``   ``Boolean``  The value of the `with_months`_ option.
``with_seconds``  ``Boolean``  The value of the `with_seconds`_ option.
``with_weeks``    ``Boolean``  The value of the `with_weeks`_ option.
``with_years``    ``Boolean``  The value of the `with_years`_ option.
================  ===========  ========================================

.. _`ISO 8601`: https://en.wikipedia.org/wiki/ISO_8601
