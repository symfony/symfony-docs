.. index::
   single: Forms; Fields; date

date Field Type
===============

A field that allows the user to modify date information via a variety of
different HTML elements.

The underlying data used for this field type can be a ``DateTime`` object,
a string, a timestamp or an array. As long as the ``input`` option is set
correctly, the field will take care of all of the details (see the ``input`` option).

The field can be rendered as a single text box or three select boxes (month,
day, and year).

+----------------------+-----------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, string, timestamp, or array (see the ``input`` option) |
+----------------------+-----------------------------------------------------------------------------+
| Rendered as          | single text box or three select fields                                      |
+----------------------+-----------------------------------------------------------------------------+
| Options              | - ``widget``                                                                |
|                      | - ``input``                                                                 |
|                      | - ``years``                                                                 |
|                      | - ``months``                                                                |
|                      | - ``days``                                                                  |
|                      | - ``format``                                                                |
|                      | - ``pattern``                                                               |
|                      | - ``data_timezone``                                                         |
|                      | - ``user_timezone``                                                         |
+----------------------+-----------------------------------------------------------------------------+
| Parent type          | ``field`` (if text), ``form`` otherwise                                     |
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

Options
-------

.. include:: /reference/forms/types/options/date_widget.rst.inc

.. _form-reference-date-input:

.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/date_format.rst.inc

.. include:: /reference/forms/types/options/date_pattern.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
