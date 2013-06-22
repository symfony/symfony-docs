.. index::
   single: Forms; Fields; password

password Field Type
===================

The ``password`` field renders an input password text box.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``password`` field                                           |
+-------------+------------------------------------------------------------------------+
| Options     | - `always_empty`_                                                      |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `max_length`_                                                        |
| options     | - `required`_                                                          |
|             | - `label`_                                                             |
|             | - `trim`_                                                              |
|             | - `read_only`_                                                         |
|             | - `disabled`_                                                          |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `mapped`_                                                            |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`text</reference/forms/types/text>`                               |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType` |
+-------------+------------------------------------------------------------------------+

Field Options
-------------

always_empty
~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If set to true, the field will *always* render blank, even if the corresponding
field has a value. When set to false, the password field will be rendered
with the ``value`` attribute set to its true value.

Put simply, if for some reason you want to render your password field
*with* the password value already entered into the box, set this to false.

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
