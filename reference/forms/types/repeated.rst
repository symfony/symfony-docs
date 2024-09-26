RepeatedType Field
==================

This is a special field "group", that creates two identical fields whose
values must match (or a validation error is thrown). The most common use
is when you need the user to repeat their password or email to verify
accuracy.

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | input ``text`` field by default, but see `type`_ option                |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | The values do not match.                                               |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                          |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
    // ...

    $builder->add('password', RepeatedType::class, [
        'type' => PasswordType::class,
        'invalid_message' => 'The password fields must match.',
        'options' => ['attr' => ['class' => 'password-field']],
        'required' => true,
        'first_options'  => ['label' => 'Password'],
        'second_options' => ['label' => 'Repeat Password'],
    ]);

Upon a successful form submit, the value entered into both of the "password"
fields becomes the data of the ``password`` key. In other words, even though
two fields are actually rendered, the end data from the form is just the
single value (usually a string) that you need.

The most important option is ``type``, which can be any field type and determines
the actual type of the two underlying fields. The ``options`` option is
passed to each of those individual fields, meaning - in this example - any
option supported by the ``PasswordType`` can be passed in this array.

Rendering
~~~~~~~~~

The repeated field type is actually two underlying fields, which you can
render all at once, or individually. To render all at once, use something
like:

.. code-block:: twig

    {{ form_row(form.password) }}

To render each field individually, use something like this:

.. code-block:: twig

    {# .first and .second may vary in your use - see the note below #}
    {{ form_row(form.password.first) }}
    {{ form_row(form.password.second) }}

.. note::

    The names ``first`` and ``second`` are the default names for the two
    sub-fields. However, these names can be controlled via the `first_name`_
    and `second_name`_ options. If you've set these options, then use those
    values instead of ``first`` and ``second`` when rendering.

Validation
~~~~~~~~~~

One of the key features of the ``repeated`` field is internal validation
(you don't need to do anything to set this up) that forces the two fields
to have a matching value. If the two fields don't match, an error will be
shown to the user.

The ``invalid_message`` is used to customize the error that will
be displayed when the two fields do not match each other.

.. note::

    The ``mapped`` option is always ``true`` for both fields in order for the type
    to work properly.

Field Options
-------------

``first_name``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``first``

This is the actual field name to be used for the first field. This is mostly
meaningless, however, as the actual data entered into both of the fields
will be available under the key assigned to the ``RepeatedType`` field itself
(e.g.  ``password``). However, if you don't specify a label, this field
name is used to "guess" the label for you.

``first_options``
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (will be merged into `options`_ below) that should be
passed *only* to the first field. This is especially useful for customizing
the label::

    use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
    // ...

    $builder->add('password', RepeatedType::class, [
        'first_options'  => ['label' => 'Password'],
        'second_options' => ['label' => 'Repeat Password'],
    ]);

``options``
~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

This options array will be passed to each of the two underlying fields.
In other words, these are the options that customize the individual field
types. For example, if the ``type`` option is set to ``password``, this
array might contain the options ``always_empty`` or ``required`` - both
options that are supported by the ``PasswordType`` field.

``second_name``
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``second``

The same as ``first_name``, but for the second field.

``second_options``
~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Additional options (will be merged into `options`_ above) that should be
passed *only* to the second field. This is especially useful for customizing
the label (see `first_options`_).

``type``
~~~~~~~~

**type**: ``string`` **default**: ``Symfony\Component\Form\Extension\Core\Type\TextType``

The two underlying fields will be of this field type. For example, passing
``PasswordType::class`` will render two password fields.

Overridden Options
------------------

``error_bubbling``
~~~~~~~~~~~~~~~~~~

**default**: ``false``

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
