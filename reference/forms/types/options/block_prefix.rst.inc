block_prefix
~~~~~~~~~~~~

**type**: ``string`` or ``null`` **default**: ``null`` (see :ref:`Knowing which
block to customize <form-customization-sidebar>`)

.. versionadded:: 4.3

    The ``block_prefix`` option was introduced in Symfony 4.3.

Allows you to add a custom block prefix and override the block name
used to render the form type. Useful for example if you have multiple
instances of the same form and you need to personalize the rendering
of all of them without the need to create a new form type.
