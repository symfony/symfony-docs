.. index::
   single: Forms; Fields; datetime

datetime Field Type
===================

This field type allows the user to modify data that represents a specific
date and time (e.g. ``1984-06-05 12:15:30``).

Can be rendered as a text input or select tags. The underlying format of the
data can be a ``DateTime`` object, a string, a timestamp or an array.

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `date_widget`_                                                            |
|                      | - `time_widget`_                                                            |
|                      | - `input`_                                                                  |
|                      | - `date_format`_                                                            |
|                      | - `hours`_                                                                  |
|                      | - `minutes`_                                                                |
|                      | - `seconds`_                                                                |
|                      | - `years`_                                                                  |
|                      | - `months`_                                                                 |
|                      | - `days`_                                                                   |
|                      | - `with_seconds`_                                                           |
|                      | - `data_timezone`_                                                          |
|                      | - `user_timezone`_                                                          |
+----------------------+-----------------------------------------------------------------------------+
| Inherited            | - `invalid_message`_                                                        |
| options              | - `invalid_message_parameters`_                                             |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`form</reference/forms/types/form>`                                    |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateTimeType`      |
+----------------------+-----------------------------------------------------------------------------+

Field Options
-------------

date_widget
~~~~~~~~~~~

**type**: ``string`` **default**: ``choice``

Defines the ``widget`` option for the :doc:`date</reference/forms/types/date>` type

time_widget
~~~~~~~~~~~

**type**: ``string`` **default**: ``choice``

Defines the ``widget`` option for the :doc:`time</reference/forms/types/time>` type

input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored on
your underlying object. Valid values are:

* ``string`` (e.g. ``2011-06-05 12:15:00``)
* ``datetime`` (a ``DateTime`` object)
* ``array`` (e.g. ``array(2011, 06, 05, 12, 15, 0)``)
* ``timestamp`` (e.g. ``1307276100``)

The value that comes back from the form will also be normalized back into
this format.

date_format
~~~~~~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Defines the ``format`` option that will be passed down to the date field.
See the :ref:`date type's format option<reference-forms-type-date-format>`
for more details.

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc

Inherited options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc