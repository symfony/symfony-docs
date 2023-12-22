Cascade
=======

The Cascade constraint is used to validate a whole class, including all the
objects that might be stored in its properties. Thanks to this constraint,
you don't need to add the :doc:`/reference/constraints/Valid` constraint on
every child object that you want to validate in your class.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Cascade`
==========  ===================================================================

Basic Usage
-----------

In the following example, the
:class:`Symfony\\Component\\Validator\\Constraints\\Cascade` constraint
will tell the validator to validate all properties of the class, including
constraints that are set in the child classes ``BookMetadata`` and
``Author``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/BookCollection.php
        namespace App\Model;

        use App\Model\Author;
        use App\Model\BookMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        #[Assert\Cascade]
        class BookCollection
        {
            #[Assert\NotBlank]
            protected string $name = '';

            public BookMetadata $metadata;

            public Author $author;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\BookCollection:
            constraints:
                - Cascade: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\BookCollection">
                <constraint name="Cascade"/>
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
                $metadata->addConstraint(new Assert\Cascade());
            }
        }

Options
-------

The ``groups`` option is not available for this constraint.

``exclude``
~~~~~~~~~~~

**type**: ``array`` | ``string`` **default**: ``null``

This option can be used to exclude one or more properties from the
cascade validation.

.. include:: /reference/constraints/_payload-option.rst.inc
