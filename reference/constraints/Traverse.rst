Traverse
========

Objects do not validate nested objects by default unless explicitly using
this constraint.
If only specific nested objects should be validated by cascade, consider
using the :doc:`/reference/constraints/Valid` instead.

+----------------+-------------------------------------------------------------------------------------+
| Applies to     | :ref:`class <validation-class-target>`                                              |
+----------------+-------------------------------------------------------------------------------------+
| Options        | - `payload`_                                                                        |
+----------------+-------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Traverse`                       |
+----------------+-------------------------------------------------------------------------------------+

Basic Usage
-----------

In the following example, create three classes ``Book``, ``Author`` and
``Editor`` that all have constraints on their properties. Furthermore,
``Book`` stores an ``Author`` and an ``Editor`` instance that must be
valid too. Instead of adding the ``Valid`` constraint to both fields,
configure the ``Traverse`` constraint on the ``Book`` class.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Book.php
        namespace AppBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @ORM\Entity
         * @Assert\Traverse
         */
        class Book
        {
            /**
             * @var Author
             *
             * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Author")
             */
            protected $author;

            /**
             * @var Editor
             *
             * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Editor")
             */
            protected $editor;

            // ...
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Book:
            constraints:
                - Symfony\Component\Validator\Constraints\Traverse: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Book">
                <constraint name="Symfony\Component\Validator\Constraints\Traverse"/>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Book.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Book
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Traverse());
            }
        }

Options
-------

.. include:: /reference/constraints/_payload_option.rst.inc
