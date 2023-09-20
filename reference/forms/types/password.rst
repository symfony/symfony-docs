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

``hash_property_path``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

.. versionadded:: 6.2

    The ``hash_property_path`` option was introduced in Symfony 6.2.

If set, the password will be hashed using the
:doc:`PasswordHasher component </security/passwords>` and stored in the
property defined by the given :doc:`PropertyAccess expression </components/property_access>`.

Data passed to the form must be a
:class:`Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface`
object.

.. caution::

    To minimize the risk of leaking the plain password, this option can
    only be used with the :ref:`"mapped" option <reference-form-password-mapped>`
    set to ``false``::

        $builder->add('plainPassword', PasswordType::class, [
            'hash_property_path' => 'password',
            'mapped' => false,
        ]);

    or if you want to use it with the ``RepeatedType``::

        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options'  => ['label' => 'Password', 'hash_property_path' => 'password'],
            'second_options' => ['label' => 'Repeat Password'],
            'mapped' => false,
        ]);

``toggle``
~~~~~~~~~~
**type**: ``boolean`` **requires**: `symfony/ux-toggle-password`_

Adds "Show"/"Hide" links to the field which toggle the password field to plaintext when clicked.
See `symfony/ux-toggle-password`_ for more details.

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

.. _reference-form-password-mapped:

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`symfony/ux-toggle-password`: https://symfony.com/bundles/ux-toggle-password/current/index.html
