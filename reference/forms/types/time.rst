.. index::
   single: Forms; Fields; time

time Field Type
===============

A field to capture time input.

This can be rendered as a text field or a series of choice fields. The underlying
data can be stored as a ``DateTime`` object, a string, a timestamp or an
array.

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | can be various tags (see below)                                             |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - ``widget``                                                                |
|                      | - ``input``                                                                 |
|                      | - ``with_seconds``                                                          |
|                      | - ``hours``                                                                 |
|                      | - ``minutes``                                                               |
|                      | - ``seconds``                                                               |
|                      | - ``data_timezone``                                                         |
|                      | - ``user_timezone``                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | form                                                                        |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType`          |
+----------------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

This field type is highly configurable, but easy to use. The most important
options are ``input`` and ``widget``.

Suppose that you have a ``startTime`` field whose underlying time data is a
``DateTime`` object. The following configures the ``time`` type for that
field as three different choice fields:

.. code-block:: php

    $builder->add('startTime', 'date', array(
        'input'  => 'datetime',
        'widget' => 'choice',
    ));

The ``input`` option *must* be changed to match the type of the underlying
date data. For example, if the ``startTime`` field's data were a unix timestamp,
you'd need to set ``input`` to ``timestamp``:

.. code-block:: php

    $builder->add('startTime', 'date', array(
        'input'  => 'datetime',
        'widget' => 'choice',
    ));

The field also supports an ``array`` and ``string`` as valid ``input`` option
values.

Options
-------

*   ``widget`` [type: string, default: ``choice``]
    Type of widget used for this form type.  Can be ``text`` or ``choice``.  
    
      * ``text``: renders a single input of type text.  User's input is validated based on the ``format`` option.
      * ``choice``: renders two select inputs (three select inputs if ``with_seconds`` is set to ``true``).

*   ``input`` [type: string, default: ``datetime``]
    The value of the input for the widget.  Can be ``string``, ``datetime`` or ``array``.  The form type input value will be returned 
    in the format specified.  The value "12:30" with the ``input`` option set to ``array`` would return:
    
    .. code-block:: php

        array('hour' => '12', 'minute' => '30' )

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
