UrlType Field
=============

The ``UrlType`` field is a text field that prepends the submitted value with
a given protocol (e.g. ``http://``) if the submitted value doesn't already
have a protocol.

+---------------------------+-------------------------------------------------------------------+
| Rendered as               | ``input url`` field                                               |
+---------------------------+-------------------------------------------------------------------+
| Default invalid message   | Please enter a valid URL.                                         |
+---------------------------+-------------------------------------------------------------------+
| Parent type               | :doc:`TextType </reference/forms/types/text>`                     |
+---------------------------+-------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\UrlType` |
+---------------------------+-------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

``default_protocol``
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Set this value to ``null`` to render the field using a ``<input type="url"/>``,
allowing the browser to perform local validation before submission.

When this value is neither ``null`` nor an empty string, the form field is
rendered using a ``<input type="text"/>``. This ensures users can submit the
form field without specifying the protocol.

If a value is submitted that doesn't begin with some protocol (e.g. ``http://``,
``ftp://``, etc), this protocol will be prepended to the string when
the data is submitted to the form.

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

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
