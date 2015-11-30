.. index::
   single: Forms; Fields; DateTimeType

DateTimeType Field
==================

This field type allows the user to modify data that represents a specific
date and time (e.g. ``1984-06-05 12:15:30``).

Can be rendered as a text input or select tags. The underlying format of
the data can be a ``DateTime`` object, a string, a timestamp or an array.

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `date_format`_                                                            |
|                      | - `date_widget`_                                                            |
|                      | - `days`_                                                                   |
|                      | - `placeholder`_                                                            |
|                      | - `format`_                                                                 |
|                      | - `hours`_                                                                  |
|                      | - `html5`_                                                                  |
|                      | - `input`_                                                                  |
|                      | - `minutes`_                                                                |
|                      | - `model_timezone`_                                                         |
|                      | - `months`_                                                                 |
|                      | - `seconds`_                                                                |
|                      | - `time_widget`_                                                            |
|                      | - `view_timezone`_                                                          |
|                      | - `widget`_                                                                 |
|                      | - `with_minutes`_                                                           |
|                      | - `with_seconds`_                                                           |
|                      | - `years`_                                                                  |
+----------------------+-----------------------------------------------------------------------------+
| Overridden options   | - `by_reference`_                                                           |
|                      | - `compound`_                                                               |
|                      | - `data_class`_                                                             |
|                      | - `error_bubbling`_                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Inherited            | - `data`_                                                                   |
| options              | - `disabled`_                                                               |
|                      | - `inherit_data`_                                                           |
|                      | - `invalid_message`_                                                        |
|                      | - `invalid_message_parameters`_                                             |
|                      | - `mapped`_                                                                 |
|                      | - `read_only`_ (deprecated as of 2.8)                                       |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`FormType </reference/forms/types/form>`                               |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType`      |
+----------------------+-----------------------------------------------------------------------------+

Field Options
-------------

date_format
~~~~~~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Defines the ``format`` option that will be passed down to the date field.
See the :ref:`DateType's format option <reference-forms-type-date-format>`
for more details.

date_widget
~~~~~~~~~~~

.. include:: /reference/forms/types/options/date_widget_description.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

format
~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\DateTimeType::HTML5_FORMAT``

If the ``widget`` option is set to ``single_text``, this option specifies
the format of the input, i.e. how Symfony will interpret the given input
as a datetime string. It defaults to the `RFC 3339`_ format which is used
by the HTML5 ``datetime`` field. Keeping the default value will cause the
field to be rendered as an ``input`` field with ``type="datetime"``.

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/html5.rst.inc

input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored
on your underlying object. Valid values are:

* ``string`` (e.g. ``2011-06-05 12:15:00``)
* ``datetime`` (a ``DateTime`` object)
* ``array`` (e.g. ``array(2011, 06, 05, 12, 15, 0)``)
* ``timestamp`` (e.g. ``1307276100``)

The value that comes back from the form will also be normalized back into
this format.

.. include:: /reference/forms/types/options/_date_limitation.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

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

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

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

.. _`RFC 3339`: http://tools.ietf.org/html/rfc3339
