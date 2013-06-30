.. index::
   single: Forms; Fields; birthday

birthday Field Type
===================

A :doc:`date</reference/forms/types/date>` field that specializes in handling
birthdate data.

Can be rendered as a single text box, three text boxes (month, day, and year),
or three select boxes.

This type is essentially the same as the :doc:`date</reference/forms/types/date>`
type, but with a more appropriate default for the `years`_ option. The `years`_
option defaults to 120 years ago to the current year.

+----------------------+-------------------------------------------------------------------------------+
| Underlying Data Type | can be ``DateTime``, ``string``, ``timestamp``, or ``array``                  |
|                      | (see the :ref:`input option <form-reference-date-input>`)                     |
+----------------------+-------------------------------------------------------------------------------+
| Rendered as          | can be three select boxes or 1 or 3 text boxes, based on the `widget`_ option |
+----------------------+-------------------------------------------------------------------------------+
| Overridden Options   | - `years`_                                                                    |
+----------------------+-------------------------------------------------------------------------------+
| Inherited Options    | - `widget`_                                                                   |
|                      | - `input`_                                                                    |
|                      | - `months`_                                                                   |
|                      | - `days`_                                                                     |
|                      | - `format`_                                                                   |
|                      | - `model_timezone`_                                                           |
|                      | - `view_timezone`_                                                            |
|                      | - `invalid_message`_                                                          |
|                      | - `invalid_message_parameters`_                                               |
|                      | - `read_only`_                                                                |
|                      | - `disabled`_                                                                 |
|                      | - `mapped`_                                                                   |
|                      | - `inherit_data`_                                                             |
+----------------------+-------------------------------------------------------------------------------+
| Parent type          | :doc:`date</reference/forms/types/date>`                                      |
+----------------------+-------------------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType`        |
+----------------------+-------------------------------------------------------------------------------+

Overridden Options
------------------

years
~~~~~

**type**: ``array`` **default**: 120 years ago to the current year

List of years available to the year field type.  This option is only
relevant when the ``widget`` option is set to ``choice``.

Inherited options
-----------------

These options inherit from the :doc:`date</reference/forms/types/date>` type:

.. include:: /reference/forms/types/options/date_widget.rst.inc
    
.. include:: /reference/forms/types/options/date_input.rst.inc

.. include:: /reference/forms/types/options/months.rst.inc

.. include:: /reference/forms/types/options/days.rst.inc

.. include:: /reference/forms/types/options/date_format.rst.inc

.. include:: /reference/forms/types/options/model_timezone.rst.inc

.. include:: /reference/forms/types/options/view_timezone.rst.inc

These options inherit from the :doc:`date</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc
