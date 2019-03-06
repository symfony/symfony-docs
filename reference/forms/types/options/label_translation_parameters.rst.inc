label_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

.. versionadded:: 4.3

    The ``label_translation_parameters`` option was introduced in Symfony 4.3.

The content of the `label`_ option is translated before displaying it, so it
can contain :ref:`translation placeholders <component-translation-placeholders>`.
This option defines the values used to replace those placeholders.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order.id: 'Identifier of the order to %company%'

You can specify the placeholder values as follows:

.. code-block:: php

    $builder->add('id', null, array(
        'label' => 'form.order.id',
        'label_translation_parameters' => [
            '%company%' => 'ACME Inc.'
        ]
    ));

The ``label_translation_parameters`` option of children fields is merged with
the same option of their parents, so children can reuse and/or override any of
the parent placeholders.
