.. index::
   single: Forms; Fields; date

``date`` Field Type
===================

A field to capture date input.

Can be rendered as a single text box, three text boxes (month, day, and year),
or three select boxes (month, day, and year)

============  ======
Rendered as   can be various tags (see below)
Options       ``years`` ``months`` ``days`` ``widget`` ``input`` ``format`` ``pattern`` ``data_timezone`` ``user_timezone``
Parent type   :doc:`form</reference/forms/types/field>` (if text), ``form`` otherwise
Class         :class:`Symfony\\Component\\Form\\Type\\DateType`
============  ======

Options
-------

.. include:: /reference/forms/types/options/years.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

* ``widget`` [type: string, default: choice]
    Type of widget used for this form type.  Can be ``text``, ``split-text`` or ``choice``.  
    
      * ``text``: renders a single input of type text.  User's input is validated based on the ``format`` option.
      * ``split-text``: renders three inputs of type text.  User's input is validated based on the ``pattern`` option.
      * ``choice``: renders three select inputs.  The order of the selects is defined in the ``pattern`` option.
    
* ``input`` [type: string, default: datetime]
    The value of the input for the widget.  Can be ``string``, ``datetime`` or ``array``.  The form type input value will be returned 
    in the format specified.  The input of April 21th, 2011 as an array would return:
    
    .. code-block:: php

        array('month' => 4, 'day' => 21, 'year' => 2011 )
    
* ``format`` [type: integer, default: IntlDateFormatter::MEDIUM]
    Option passed to the IntlDateFormatter class, used to transform user input into the proper format. This is critical when the ``widget``
    option is set to ``text``, and will define how to transform the input.
    See :class:`IntlDateFormatter`.
    
* ``pattern`` [type: string, default: null]
    This option is only relevant when the ``widget`` is set to ``choide`` or ``split-text``.  The default pattern is based off the ``format`` option, and tries to match the characters ``M``, ``d``, and ``y`` in the format pattern.  If
    no match is found, the default is the string ``{{ year }}-{{ month }}-{{ day }}``.  Tokens for this option include:

      * ``{{ year }}``: Replaced with the ``year`` widget
      * ``{{ month }}``: Replaced with the ``month`` widget
      * ``{{ day }}``: Replaced with the ``day`` widget

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
