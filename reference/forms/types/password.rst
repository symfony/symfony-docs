PasswordType Field
==================

The ``PasswordType`` field renders an input password text box.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | ``input`` ``password`` field                                           |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | The password is invalid.                                               |
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

If set, the password will be hashed using the
:doc:`PasswordHasher component </security/passwords>` and stored in the
property defined by the given :doc:`PropertyAccess expression </components/property_access>`.

Data passed to the form must be a
:class:`Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface`
object.

The password is hashed in the ``post_validate`` event of the field, and only if
the whole form is valid. This event is dispatched by the Validator extension of
the Form component, which is enabled by default in Symfony applications. In
:ref:`standalone applications <forms-standalone-validation>`, add the
:class:`Symfony\\Component\\Form\\Extension\\Validator\\ValidatorExtension` to
your form factory. Without it, the password is never hashed.

.. versionadded:: 8.2

    Hashing the password in the ``post_validate`` event was introduced in
    Symfony 8.2. In previous versions, it was hashed in the ``post_submit``
    event of the root form, so it worked without the Validator extension.

.. warning::

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

.. deprecated:: 2.29

    The ``ux-toggle-password`` component has been deprecated in Symfony UX
    2.29 and will be removed in 3.0. See `symfony/ux-toggle-password`_ for
    migration.

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

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. _reference-form-password-mapped:

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`symfony/ux-toggle-password`: https://symfony.com/bundles/ux-toggle-password/current/index.html
