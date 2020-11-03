``label_html``
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 5.1

	The ``label_html`` option was introduced in Symfony 5.1.

By default, the contents of the ``label`` option are escaped before rendering
them in the template. Set this option to ``true`` to not escape them, which is
useful when the label contains HTML elements.
