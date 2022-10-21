label_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The content of the `label`_ option is translated before displaying it, so it
can contain :ref:`translation placeholders <component-translation-placeholders>`.
This option defines the values used to replace those placeholders.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yaml
    form.order.id: 'Identifier of the order to %company%'

You can specify the placeholder values as follows::

    $builder->add('id', null, [
        'label' => 'form.order.id',
        'label_translation_parameters' => [
            '%company%' => 'ACME Inc.',
        ],
    ]);

The ``label_translation_parameters`` option of children fields is merged with
the same option of their parents, so children can reuse and/or override any of
the parent placeholders.
