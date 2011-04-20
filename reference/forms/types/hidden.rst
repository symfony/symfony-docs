.. index::
   single: Forms; Fields; hidden

``hidden`` Field Type
=====================

The hidden type represents a hidden input field.

============  ======
Rendered as   ``input`` ``hidden`` field
Options       ``max_length``, ``required``, ``label``, ``read_only``, ``trim``, ``error_bubbling``
Parent type   ``field``
Class         :class:`Symfony\\Component\\Form\\Type\\HiddenType`
============  ======

Options
-------

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

* ``error_bubbling`` [type: Boolean, default: true]
   .. include:: /reference/forms/types/options/error_bubbling.rst.inc
      :start-line: 1