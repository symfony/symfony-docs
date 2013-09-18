empty_data
~~~~~~~~~~

**type**: ``mixed`` **default**: ``array()`` if ``multiple`` or ``expanded``, ``''`` otherwise

This option determines what value the field will return when the ``empty_value``
choice is selected.

The true default value of this option depends on the field options:

* If ``compound`` is ``true`` and ``data_class`` is set, then ``new $data_class()``;
* If ``compound`` is ``true`` and no ``data_class`` is set, then ``array()``;
* If ``compound`` is ``false``, then ``null``.

.. tip::

    The ``compound`` option is set to ``true`` when the field actually represents
    a collection of fields (e.g. a form of fields).

For example, if you want the ``gender`` field to be set to ``null`` when no
value is selected, you can do it like this:

.. code-block:: php

    $builder->add('gender', 'choice', array(
        'choices' => array(
            'm' => 'Male',
            'f' => 'Female'
        ),
        'required'    => false,
        'empty_value' => 'Choose your gender',
        'empty_data'  => null
    ));

.. note::

    If you want to set the ``empty_data`` option for your entire form class,
    see the cookbook article :doc:`/cookbook/form/use_empty_data`
