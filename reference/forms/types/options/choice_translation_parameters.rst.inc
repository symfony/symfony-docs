choice_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array``, ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``[]``

The choice values are translated before displaying it, so it can contain
:ref:`translation placeholders <component-translation-placeholders>`.
This option defines the values used to replace those placeholders. This can be
an associative array where the keys match the choice keys and the values
are the attributes for each choice, a callable or a property path
(just like `choice_label`_).

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yaml
    form.order.yes: 'I confirm my order to the company %company%'
    form.order.no: 'I cancel my order'

You can specify the placeholder values as follows::

    $builder->add('id', null, [
        'choice' => [
            'form.order.yes' => true,
            'form.order.no' => false,
        ],
        'choice_translation_parameters' => function ($choice, $key, $value) {
            if (false === $choice) {
                return [];
            }

            return ['%company%' => 'ACME Inc.']
        },
    ]);

If an array, the keys of the ``choices`` array must be used as keys::

    $builder->add('id', null, [
        'choice' => [
            'form.order.yes' => true,
            'form.order.no' => false,
        ],
        'choice_translation_parameters' => [
            'form.order.yes' => ['%company%' => 'ACME Inc.'],
            'form.order.no' => [],
        ],
    ]);

The ``choice_translation_parameters`` option of children fields is merged with
the same option of their parents, so children can reuse and/or override any of
the parent placeholders.
