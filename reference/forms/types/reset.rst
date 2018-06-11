.. index::
   single: Forms; Fields; ResetType

ResetType Field
===============

A button that resets all fields to their original values.

+----------------------+---------------------------------------------------------------------+
| Rendered as          | ``input`` ``reset`` tag                                             |
+----------------------+---------------------------------------------------------------------+
| Inherited            | - `attr`_                                                           |
| options              | - `disabled`_                                                       |
|                      | - `label`_                                                          |
|                      | - `translation_domain`_                                             |
+----------------------+---------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType </reference/forms/types/button>`                   |
+----------------------+---------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType` |
+----------------------+---------------------------------------------------------------------+

Inherited Options
-----------------

attr
~~~~

**type**: ``array`` **default**: ``array()``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('save', ResetType::class, array(
        'attr' => array('class' => 'save'),
    ));

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc
