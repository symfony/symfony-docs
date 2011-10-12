.. index::
   single: Forms; Fields; url

url Field Type
==============

The ``url`` field is a text field that prepends the submitted value with
a given protocol (e.g. ``http://``) if the submitted value doesn't already
have a protocol.

+-------------+-------------------------------------------------------------------+
| Rendered as | ``input url`` field                                               |
+-------------+-------------------------------------------------------------------+
| Options     | - ``default_protocol``                                            |
+-------------+-------------------------------------------------------------------+
| Inherited   | - `max_length`_                                                   |
| options     | - `required`_                                                     |
|             | - `label`_                                                        |
|             | - `trim`_                                                         |
|             | - `read_only`_                                                    |
|             | - `error_bubbling`_                                               |
+-------------+-------------------------------------------------------------------+
| Parent type | :doc:`text</reference/forms/types/text>`                          |
+-------------+-------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\UrlType` |
+-------------+-------------------------------------------------------------------+

Field Options
-------------

default_protocol
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``http``

If a value is submitted that doesn't begin with some protocol (e.g. ``http://``,
``ftp://``, etc), this protocol will be prepended to the string when
the data is bound to the form.

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
