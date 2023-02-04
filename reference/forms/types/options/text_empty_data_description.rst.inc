This option determines what value the field will *return* when the submitted
value is empty (or missing). It does not set an initial value if none is
provided when the form is rendered in a view.

This means it helps you handling form submission with blank fields. For
example, if you want the ``name`` field to be explicitly set to ``John Doe``
when no value is selected, you can do it like this::

    $builder->add('name', null, [
        'required'   => false,
        'empty_data' => 'John Doe',
    ]);

This will still render an empty text box, but upon submission the ``John Doe``
value will be set. Use the ``data`` or ``placeholder`` options to show this
initial value in the rendered form.

If a form is compound, you can set ``empty_data`` as an array, object or
closure. See the :doc:`/form/use_empty_data` article for more details about
these options.

.. note::

    If you want to set the ``empty_data`` option for your entire form class,
    see the :doc:`/form/use_empty_data` article.
