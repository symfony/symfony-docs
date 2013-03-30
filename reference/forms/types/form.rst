.. index::
   single: Forms; Fields; form

form Field Type
===============

See :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType`.

Overriden Options
-----------------

empty_data
~~~~~~~~~~

**default**: ``array()``

When no ``data_class`` option is specified, it will return an empty array if
no value is set.

virtual
~~~~~~~

**default**: ``false``

error_bubbling
~~~~~~~~~~~~~~

**default**: ``true``

Errors of the form bubbles to the root form by default.
