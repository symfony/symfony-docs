PasswordType Field
==================

The ``PasswordType`` field renders an input password text box.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | ``input`` ``password`` field                                           |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | The password is invalid.                                               |
+---------------------------+------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                    |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`TextType </reference/forms/types/text>`                          |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

``always_empty``
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to true, the field will *always* render blank, even if the corresponding
field has a value. When set to false, the password field will be rendered
with the ``value`` attribute set to its true value only upon submission.

If you want to render your password field *with* the password value already
entered into the box, set this to false and submit the form.

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

``trim``
~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Unlike the rest of form types, the ``PasswordType`` doesn't apply the
:phpfunction:`trim` function to the value submitted by the user. This ensures that
the password is merged back onto the underlying object exactly as it was typed
by the user.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
