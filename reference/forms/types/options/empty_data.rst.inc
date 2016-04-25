empty_data
~~~~~~~~~~

**type**: ``string`` or ``array`` when the form is compound

.. This file should only be included with start-after or end-before that's
   set to this placeholder value. Its purpose is to let us include only
   part of this file.

DEFAULT_PLACEHOLDER

This option determines what value the field will return when the submitted
value is empty. It does not set an initial value if none is provided when
the form is rendered in a view (see ``data`` or ``placeholder`` options).

It helps you handling form submission and you can customize this to your needs.
For example, if you want the ``gender`` choice field to be explicitly set to ``Male``
when no value is selected, you can do it like this::

    $builder->add('gender', 'choice', array(
        'choices' => array(
            'm' => 'Male',
            'f' => 'Female',
        ),
        'required'    => false,
        'empty_value' => 'Choose your gender',
        'empty_data'  => 'm',
    ));

If a form is compound, you can set ``empty_data`` as an array with fields names
as keys and submitted values as string values (or arrays if nested fields are
also compound).

.. caution::

    In this example, the choice field is not set as ``multiple``. If it was
    ``empty_data`` option should be an array of submitted string values::

        'empty_data' => array('m'),

.. note::

    If you want to set the ``empty_data`` option for your entire form class,
    see the cookbook article :doc:`/cookbook/form/use_empty_data`.
