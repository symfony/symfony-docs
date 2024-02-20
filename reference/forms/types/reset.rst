ResetType Field
===============

A button that resets all fields to their original values.

+----------------------+---------------------------------------------------------------------+
| Rendered as          | ``input`` ``reset`` tag                                             |
+----------------------+---------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType </reference/forms/types/button>`                   |
+----------------------+---------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType` |
+----------------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Inherited Options
-----------------

``attr``
~~~~~~~~

**type**: ``array`` **default**: ``[]``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('save', ResetType::class, [
        'attr' => ['class' => 'save'],
    ]);

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc

label_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The content of the `label`_ option is translated before displaying it, so it
can contain :ref:`translation placeholders <component-translation-placeholders>`.
This option defines the values used to replace those placeholders.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yaml
    form.order.reset: 'Reset an order to %company%'

You can specify the placeholder values as follows::

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, [
        'label' => 'form.order.reset',
        'label_translation_parameters' => [
            '%company%' => 'ACME Inc.',
        ],
    ]);

The ``label_translation_parameters`` option of buttons is merged with the same
option of its parents, so buttons can reuse and/or override any of the parent
placeholders.

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
