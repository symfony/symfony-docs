Unique
======

Validates that all the elements of the given collection are unique (none of them
is present more than once). By default elements are compared strictly,
so ``'7'`` and ``7`` are considered different elements (a string and an integer, respectively).
If you want to apply any other comparison logic, use the `normalizer`_ option.

.. seealso::

    If you want to apply different validation constraints to the elements of a
    collection or want to make sure that certain collection keys are present,
    use the :doc:`Collection constraint </reference/constraints/Collection>`.

.. seealso::

    If you want to validate that the value of an entity property is unique among
    all entities of the same type (e.g. the registration email of all users) use
    the :doc:`UniqueEntity constraint </reference/constraints/UniqueEntity>`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Unique`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\UniqueValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint can be applied to any property of type ``array`` or
``\Traversable``. In the following example, ``$contactEmails`` is an array of
strings:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\Unique]
            protected array $contactEmails;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                contactEmails:
                    - Unique: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="contactEmails">
                    <constraint name="Unique"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('contactEmails', new Assert\Unique());
            }
        }

Options
-------

``fields``
~~~~~~~~~~

**type**: ``array`` | ``string``


.. versionadded:: 6.1

    The ``fields`` option was introduced in Symfony 6.1.

This is defines the key or keys in a collection that should be checked for
uniqueness. By default, all collection keys are checked for uniqueness.

For instance, assume you have a collection of items that contain a
``latitude``, ``longitude`` and ``label`` fields. By default, you can have
duplicate coordinates as long as the label is different. By setting the
``fields`` option, you can force latitude+longitude to be unique in the
collection::

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Poi.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Poi
        {
            #[Assert\Unique(fields=['latitude', 'longitude'])]
            protected array $coordinates;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Poi:
            properties:
                coordinates:
                    - Unique:
                          fields: [latitude, longitude]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Poi">
                <property name="coordinates">
                    <constraint name="Unique">
                        <option name="fields">
                            <value>latitude</value>
                            <value>longitude</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Poi.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Poi
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('coordinates', new Assert\Unique([
                    'fields' => ['latitude', 'longitude'],
                ]));
            }
        }

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain only unique elements.``

This is the message that will be shown if at least one element is repeated in
the collection.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ value }}``                The current (invalid) value
=============================  ================================================

``normalizer``
~~~~~~~~~~~~~~

**type**: a `PHP callable`_ **default**: ``null``

This option defined the PHP callable applied to each element of the given
collection before checking if the collection is valid.

For example, you can pass the ``'trim'`` string to apply the :phpfunction:`trim`
PHP function to each element of the collection in order to ignore leading and
trailing whitespace during validation.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`PHP callable`: https://www.php.net/callable
