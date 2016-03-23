group_by
~~~~~~~~

.. versionadded:: 2.7
    The ``group_by`` option was introduced in Symfony 2.7.

**type**: ``callable``, ``string`` or :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` **default**: ``null``

You can easily "group" options in a select simply by passing a multi-dimensional
array to ``choices``. See the :ref:`Grouping Options <form-choices-simple-grouping>`
section about that.

The ``group_by`` option is an alternative way to group choices, which gives you
a bit more flexibility.

Take the following example::

    $builder->add('publishAt', 'choice', array(
        'choices' => array(
            'now' => new \DateTime('now'),
            'tomorrow' => new \DateTime('+1 day'),
            '1 week' => new \DateTime('+1 week'),
            '1 month' => new \DateTime('+1 month')
        ),
        'choices_as_values' => true,
        'group_by' => function($choice, $key, $value) {
            if ($choice <= new \DateTime('+3 days')) {
                return 'Soon';
            } else {
                return 'Later';
            }
        },
    ));

This groups the dates that are within 3 days into "Soon" and everything else into
a "Later" group:

.. image:: /images/reference/form/choice-example5.png
   :align: center

If you return ``null``, the option won't be grouped. You can also pass a string
"property path" that will be called to get the group name.

See the `choice_label`_ for details about using a property path or a callable.
