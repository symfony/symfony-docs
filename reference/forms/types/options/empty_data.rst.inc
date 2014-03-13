empty_data
~~~~~~~~~~

**type**: ``mixed`` **default**: depends on other field options, see below

This option determines what value the field will return when the submitted
value is empty. This may happen when the ``empty_value`` choice in a
``choice`` field is selected or when an ``input`` field of some type is not
required and left empty by the user.

The true default value of this option depends on other field options:

* If ``data_class`` is set and ``required`` is ``true``, then ``new $data_class()``;
* If ``data_class`` is set and ``required`` is ``false``, then ``null``;
* If ``data_class`` is not set and ``compound`` is ``true``, then ``array()``;
* If ``data_class`` is not set and ``compound`` is ``false``, then ``''`` (empty string).

But you can customize this to your needs. For example, if you want the
``gender`` choice field to be explicitly set to ``null`` when no value is
selected, you can do it like this:

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
    see the cookbook article :doc:`/cookbook/form/use_empty_data`.
