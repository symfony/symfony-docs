.. index::
   single: Forms; Fields; csrf

csrf Field Type
===============

The ``csrf`` type is a hidden input field containing a CSRF token.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``hidden`` field                                         |
+-------------+--------------------------------------------------------------------+
| Options     | - ``csrf_provider``                                                |
|             | - ``intention``                                                    |
+-------------+--------------------------------------------------------------------+
| Overriden   | - ``property_path``                                                |
| Options     |                                                                    |
|             |                                                                    |
+-------------+--------------------------------------------------------------------+
| Parent type | ``hidden``                                                         |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Csrf\\Type\\CsrfType` |
+-------------+--------------------------------------------------------------------+

Overriden Options
-----------------

property_path
~~~~~~~~~~~~~

**default**: ``false``

A Csrf field must not be mapped to the object, so this option defaults to ``false``.

Field Options
-------------

csrf_provider
~~~~~~~~~~~~~

**type**: ``Symfony\Component\Form\CsrfProvider\CsrfProviderInterface``

The ``CsrfProviderInterface`` object that should generate the CSRF token.
If not set, this defaults to the default provider.

intention
~~~~~~~~~

**type**: ``string``

An optional unique identifier used to generate the CSRF token.

.. include:: /reference/forms/types/options/property_path.rst.inc
