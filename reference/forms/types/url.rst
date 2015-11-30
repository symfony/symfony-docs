.. index::
   single: Forms; Fields; UrlType

UrlType Field
=============

The ``UrlType`` field is a text field that prepends the submitted value with
a given protocol (e.g. ``http://``) if the submitted value doesn't already
have a protocol.

+-------------+-------------------------------------------------------------------+
| Rendered as | ``input url`` field                                               |
+-------------+-------------------------------------------------------------------+
| Options     | - `default_protocol`_                                             |
+-------------+-------------------------------------------------------------------+
| Inherited   | - `data`_                                                         |
| options     | - `disabled`_                                                     |
|             | - `empty_data`_                                                   |
|             | - `error_bubbling`_                                               |
|             | - `error_mapping`_                                                |
|             | - `label`_                                                        |
|             | - `label_attr`_                                                   |
|             | - `mapped`_                                                       |
|             | - `max_length`_ (deprecated as of 2.5)                            |
|             | - `read_only`_ (deprecated as of 2.8)                             |
|             | - `required`_                                                     |
|             | - `trim`_                                                         |
+-------------+-------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                     |
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
the data is submitted to the form.

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

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc
