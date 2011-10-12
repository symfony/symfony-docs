preferred_choices
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

If this option is specified, then a sub-set of all of the options will be
moved to the top of the select menu. The following would move the "Baz" option
to the top, with a visual separator between it and the rest of the options::

    $builder->add('foo_choices', 'choice', array(
        'choices' => array('foo' => 'Foo', 'bar' => 'Bar', 'baz' => 'Baz'),
        'preferred_choices' => array('baz'),
    ));

Note that preferred choices are only meaningful when rendering as a ``select``
element (i.e. ``expanded`` is false). The preferred choices and normal choices
are separated visually by a set of dotted lines (i.e. ``-------------------``).
This can be customized when rendering the field:

.. configuration-block::

    .. code-block:: jinja
    
        {{ form_widget(form.foo_choices, { 'separator': '=====' }) }}

    .. code-block:: php
    
        <?php echo $view['form']->widget($form['foo_choices'], array('separator' => '=====')) ?>
