choice_value
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_value`` option was introduced in Symfony 2.7.

**type**: ``callable``, ``string`` or :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` **default**: ``null``

Returns the string "value" for each choice. This is used in the ``value`` attribute
in HTML and submitted in the POST/PUT requests. You don't normally need to worry
about this, but it might be handy when processing an API request (since you can
configure the value that will be sent in the API request).

This can be a callable or a property path. Both have the responsibility to return
a unique string for each choice, otherwise an incrementing integer is used.

If it is a callable, it get passed each choice as only argument, supposed your choices
are some previously loaded entities in a given array::

    $builder->add('attending', 'choice', array(
        'choices' => $entities,
        'choices_as_values' => true,
        'choice_value' => 'id',
    ));

Is equivalent to::

    $builder->add('attending', 'choice', array(
        'choices' => $entities,
        'choices_as_values' => true,
        'choice_value' => function ($choice) {
            return $choice->getId();
        },
    ));

.. tip::

    Those examples reproduce the default behavior of the ``EntityType``.
    When your choices are already loaded you should consider using the
    ``ChoiceType`` instead unless you need a lazy loading or a custom query.

.. caution::

    In Symfony 2.7, there was a small backwards-compatibility break with how the
    `value` attribute of options is generated. This is not a problem unless you
    rely on the option values in JavaScript. See `issue #14825`_ for details.

.. caution::

    If you want to pass a string property path which is also a callable (e.g 'end'),
    the component will treat it as a callable. You should pass a :class:``Symfony\\Component\\PropertyAccess\\PropertyPath``
    object to ensure the expected behavior::

        use Symfony\Component\PropertyAccess\PropertyPath;

        'choice_value' => new PropertyPath('end'),

.. _`issue #14825`: https://github.com/symfony/symfony/pull/14825
