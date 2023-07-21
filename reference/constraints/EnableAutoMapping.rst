EnableAutoMapping
=================

This constraint allows to enable :ref:`Doctrine's auto mapping <doctrine_auto-mapping>`
on a class or a property. Automapping allows to determine validation rules based
on Doctrine's attributes. You may use this constraint when
automapping is globally disabled, but you still want to enable this feature for
a class or a property specifically.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\EnableAutoMapping`
==========  ===================================================================

Basic Usage
-----------

In the following example, the
:class:`Symfony\\Component\\Validator\\Constraints\\EnableAutoMapping`
constraint will tell the validator to gather constraints from Doctrine's
metadata:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/BookCollection.php
        namespace App\Model;

        use App\Model\Author;
        use App\Model\BookMetadata;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Component\Validator\Constraints as Assert;

        #[Assert\EnableAutoMapping]
        class BookCollection
        {
            #[ORM\Column(nullable: false)]
            protected string $name = '';

            #[ORM\ManyToOne(targetEntity: Author::class)]
            public Author $author;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\BookCollection:
            constraints:
                - EnableAutoMapping: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\BookCollection">
                <constraint name="EnableAutoMapping"/>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/BookCollection.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class BookCollection
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new Assert\EnableAutoMapping());
            }
        }

Options
-------

The ``groups`` option is not available for this constraint.

.. include:: /reference/constraints/_payload-option.rst.inc
