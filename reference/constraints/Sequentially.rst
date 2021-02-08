Sequentially
============

This constraint allows you to apply a set of rules that should be validated
step-by-step, allowing to interrupt the validation once the first violation is raised.

As an alternative in situations ``Sequentially`` cannot solve, you may consider
using :doc:`GroupSequence </validation/sequence_provider>` which allows more control.

.. versionadded:: 5.1

    The ``Sequentially`` constraint was introduced in Symfony 5.1.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `constraints`_
            - `groups`_
            - `payload`_
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

    .. code-block:: php-annotations

        // src/Localization/Place.php
        namespace App\Localization;

        use App\Validator\Constraints as AcmeAssert;
        use Symfony\Component\Validator\Constraints as Assert;

        class Place
        {
            /**
             * @var string
             *
             * @Assert\Sequentially({
             *     @Assert\NotNull(),
             *     @Assert\Type("string"),
             *     @Assert\Length(min=10),
             *     @Assert\Regex(Place::ADDRESS_REGEX),
             *     @AcmeAssert\Geolocalizable(),
             * })
             */
            public $address;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Localization\Place:
            properties:
                address:
                    - Sequentially:
                        - NotNull: ~
                        - Type: string
                        - Length: { min: 10 }
                        - Regex: !php/const App\Localization\Place::ADDRESS_REGEX
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
                            <constraint name="Type">string</constraint>
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('address', new Assert\Sequentially([
                    new Assert\NotNull(),
                    new Assert\Type('string'),
                    new Assert\Length(['min' => 10]),
                    new Assert\Regex(self::ADDRESS_REGEX),
                    new AcmeAssert\Geolocalizable(),
                ]));
            }
        }

Options
-------

``constraints``
~~~~~~~~~~~~~~~

**type**: ``array`` [:ref:`default option <validation-default-option>`]

This required option is the array of validation constraints that you want
to apply sequentially.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
