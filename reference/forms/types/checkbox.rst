.. index::
   single: Forms; Fields; checkbox

checkbox Field Type
===================

Creates a single input checkbox. This should always be used for a field that
has a Boolean value: if the box is checked, the field will be set to true,
if the box is unchecked, the value will be set to false.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                               |
+-------------+------------------------------------------------------------------------+
| Options     | - ``value``                                                            |
+-------------+------------------------------------------------------------------------+
| Inherited   | - ``required``                                                         |
| options     | - ``label``                                                            |
|             | - ``read_only``                                                        |
|             | - ``error_bubbling``                                                   |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                             |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType` |
+-------------+------------------------------------------------------------------------+

Example Usage
-------------

.. code-block:: php

    $builder->add('public', 'checkbox', array(
        'label'     => 'Show this entry publicly?',
        'required'  => false,
    ));

Options
-------

*   ``value`` [type: mixed, default: 1]
    The value that's actually used as the value for the checkbox. This does
    not affect the value that's set on your object.

Inherited options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
