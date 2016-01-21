.. index::
   single: Forms; Fields; RangeType

RangeType Field
===============

The ``RangeType`` field is a slider that is rendered using the HTML5
``<input type="range" />`` tag.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``range`` field (slider in HTML5 supported browser)       |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `attr`_                                                           |
| options     | - `data`_                                                           |
|             | - `disabled`_                                                       |
|             | - `empty_data`_                                                     |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `mapped`_                                                         |
|             | - `required`_                                                       |
|             | - `trim`_                                                           |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                       |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RangeType` |
+-------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\RangeType;
    // ...

    $builder->add('name', RangeType::class, array(
        'attr' => array(
            'min' => 5,
            'max' => 50
        )
    ));

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
