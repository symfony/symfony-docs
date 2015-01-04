.. index::
   single: Forms; Fields; date

date Field Type
===============

A field that allows the user to modify date information via a variety of
different HTML elements.

The underlying data used for this field type can be a ``DateTime`` object,
a string, a timestamp or an array. As long as the `input`_ option is set
correctly, the field will take care of all of the details.

The field can be rendered as a single text box, three text boxes (month,
day, and year) or three select boxes (see the `widget`_ option).

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - `days`_                                                                   |
|                      | - `placeholder`_                                                            |
|                      | - `format`_                                                                 |
|                      | - `html5`_                                                                  |
|                      | - `input`_                                                                  |
|                      | - `model_timezone`_                                                         |
|                      | - `months`_                                                                 |
|                      | - `view_timezone`_                                                          |
|                      | - `widget`_                                                                 |
|                      | - `years`_                                                                  |
+----------------------+-----------------------------------------------------------------------------+
| Overridden Options   | - `by_reference`_                                                           |
|                      | - `error_bubbling`_                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Inherited            | - `data`_                                                                   |
| options              | - `disabled`_                                                               |
|                      | - `error_mapping`_                                                          |
|                      | - `inherit_data`_                                                           |
|                      | - `invalid_message`_                                                        |
|                      | - `invalid_message_parameters`_                                             |
|                      | - `mapped`_                                                                 |
|                      | - `read_only`_                                                              |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | :doc:`form </reference/forms/types/form>`                                   |
+----------------------+-----------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType`          |
+----------------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

This field type is highly configurable, but easy to use. The most important
options are ``input`` and ``widget``.

Suppose that you have a ``publishedAt`` field whose underlying date is a
``DateTime`` object. The following configures the ``date`` type for that
field as three different choice fields:

.. code-block:: php

    $builder->add('publishedAt', 'date', array(
        'input'  => 'datetime',
        'widget' => 'choice',
    ));

The ``input`` option *must* be changed to match the type of the underlying
date data. For example, if the ``publishedAt`` field's data were a unix timestamp,
you'd need to set ``input`` to ``timestamp``:

.. code-block:: php

    $builder->add('publishedAt', 'date', array(
        'input'  => 'timestamp',
        'widget' => 'choice',
    ));

The field also supports an ``array`` and ``string`` as valid ``input`` option
values.

Field Options
-------------

.. include:: /reference/forms/types/options/days.rst.inc

placeholder
~~~~~~~~~~~

.. versionadded:: 2.6
    The ``placeholder`` option was introduced in Symfony 2.6 in favor of
    ``empty_value``, which is available prior to 2.6.

**type**: ``string`` or ``array``

If your widget option is set to ``choice``, then this field will be represented
as a series of ``select`` boxes. The ``placeholder`` option can be used to
add a "blank" entry to the top of each select box::

    $builder->add('dueDate', 'date', array(
        'placeholder' => '',
    ));

Alternatively, you can specify a string to be displayed for the "blank" value::

    $builder->add('dueDate', 'date', array(
        'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day')
    ));

.. _reference-forms-type-date-format:

.. include:: /reference/forms/types/options/date_format.rst.inc

.. include:: /reference/forms/types/options/html5.rst.inc

.. _form-reference-date-input:

.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

.. include:: /reference/forms/types/options/date_widget.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

Overridden Options
------------------

by_reference
~~~~~~~~~~~~

**default**: ``false``

The ``DateTime`` classes are treated as immutable objects.

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

Field Variables
---------------

+--------------+------------+----------------------------------------------------------------------+
| Variable     | Type       | Usage                                                                |
+==============+============+======================================================================+
| widget       | ``mixed``  | The value of the `widget`_ option.                                   |
+--------------+------------+----------------------------------------------------------------------+
| type         | ``string`` | Only present when widget is ``single_text`` and HTML5 is activated,  |
|              |            | contains the input type to use (``datetime``, ``date`` or ``time``). |
+--------------+------------+----------------------------------------------------------------------+
| date_pattern | ``string`` | A string with the date format to use.                                |
+--------------+------------+----------------------------------------------------------------------+
