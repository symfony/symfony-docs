.. index::
   single: Forms; Fields; url

``url`` Field Type
==================

The ``url`` field is a text field that prepends the submitted value with
a given protocol (e.g. ``http://``) if the submitted value doesn't already
have a protocol.

============  ======
Rendered as   ``text`` field
Options       ``default_protocol``, ``max_length``, ``required``, ``label``, ``read_only``, ``trim``, ``error_bubbling``
Parent type   ``text``
Class         :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\UrlType`
============  ======

Options
-------

* ``default_protocol`` [type: string, default: ``http``]

    If a value is submitted that doesn't begin with some protocol (e.g. ``http://``,
    ``ftp://``, etc), this protocol will be prepended to the string when
    the data is bound to the form.

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc