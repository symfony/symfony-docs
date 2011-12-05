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
|             | - ``property_path``                                                |
+-------------+--------------------------------------------------------------------+
| Parent type | ``hidden``                                                         |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Csrf\\Type\\CsrfType` |
+-------------+--------------------------------------------------------------------+

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