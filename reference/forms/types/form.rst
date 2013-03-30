.. index::
   single: Forms; Fields; form

form Field Type
===============

See :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType`.

Overriden Options
-----------------

empty_data
~~~~~~~~~~

**default**: ``array()`` / ``new $data_class()``

When no ``data_class`` option is specified, it will return an empty array.
Otherwise, it will default to a new instance of the class defined in
``data_class``.

virtual
~~~~~~~

**default**: ``false``

error_bubbling
~~~~~~~~~~~~~~

**default**: ``true``

Errors of the form bubbles to the root form by default.
