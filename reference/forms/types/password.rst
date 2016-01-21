.. index::
   single: Forms; Fields; PasswordType

PasswordType Field
==================

The ``PasswordType`` field renders an input password text box.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``password`` field                                           |
+-------------+------------------------------------------------------------------------+
| Options     | - `always_empty`_                                                      |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `trim`_                                                              |
| options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `disabled`_                                                          |
| options     | - `empty_data`_                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `mapped`_                                                            |
|             | - `max_length`_ (deprecated as of 2.5)                                 |
|             | - `read_only`_ (deprecated as of 2.8)                                  |
|             | - `required`_                                                          |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                          |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType` |
+-------------+------------------------------------------------------------------------+

Field Options
-------------

always_empty
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to true, the field will *always* render blank, even if the corresponding
field has a value. When set to false, the password field will be rendered
with the ``value`` attribute set to its true value only upon submission.

Put simply, if for some reason you want to render your password field
*with* the password value already entered into the box, set this to false
and submit the form.

Overridden Options
------------------

trim
~~~~

**type**: ``boolean`` **default**: ``false``

Unlike the rest of form types, the ``PasswordType`` doesn't apply the
:phpfunction:`trim` function to the value submitted by the user. This ensures that
the password is merged back onto the underlying object exactly as it was typed
by the user.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
