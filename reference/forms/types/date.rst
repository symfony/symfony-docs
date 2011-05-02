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
        'input'  => 'datetime',
        'widget' => 'choice',
    ));

The field also supports an ``array`` and ``string`` as valid ``input`` option
values.

Options
-------

* ``widget`` [type: string, default: ``choice``]
    Type of widget used for this form type.  Can be ``text`` or ``choice``.  
    
      * ``text``: renders a single input of type text. User's input is validated
        based on the ``format`` option.

      * ``choice``: renders three select inputs.  The order of the selects
        is defined in the ``pattern`` option.

.. _form-reference-date-input:

* ``input`` [type: string, default: ``datetime``]
    The value of the input for the widget.  Can be ``string``, ``datetime``
    or ``array``.  The form type input value will be returned  in the format
    specified.  The input of ``April 21th, 2011`` as an array would return:
    
    .. code-block:: php

        array('month' => 4, 'day' => 21, 'year' => 2011 )

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

* ``format`` [type: integer, default: IntlDateFormatter::MEDIUM]
    Option passed to the IntlDateFormatter class, used to transform user input
    into the proper format. This is critical when the ``widget`` option is
    set to ``text``, and will define how to transform the input.
    
* ``pattern`` [type: string, default: null]
    This option is only relevant when the ``widget`` is set to ``choice``.
    The default pattern is based off the ``format`` option, and tries to
    match the characters ``M``, ``d``, and ``y`` in the format pattern. If
    no match is found, the default is the string ``{{ year }}-{{ month }}-{{ day }}``.
    Tokens for this option include:

      * ``{{ year }}``: Replaced with the ``year`` widget
      * ``{{ month }}``: Replaced with the ``month`` widget
      * ``{{ day }}``: Replaced with the ``day`` widget

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
