empty_data
~~~~~~~~~~

**type**: ``string`` or ``array`` when the form is compound

.. This file should only be included with start-after or end-before that's
   set to this placeholder value. Its purpose is to let us include only
   part of this file.

DEFAULT_PLACEHOLDER

This option determines what value the field will return when the submitted
value is empty (or missing). It does not set an initial value if none is
provided when the form is rendered in a view (see ``data`` or ``placeholder``
options).

It helps you handling form submission and you can customize this to your needs.
For example, if you want the ``name`` field to be explicitly set to ``John Doe``
when no value is selected, you can do it like this::

    $builder->add('name', null, array(
        'required'    => false,
        'empty_data'  => 'John Doe',
    ));

If a form is compound, you can set ``empty_data`` as an array with field names
as keys and submitted values as string values (or arrays if nested fields are
also compound).

.. note::

    If you want to set the ``empty_data`` option for your entire form class,
    see the cookbook article :doc:`/cookbook/form/use_empty_data`.

.. caution::

    When using `empty_data` as an empty string, the form will always return
    ``null``. If you need to get an empty string to be returned, you should
    use a data transformer, see the cookbook article
    :doc:`cookbook/form/data_transformers`.
