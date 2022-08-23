All
===

When applied to an array (or Traversable object), this constraint allows
you to apply a collection of constraints to each element of the array.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\All`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\AllValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose that you have an array of strings and you want to validate each
entry in that array:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        // IMPORTANT: nested attributes require PHP 8.1 or higher
        class User
        {
            #[Assert\All([
                new Assert\NotBlank,
                new Assert\Length(min: 5),
            ])]
            protected $favoriteColors = [];
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                favoriteColors:
                    - All:
                        - NotBlank:  ~
                        - Length:
                            min: 5

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="favoriteColors">
                    <constraint name="All">
                        <option name="constraints">
                            <constraint name="NotBlank"/>
                            <constraint name="Length">
                                <option name="min">5</option>
                            </constraint>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('favoriteColors', new Assert\All([
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['min' => 5]),
                    ],
                ]));
            }
        }

Now, each entry in the ``favoriteColors`` array will be validated to not
be blank and to be at least 5 characters long.

Options
-------

``constraints``
~~~~~~~~~~~~~~~

**type**: ``array`` [:ref:`default option <validation-default-option>`]

This required option is the array of validation constraints that you want
to apply to each element of the underlying array.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
