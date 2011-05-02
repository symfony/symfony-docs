.. index::
   single: Forms; Fields; csrf

csrf Field Type
===============

The ``csrf`` type is a hidden input field containing a CSRF token.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``hidden`` field                                         |
+-------------+--------------------------------------------------------------------+
| Options     | - ``csrf_provider``                                                |
|             | - ``page_id``                                                      |
|             | - ``error_bubbling``                                               |
+-------------+--------------------------------------------------------------------+
| Parent type | ``hidden``                                                         |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Csrf\\Type\\CsrfType` |
+-------------+--------------------------------------------------------------------+

Options
-------

* ``csrf_provider`` [type: ``Symfony\Component\Form\CsrfProvider\CsrfProviderInterface``]
    The ``CsrfProviderInterface`` object that should generate the CSRF token.
    If not set, this defaults to the default provider.

* ``page_id`` [type: string]
    An optional page identifier used to generate the CSRF token.

* ``error_bubbling`` [type: Boolean, default: true]
   .. include:: /reference/forms/types/options/error_bubbling.rst.inc
      :start-line: 1
