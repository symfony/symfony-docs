.. _configuration-framework-serializer:

serializer
~~~~~~~~~~

.. _reference-serializer-circular_reference_handler:

circular_reference_handler
..........................

**type** ``string``

The service id that is used as the circular reference handler of the default
serializer. The service has to implement the magic ``__invoke($object)``
method.

.. seealso::

    For more information, see
    :ref:`component-serializer-handling-circular-references`.

default_context
...............

**type**: ``array`` **default**: ``[]``

A map with default context options that will be used with each ``serialize`` and ``deserialize``
call. This can be used for example to set the json encoding behavior by setting ``json_encode_options``
to a `json_encode flags bitmask`_.

You can inspect the :ref:`serializer context builders <serializer-using-context-builders>`
to discover the available settings.

.. _reference-serializer-enable_annotations:

enable_attributes
.................

**type**: ``boolean`` **default**: ``true``

Enables support for `PHP attributes`_ in the serializer component.

.. seealso::

    See :ref:`the reference <reference-attributes-serializer>` for a list of supported attributes.

.. _reference-serializer-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the ``serializer`` service or not in the service container.

.. _reference-serializer-mapping:

mapping
.......

.. _reference-serializer-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``[]``

This option allows you to define an array of paths with files or directories where
the component will look for additional serialization files.

max_depth_handler
.................

**type**: ``string``

The service ID used as the max depth handler of the default serializer.

.. _reference-serializer-name_converter:

name_converter
..............

**type**: ``string``

The name converter to use.
The :class:`Symfony\\Component\\Serializer\\NameConverter\\CamelCaseToSnakeCaseNameConverter`
name converter can enabled by using the ``serializer.name_converter.camel_case_to_snake_case``
value.

.. seealso::

    For more information, see :ref:`serializer-name-conversion`.

named_serializers
.................

**type**: ``array``

A list of named serializers to create, keyed by the serializer name. The
``default`` name is reserved and cannot be used.

Each named serializer can define the following options:

* ``name_converter`` (**type**: ``string``) the service ID of a custom name
  converter.
* ``default_context`` (**type**: ``array`` **default**: ``[]``) a map with
  default context options.
* ``include_built_in_normalizers`` (**type**: ``boolean`` **default**: ``true``)
  whether to include the built-in normalizers.
* ``include_built_in_encoders`` (**type**: ``boolean`` **default**: ``true``)
  whether to include the built-in encoders.

.. _`json_encode flags bitmask`: https://www.php.net/json_encode
.. _`PHP attributes`: https://www.php.net/manual/en/language.attributes.overview.php
