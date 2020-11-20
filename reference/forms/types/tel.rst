.. index::
   single: Forms; Fields; TelType

TelType Field
===============

The ``TelType`` field is a text field that is rendered using the HTML5
``<input type="tel">`` tag. Following the recommended HTML5 behavior, the value
of this type is not validated in any way, because formats for telephone numbers
vary too much depending on each country.

Nevertheless, it may be useful to use this type in web applications because some
browsers (e.g. smartphone browsers) adapt the input keyboard to make it easier
to input phone numbers.

+---------------------------+-------------------------------------------------------------------+
| Rendered as               | ``input`` ``tel`` field (a text box)                              |
+---------------------------+-------------------------------------------------------------------+
| Overridden options        | - `invalid_message`_                                              |
+---------------------------+-------------------------------------------------------------------+
| Inherited options         | - `attr`_                                                         |
|                           | - `data`_                                                         |
|                           | - `disabled`_                                                     |
|                           | - `empty_data`_                                                   |
|                           | - `error_bubbling`_                                               |
|                           | - `error_mapping`_                                                |
|                           | - `help`_                                                         |
|                           | - `help_attr`_                                                    |
|                           | - `help_html`_                                                    |
|                           | - `label`_                                                        |
|                           | - `label_attr`_                                                   |
|                           | - `label_format`_                                                 |
|                           | - `mapped`_                                                       |
|                           | - `required`_                                                     |
|                           | - `row_attr`_                                                     |
|                           | - `trim`_                                                         |
+---------------------------+-------------------------------------------------------------------+
| Default invalid message   | Please provide a valid phone number.                              |
+---------------------------+-------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                               |
+---------------------------+-------------------------------------------------------------------+
| Parent type               | :doc:`TextType </reference/forms/types/text>`                     |
+---------------------------+-------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TelType` |
+---------------------------+-------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

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

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
