.. index::
   single: Forms; Fields; time

``time`` Field Type
===================

A field to capture time input.
Can be rendered as text inputs or selects.

============  ======
Rendered as   can be various tags (see below)
Options       ``hours`` ``minutes`` ``seconds`` ``widget`` ``input`` ``with_seconds`` ``data_timezone`` ``user_timezone``
Parent type   :doc:`form</reference/forms/types/field>` (if text), ``form`` otherwise
Class         :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimeType`
============  ======

.. include:: /reference/forms/types/options/hours.rst.inc

.. include:: /reference/forms/types/options/minutes.rst.inc

.. include:: /reference/forms/types/options/seconds.rst.inc

* ``widget`` [type: string, default: choice]
    Type of widget used for this form type.  Can be ``text`` or ``choice``.  
    
      * ``text``: renders a single input of type text.  User's input is validated based on the ``format`` option.
      * ``choice``: renders two select inputs (three select inputs if ``with_seconds`` is set to ``true``).

* ``input`` [type: string, default: datetime]
    The value of the input for the widget.  Can be ``string``, ``datetime`` or ``array``.  The form type input value will be returned 
    in the format specified.  The value "12:30" with the ``input`` option set to ``array`` would return:
    
    .. code-block:: php

        array('hour' => '12', 'minute' => '30' )

.. include:: /reference/forms/types/options/with_seconds.rst.inc

.. include:: /reference/forms/types/options/data_timezone.rst.inc

.. include:: /reference/forms/types/options/user_timezone.rst.inc
