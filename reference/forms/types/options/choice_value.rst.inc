choice_value
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_value`` option was introduced in Symfony 2.7.

**type**: ``callable``, ``string`` or :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` **default**: ``null``

Returns the string "value" for each choice, which must be unique across all choices.
This is used in the ``value`` attribute in HTML and submitted in the POST/PUT requests.
You don't normally need to worry about this, but it might be handy when processing
an API request (since you can configure the value that will be sent in the API request).

This can be a callable or a property path. If ``null`` is given, an incrementing
integer is used as the value.

If you pass a callable, it will receive one argument: the choice itself. When using
the :doc:`/reference/forms/types/entity`, the argument will be the entity object
for each choice or ``null`` in some cases, which you need to handle:

.. code-block:: php

    'choice_value' => function (MyOptionEntity $entity = null) {
        return $entity ? $entity->getId() : '';
    },

.. caution::

    In Symfony 2.7, there was a small backwards-compatibility break with how the
    ``value`` attribute of options is generated. This is not a problem unless you
    rely on the option values in JavaScript. See `issue #14825`_ for details.

.. caution::

    If you want to pass a string property path which is also a callable (e.g 'end'),
    the component will treat it as a callable. You should pass a :class:``Symfony\\Component\\PropertyAccess\\PropertyPath``
    object to ensure the expected behavior::

        use Symfony\Component\PropertyAccess\PropertyPath;

        'choice_value' => new PropertyPath('end'),

.. _`issue #14825`: https://github.com/symfony/symfony/pull/14825
