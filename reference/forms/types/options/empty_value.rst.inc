empty_value
~~~~~~~~~~~

**type**: ``string`` or ``Boolean``

This option determines whether or not a special "empty" option (e.g. "Choose an option")
will appear at the top of a select widget. This option only applies if both
the ``expanded`` and ``multiple`` options are set to false.

* Add an empty value with "Choose an option" as the text::

    $builder->add('states', 'choice', array(
        'empty_value' => 'Choose an option',
    ));

* Guarantee that no "empty" value option is displayed::

    $builder->add('states', 'choice', array(
        'empty_value' => false,
    ));

If you leave the ``empty_value`` option unset, then a blank (with no text)
option will automatically be added if and only if the ``required`` option
is false::

    // a blank (with no text) option will be added
    $builder->add('states', 'choice', array(
        'required' => false,
    ));