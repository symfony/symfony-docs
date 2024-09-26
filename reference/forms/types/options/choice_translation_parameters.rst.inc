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

.. configuration-block::

    .. code-block:: yaml

        # translations/messages.en.yaml
        form.order.yes: 'I confirm my order to the company %company%'
        form.order.no: 'I cancel my order'

    .. code-block:: xml

        <!-- translations/messages.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                <trans-unit id="form.order.yes">
                    <source>form.order.yes</source>
                    <target>I confirm my order to the company %company%</target>
                </trans-unit>
                <trans-unit id="form.order.no">
                    <source>form.order.no</source>
                    <target>I cancel my order</target>
                </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: php

        // translations/messages.fr.php
        return [
            'form.order.yes' => "I confirm my order to the company %company%",
            'form.order.no' => "I cancel my order",
        ];

You can specify the placeholder values as follows::

    $builder->add('id', null, [
        'choices' => [
            'form.order.yes' => true,
            'form.order.no' => false,
        ],
        'choice_translation_parameters' => function ($choice, string $key, mixed $value): array {
            if (false === $choice) {
                return [];
            }

            return ['%company%' => 'ACME Inc.'];
        },
    ]);

If an array, the keys of the ``choices`` array must be used as keys::

    $builder->add('id', null, [
        'choices' => [
            'form.order.yes' => true,
            'form.order.no' => false,
        ],
        'choice_translation_parameters' => [
            'form.order.yes' => ['%company%' => 'ACME Inc.'],
            'form.order.no' => [],
        ],
    ]);

The translation parameters of child fields are merged with the same option of
their parents, so children can reuse and/or override any of the parent placeholders.
