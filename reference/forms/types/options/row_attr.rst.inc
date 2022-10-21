row_attr
~~~~~~~~

**type**: ``array`` **default**: ``[]``

An associative array of the HTML attributes added to the element which is used
to render the :ref:`form type row <form-rendering-basics>`::

    $builder->add('body', TextareaType::class, [
        'row_attr' => ['class' => 'text-editor', 'id' => '...'],
    ]);

.. seealso::

    Use the ``attr`` option if you want to add these attributes to
    the :ref:`form type widget <form-rendering-basics>` element.
