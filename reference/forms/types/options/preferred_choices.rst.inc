preferred_choices
~~~~~~~~~~~~~~~~~

**type**: ``array``, ``callable`` or ``string`` **default**: ``array()``

This option allows you to move certain choices to the top of your list with a visual
separator between them and the rest of the options. If you have a form of languages,
you can list the most popular on top, like Bork Bork and Pirate::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('language', ChoiceType::class, array(
        'choices' => array(
            'English' => 'en',
            'Spanish' => 'es',
            'Bork'   => 'muppets',
            'Pirate' => 'arr'
        ),
        'choices_as_values' => true,
        'preferred_choices' => array('muppets', 'arr')
    ));

.. versionadded:: 2.7
    Setting a callable or propery path was introduced in Symfony 2.7.

This options can also be a callback function to give you more flexibility. This might
be especially useful if your values are objects::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('publishAt', ChoiceType::class, array(
        'choices' => array(
            'now' => new \DateTime('now'),
            'tomorrow' => new \DateTime('+1 day'),
            '1 week' => new \DateTime('+1 week'),
            '1 month' => new \DateTime('+1 month')
        ),
        'choices_as_values' => true,
        'preferred_choices' => function ($val, $key) {
            // prefer options within 3 days
            return $val <= new \DateTime('+3 days');
        },
    ));

This will "prefer" the "now" and "tomorrow" choices only:

.. image:: /images/reference/form/choice-example3.png
   :align: center

Finally, if your values are objects, you can also specific a property path string
on the object that will return true or false.

The preferred choices are only meaningful when rendering a ``select`` element
(i.e. ``expanded`` false). The preferred choices and normal choices are separated
visually by a set of dotted lines (i.e. ``-------------------``). This can be customized
when rendering the field:

.. configuration-block::

    .. code-block:: twig

        {{ form_widget(form.publishAt, { 'separator': '=====' }) }}

    .. code-block:: php

        <?php echo $view['form']->widget($form['publishAt'], array(
                  'separator' => '====='
        )) ?>
