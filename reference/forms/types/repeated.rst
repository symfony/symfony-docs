.. index::
   single: Forms; Fields; repeated

repeated Field Type
===================

This is a special field "group", that creates two identical fields whose
values must match (or a validation error is thrown). The most common use
is when you need the user to repeat their password or email to verify
accuracy.

+-------------+------------------------------------------------------------------------+
| Rendered as | input ``text`` field by default, but see `type`_ option                |
+-------------+------------------------------------------------------------------------+
| Options     | - `type`_                                                              |
|             | - `options`_                                                           |
|             | - `first_options`_                                                     |
|             | - `second_options`_                                                    |
|             | - `first_name`_                                                        |
|             | - `second_name`_                                                       |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `error_bubbling`_                                                    |
| Options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `data`_                                                              |
| options     | - `invalid_message`_                                                   |
|             | - `invalid_message_parameters`_                                        |
|             | - `mapped`_                                                            |
|             | - `error_mapping`_                                                     |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                              |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType` |
+-------------+------------------------------------------------------------------------+

Example Usage
-------------

.. code-block:: php

    $builder->add('password', 'repeated', array(
        'type' => 'password',
        'invalid_message' => 'The password fields must match.',
        'options' => array('attr' => array('class' => 'password-field')),
        'required' => true,
        'first_options'  => array('label' => 'Password'),
        'second_options' => array('label' => 'Repeat Password'),
    ));

Upon a successful form submit, the value entered into both of the "password"
fields becomes the data of the ``password`` key. In other words, even though
two fields are actually rendered, the end data from the form is just the
single value (usually a string) that you need.

The most important option is ``type``, which can be any field type and determines
the actual type of the two underlying fields. The ``options`` option is passed
to each of those individual fields, meaning - in this example - any option
supported by the ``password`` type can be passed in this array.

Rendering
~~~~~~~~~

The repeated field type is actually two underlying fields, which you can
render all at once, or individually. To render all at once, use something
like:

.. configuration-block::

    .. code-block:: jinja

        {{ form_row(form.password) }}

    .. code-block:: php

        <?php echo $view['form']->row($form['password']) ?>

To render each field individually, use something like this:

.. configuration-block::

    .. code-block:: jinja

        {# .first and .second may vary in your use - see the note below #}
        {{ form_row(form.password.first) }}
        {{ form_row(form.password.second) }}

    .. code-block:: php

        <?php echo $view['form']->row($form['password']['first']) ?>
        <?php echo $view['form']->row($form['password']['second']) ?>

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

Field Options
-------------

type
~~~~

**type**: ``string`` **default**: ``text``

The two underlying fields will be of this field type. For example, passing
a type of ``password`` will render two password fields.

options
~~~~~~~

**type**: ``array`` **default**: ``array()``

This options array will be passed to each of the two underlying fields. In
other words, these are the options that customize the individual field types.
For example, if the ``type`` option is set to ``password``, this array might
contain the options ``always_empty`` or ``required`` - both options that are
supported by the ``password`` field type.

first_options
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Additional options (will be merged into `options` above) that should be passed
*only* to the first field. This is especially useful for customizing the
label::

    $builder->add('password', 'repeated', array(
        'first_options'  => array('label' => 'Password'),
        'second_options' => array('label' => 'Repeat Password'),
    ));

second_options
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Additional options (will be merged into `options` above) that should be passed
*only* to the second field. This is especially useful for customizing the
label (see `first_options`_).

first_name
~~~~~~~~~~

**type**: ``string`` **default**: ``first``

This is the actual field name to be used for the first field. This is mostly
meaningless, however, as the actual data entered into both of the fields will
be available under the key assigned to the ``repeated`` field itself (e.g.
``password``). However, if you don't specify a label, this field name is used
to "guess" the label for you.

second_name
~~~~~~~~~~~

**type**: ``string`` **default**: ``second``

The same as ``first_name``, but for the second field.

Overridden Options
------------------

error_bubbling
~~~~~~~~~~~~~~

**default**: ``false``

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc
