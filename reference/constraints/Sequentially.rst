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

    Support for the ``Valid`` constraint inside ``Sequentially`` was introduced
    in Symfony 8.2.

When a property can hold values of different types (e.g. user input that can be
a string or an object), you may want to validate the nested object only after
checking that it's an instance of the expected class. Applying the
:doc:`Type </reference/constraints/Type>` and
:doc:`Valid </reference/constraints/Valid>` constraints separately doesn't
work, because both always run. Instead, add ``Valid`` after ``Type`` inside
``Sequentially``:

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

If ``$resource`` is not a ``ResourceInput`` object, ``Type`` adds a violation
and the sequence stops. Otherwise, ``Valid`` validates the constraints of that
object. Always add ``Valid`` after the constraints that check the value type;
if ``Valid`` runs first on a scalar value, an exception is thrown.

The nested ``Valid`` constraint can't define the ``groups`` option. Define it
in ``Sequentially`` instead. These groups are also the ones used to validate
the nested object. For example, with ``groups: ['import']``, only the
``import`` constraints of the nested object run; add ``Default`` to that list
to also run its ``Default`` constraints.

``Valid`` can't be used directly inside other composite constraints (such as
``All`` or ``AtLeastOneOf``). Instead, wrap it in a ``Sequentially`` constraint
and add that to the other composite constraint.

Options
-------

``constraints``
~~~~~~~~~~~~~~~

**type**: ``array``

This required option is the array of validation constraints that you want
to apply sequentially.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
