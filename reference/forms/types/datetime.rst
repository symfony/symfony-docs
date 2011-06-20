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
| Options              | - ``date_widget``                                                           |
|                      | - ``time_widget``                                                           |
|                      | - ``input``                                                                 |
|                      | - ``years``                                                                 |
|                      | - ``months``                                                                |
|                      | - ``days``                                                                  |
|                      | - ``format``                                                                |
|                      | - ``pattern``                                                               |
|                      | - ``data_timezone``                                                         |
|                      | - ``user_timezone``                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`form</reference/forms/types/form>`                                    |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DatetimeType`      |
+----------------------+-----------------------------------------------------------------------------+

Options
-------

*   ``date_widget`` [type: string, default: choice]
    Defines the ``widget`` option for the :doc:`date</reference/forms/types/date>` type

*   ``time_widget`` [type: string, default: choice]
    Defines the ``widget`` option for the :doc:`time</reference/forms/types/time>` type

*   ``input`` [type: string, default: ``datetime``]
    The value of the input for the widget.  Can be ``string``, ``datetime``
    or ``array``.  The form type input value will be returned in the format
    specified.  The input of ``April 21th, 2011 18:15:30`` as an array would return:

    .. code-block:: php

        array('month' => 4, 'day' => 21, 'year' => 2011, 'hour' => 18, 'minute' => 15, 'second' => 30)

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
