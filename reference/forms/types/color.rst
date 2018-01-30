.. index::
   single: Forms; Fields; ColorType

ColorType Field
===============

The ``ColorType`` field is a text field that is rendered using the HTML5
``<input type="color">`` tag. Depending on each browser, the behavior of this
form field can vary substantially. Some browsers display it as a simple text
field, while others display a native color picker.

The value of the underlying ``<input type="color">`` field is always a
7-character string specifying an RGB color in lower case hexadecimal notation.
That's why it's not possible to select semi-transparent colors with this
element.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``color`` field (a text box)                              |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `data`_                                                           |
| options     | - `disabled`_                                                       |
|             | - `empty_data`_                                                     |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `label_format`_                                                   |
|             | - `mapped`_                                                         |
|             | - `required`_                                                       |
|             | - `trim`_                                                           |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                       |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ColorType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

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

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
