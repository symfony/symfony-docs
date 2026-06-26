RangeType Field
===============

The ``RangeType`` field is a slider that is rendered using the HTML5
``<input type="range">`` tag.

+---------------------------+---------------------------------------------------------------------+
| Rendered as               | ``input`` ``range`` field (slider in HTML5 supported browser)       |
+---------------------------+---------------------------------------------------------------------+
| Default invalid message   | Please choose a valid range.                                        |
+---------------------------+---------------------------------------------------------------------+
| Parent type               | :doc:`TextType </reference/forms/types/text>`                       |
+---------------------------+---------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RangeType` |
+---------------------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\RangeType;
    // ...

    $builder->add('name', RangeType::class, [
        'attr' => [
            'min' => 5,
            'max' => 50
        ],
    ]);

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
