Sequentially
============

This constraint allows you to apply a set of rules that should be validated
step-by-step, allowing you to interrupt the validation once the first violation is raised.

As an alternative in situations ``Sequentially`` cannot solve, you may consider
using :doc:`GroupSequence </validation/sequence_provider>` which allows more control.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Sequentially`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\SequentiallyValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose that you have a ``Place`` object with an ``$address`` property which
must match the following requirements:

* it's a non-blank string
* of at least 10 chars long
* with a specific format
* and geolocalizable using an external service

In such situations, you may encounter three issues:

* the ``Length`` or ``Regex`` constraints may fail hard with a :class:`Symfony\\Component\\Validator\\Exception\\UnexpectedValueException`
  exception if the actual value is not a string, as enforced by ``Type``.
* you may end with multiple error messages for the same property.
* you may perform a useless and heavy external call to geolocalize the address,
  while the format isn't valid.

You can validate each of these constraints sequentially to solve these issues:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Localization/Place.php
        namespace App\Localization;

        use App\Validator\Constraints as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;

        class Place
        {
            private const string ADDRESS_REGEX = '...';

            #[Assert\Sequentially([
                new Assert\NotNull,
                new Assert\Type('string'),
                new Assert\Length(min: 10),
                new Assert\Regex(Place::ADDRESS_REGEX),
                new AcmeAssert\Geolocalizable,
            ])]
            public string $address;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Localization\Place:
            properties:
                address:
                    - Sequentially:
                        - NotNull: ~
                        - Type:
                            value: string
                        - Length: { min: 10 }
                        - Regex:
                            pattern: !php/const App\Localization\Place::ADDRESS_REGEX
                        - App\Validator\Constraints\Geolocalizable: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Localization\Place">
                <property name="address">
                    <constraint name="Sequentially">
                            <constraint name="NotNull"/>
                            <constraint name="Type">
                                <option name="type">string</option>
                            </constraint>
                            <constraint name="Length">
                                <option name="min">10</option>
                            </constraint>
                            <constraint name="Regex">
                                <option name="pattern">/address-regex/</option>
                            </constraint>
                            <constraint name="App\Validator\Constraints\Geolocalizable"/>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Localization/Place.php
        namespace App\Localization;

        use App\Validator\Constraints as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Place
        {
            private const string ADDRESS_REGEX = '...';

            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('address', new Assert\Sequentially(
                    constraints: [
                        new Assert\NotNull(),
                        new Assert\Type('string'),
                        new Assert\Length(min: 10),
                        new Assert\Regex(self::ADDRESS_REGEX),
                        new AcmeAssert\Geolocalizable(),
                    ],
                ));
            }
        }

.. _reference-constraint-sequentially-conditional-cascading:

Conditionally Cascading Validation
----------------------------------

.. versionadded:: 8.2

    Support for nesting ``Valid`` in ``Sequentially`` was introduced in
    Symfony 8.2.

Sometimes a property can contain values of different types, but nested
constraints should run only after the value is confirmed to be an object of the
expected class. Combining the :doc:`Type </reference/constraints/Type>` and
:doc:`Valid </reference/constraints/Valid>` constraints separately does not
guarantee their execution order.

Nest ``Valid`` in ``Sequentially`` after the type guard to cascade validation
only when the guard succeeds:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/ImportRequest.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;

        class ImportRequest
        {
            #[Assert\Sequentially([
                new Assert\Type(ResourceInput::class),
                new Assert\Valid(),
            ])]
            public mixed $resource = null;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\ImportRequest:
            properties:
                resource:
                    - Sequentially:
                        constraints:
                            - Type: App\Model\ResourceInput
                            - Valid: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Model\ImportRequest">
                <property name="resource">
                    <constraint name="Sequentially">
                        <option name="constraints">
                            <constraint name="Type">
                                <option name="type">App\Model\ResourceInput</option>
                            </constraint>
                            <constraint name="Valid"/>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Model/ImportRequest.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class ImportRequest
        {
            public mixed $resource = null;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint(
                    'resource',
                    new Assert\Sequentially([
                        new Assert\Type(ResourceInput::class),
                        new Assert\Valid(),
                    ]),
                );
            }
        }

When ``$resource`` is not a ``ResourceInput``, the ``Type`` constraint adds a
violation and the sequence stops. When it has the expected type, ``Valid``
cascades into the object and validates its constraints.

The order is important. Place all guard constraints before ``Valid``. If
``Valid`` runs first for a scalar value, validation tries to load object
metadata and throws a
:class:`Symfony\\Component\\Validator\\Exception\\NoSuchMetadataException`
before a later type guard can stop the sequence.

A ``Valid`` constraint nested in ``Sequentially`` cannot define its own groups.
Set the groups on ``Sequentially`` instead. Those groups are also used to
validate the nested object. For example, if the sequence has only the ``import``
group, the nested object's ``import`` constraints run, but its ``Default``
constraints do not. Include ``Default`` in the sequence's groups when both
should run. Since the nested ``Valid`` cannot define groups, setting its
``restrictGroups`` option to ``false`` has no effect.

The nested ``Valid`` keeps its normal traversal behavior. Arrays are always
traversed, while its ``traverse`` option controls whether ``Traversable`` values
are traversed.

This composition can be used on properties and methods, or passed directly to
the validator. It cannot be used as a class-level constraint. ``Valid`` also
cannot be nested directly in other composite constraints. If needed, nest a
``Sequentially`` constraint containing ``Valid`` in the other composite.

Options
-------

``constraints``
~~~~~~~~~~~~~~~

**type**: ``array``

This required option is the array of validation constraints that you want
to apply sequentially.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
