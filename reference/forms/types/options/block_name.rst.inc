``block_name``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: the form's name (see :ref:`Knowing which
block to customize <form-customization-sidebar>`)

Allows you to add a custom block name to the ones used by default to render the
form type. Useful for example if you have multiple instances of the same form
and you need to personalize the rendering of the forms individually.

If you set for example this option to ``my_custom_name`` and the field is of
type ``text``, Symfony will use the following names (and in this order) to find
the block used to render the widget of the field: ``_my_custom_name_widget``,
``text_widget`` and ``form_widget``.
