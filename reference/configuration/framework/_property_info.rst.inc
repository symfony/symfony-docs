property_info
~~~~~~~~~~~~~

.. _reference-property-info-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

with_constructor_extractor
..........................

**type**: ``boolean`` **default**: ``false``

Configures the ``property_info`` service to extract property information from the constructor arguments
using the :ref:`ConstructorExtractor <components-property-information-constructor-extractor>`.

.. versionadded:: 7.3

    The ``with_constructor_extractor`` option was introduced in Symfony 7.3.
    It's required to set a value for it because its default value will change
    from ``false`` to ``true`` in Symfony 8.0.
