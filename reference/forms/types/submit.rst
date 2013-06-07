.. index::
   single: Forms; Fields; submit

submit Field Type
=================

.. versionadded:: 2.3
    The ``submit`` type was added in Symfony 2.3

A submit button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``input`` ``submit`` tag                                             |
+----------------------+----------------------------------------------------------------------+
| Inherited            | - `attr`_                                                            |
| options              | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `translation_domain`_                                              |
+----------------------+----------------------------------------------------------------------+
| Parent type          | :doc:`button</reference/forms/types/button>`                         |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType` |
+----------------------+----------------------------------------------------------------------+

The Submit button has an additional method
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` that lets you
check whether this button was used to submit the form. This is especially
useful when :ref:`a form has multiple submit buttons <book-form-submitting-multiple-buttons>`::

    if ($form->get('save')->isClicked()) {
        // ...
    }

Inherited options
-----------------

.. include:: /reference/forms/types/options/button_attr.rst.inc

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc
