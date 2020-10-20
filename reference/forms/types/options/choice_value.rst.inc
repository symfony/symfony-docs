``choice_value``
~~~~~~~~~~~~~~~~

**type**: ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

Returns the string "value" for each choice, which must be unique across all choices.
This is used in the ``value`` attribute in HTML and submitted in the POST/PUT requests.
You don't normally need to worry about this, but it might be handy when processing
an API request (since you can configure the value that will be sent in the API request).

This can be a callable or a property path. By default, the choices are used if they
can be casted to strings. Otherwise an incrementing integer is used (starting at ``0``).

If you pass a callable, it will receive one argument: the choice itself. When using
the :doc:`/reference/forms/types/entity`, the argument will be the entity object
for each choice or ``null`` in a placeholder is used, which you need to handle::

    'choice_value' => function (?MyOptionEntity $entity) {
        return $entity ? $entity->getId() : '';
    },

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'choice_value' => ChoiceList::value($this, 'uuid'),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
