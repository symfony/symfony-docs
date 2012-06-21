Image
=====

The Image constraint works exactly like the :doc:`File</reference/constraints/File>`
constraint, except that its `mimeTypes`_ and `mimeTypesMessage` options are
automatically setup to work for image files specifically.

See the :doc:`File</reference/constraints/File>` constraint for the bulk of
the documentation on this constraint.

Options
-------

This constraint shares all of its options with the :doc:`File</reference/constraints/File>`
constraint. It does, however, modify two of the default option values:

mimeTypes
~~~~~~~~~

**type**: ``array`` or ``string`` **default**: an array of jpg, gif and png image mime types

You can find a list of existing image mime types on the `IANA website`_

mimeTypesMessage
~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This file is not a valid image``


.. _`IANA website`: http://www.iana.org/assignments/media-types/image/index.html