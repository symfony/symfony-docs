.. index::
   single: Forms; Fields; ButtonType

ButtonType Field
================

A simple, non-responsive button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` tag                                                       |
+----------------------+----------------------------------------------------------------------+
| Inherited            | - `attr`_                                                            |
| options              | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `translation_domain`_                                              |
+----------------------+----------------------------------------------------------------------+
| Parent type          | none                                                                 |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType` |
+----------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Inherited Options
-----------------

The following options are defined in the
:class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BaseType` class.
The ``BaseType`` class is the parent class for both the ``button`` type
and the :doc:`FormType </reference/forms/types/form>`, but it is not part
of the form type tree (i.e. it cannot be used as a form type on its own).

attr
~~~~

**type**: ``array`` **default**: ``[]``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('save', ButtonType::class, [
        'attr' => ['class' => 'save'],
    ]);

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc
