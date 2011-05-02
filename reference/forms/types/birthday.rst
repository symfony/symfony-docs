.. index::
   single: Forms; Fields; birthday

birthday Field Type
===================

A special :doc:`date</reference/forms/types/date>` field that specializes
in handling birthdate data.

Can be rendered as a single text box or three select boxes (month, day, and year)

This type is essentially the same as the ``date`` type, but with a more appropriate
default for the ``years`` option.   The ``years`` option defaults to 120
years ago to the current year.

+----------------------+------------------------------------------------------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, ``string``, ``timestamp``, or ``array`` (see the :ref:`input option <form-reference-date-input>`) |
+----------------------+------------------------------------------------------------------------------------------------------------------------+
| Rendered as          | can be three select boxes or a text box, based on the ``widget`` option                                                |
+----------------------+------------------------------------------------------------------------------------------------------------------------+
| Options              | - ``widget``                                                                                                           |
|                      | - ``input``                                                                                                            |
|                      | - ``years``                                                                                                            |
|                      | - ``months``                                                                                                           |
|                      | - ``days``                                                                                                             |
|                      | - ``format``                                                                                                           |
|                      | - ``pattern``                                                                                                          |
|                      | - ``data_timezone``                                                                                                    |
|                      | - ``user_timezone``                                                                                                    |
+----------------------+------------------------------------------------------------------------------------------------------------------------+
| Parent type          | :doc:`date</reference/forms/types/date>`                                                                               |
+----------------------+------------------------------------------------------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType`                                                 |
+----------------------+------------------------------------------------------------------------------------------------------------------------+

Options
-------

* ``widget`` [type: string, default: ``choice``]
    Type of widget used for this form type.  Can be ``text`` or ``choice``.  
    
      * ``text``: renders a single input of type text. User's input is validated
        based on the ``format`` option.

      * ``choice``: renders three select inputs.  The order of the selects
        is defined in the ``pattern`` option.
    
* ``input`` [type: string, default: datetime]
    The value of the input for the widget.  Can be ``string``, ``datetime``
    or ``array``.  The form type input value will be returned  in the format
    specified.  The input of April 21th, 2011 as an array would return:
    
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
