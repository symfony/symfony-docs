.. index::
   single: Forms; Fields; radio

radio Field Type
================

Creates a single radio button. This should always be used for a field that
has a Boolean value: if the radio button is selected, the field will be set
to true, if the button is not selected, the value will be set to false.

The ``radio`` type isn't usually used directly. More commonly it's used
internally by other types such as :doc:`choice</reference/forms/types/choice>`.
If you want to have a Boolean field, use :doc:`checkbox</reference/forms/types/checkbox>`.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                            |
+-------------+---------------------------------------------------------------------+
| Options     | - `value`_                                                          |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `required`_                                                       |
| options     | - `label`_                                                          |
|             | - `read_only`_                                                      |
|             | - `error_bubbling`_                                                 |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                          |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RadioType` |
+-------------+---------------------------------------------------------------------+

Field Options
-------------

value
~~~~~

**type**: ``mixed`` **default**: ``1``

The value that's actually used as the value for the radio button. This does
not affect the value that's set on your object.

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
