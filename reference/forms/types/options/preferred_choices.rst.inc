preferred_choices
~~~~~~~~~~~~~~~~~

**type**: ``array``, ``callable`` or ``string`` **default**: ``array()``

If this option is specified as an array, then a sub-set of all of the options
will be moved to the top of the select menu. The following would move the
"Yes" option to the top, with a visual separator between it and the rest
of the options::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ),
        'choices_as_values' => true,
        'preferred_choices' => array(true),
    ));

.. versionadded:: 2.7
    Setting a callable or propery path was introduced in Symfony 2.7.

If this option is specified as a callable, then the the preferred options
are computed by the callback::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ),
        'choices_as_values' => true,
        'preferred_choices' => function ($choice, $key) {
            return true === $choice;
        },
    ));

If this option is specified as a property path, then the preferred options
are taken from the objects::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => Status::getInstance(Status::YES),
            'No' => Status::getInstance(Status::NO),
            'Maybe' => Status::getInstance(Status::MAYBE),
        ),
        'choices_as_values' => true,
        'preferred_choices' => 'preferred',
    ));

Note that preferred choices are only meaningful when rendering as a ``select``
element (i.e. ``expanded`` is false). The preferred choices and normal choices
are separated visually by a set of dotted lines (i.e. ``-------------------``).
This can be customized when rendering the field:

.. configuration-block::

    .. code-block:: twig

        {{ form_widget(form.foo_choices, { 'separator': '=====' }) }}

    .. code-block:: php

        <?php echo $view['form']->widget($form['foo_choices'], array(
                  'separator' => '====='
        )) ?>
