``normalizer``
~~~~~~~~~~~~~~

**type**: a `PHP callable`_ **default**: ``null``

This option allows to define the PHP callable applied to the given value before
checking if it is valid.

For example, you may want to pass the ``'trim'`` string to apply the
:phpfunction:`trim` PHP function in order to ignore leading and trailing
whitespace during validation.

.. _`PHP callable`: https://www.php.net/callable
