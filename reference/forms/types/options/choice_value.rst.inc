choice_value
~~~~~~~~~~~~

**type**: ``callable`` or ``string`` **default**: ``null``

Returns the string "value" for each choice, which must be unique across all choices.
This is used in the ``value`` attribute in HTML and submitted in the POST/PUT requests.
You don't normally need to worry about this, but it might be handy when processing
an API request (since you can configure the value that will be sent in the API request).

This can be a callable or a property path. If ``null`` is given, an incrementing
integer is used as the value.

If you pass a callable, it will receive one argument: the choice itself. When using
the :doc:`/reference/forms/types/entity`, the argument will be the entity object
for each choice or ``null`` in some cases, which you need to handle::

    'choice_value' => function (MyOptionEntity $entity = null) {
        return $entity ? $entity->getId() : '';
    },
