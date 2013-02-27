read_only
~~~~~~~~~

.. versionadded:: 2.1
    The ``read_only`` option was changed in 2.1 to render as a ``readonly``
    HTML attribute. Previously, it rendered as a ``disabled`` attribute.
    Use the `disabled`_ option if you need the old behavior.

**type**: ``Boolean`` **default**: ``false``

If this option is true, the field will be rendered with the ``readonly``
attribute so that the field is not editable.