Date
====

Validates that a value is a valid date, meaning either a ``DateTime`` object
or a string (or an object that can be cast into a string) that follows a
valid YYYY-MM-DD format.

+----------------+--------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`              |
+----------------+--------------------------------------------------------------------+
| Options        | - `message`_                                                       |
+----------------+--------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Date`          |
+----------------+--------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateValidator` |
+----------------+--------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                birthday:
                    - Date: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Date()
             */
             protected $birthday;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="birthday">
                <constraint name="Date" />
            </property>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('birthday', new Assert\Date());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid date``

This message is shown if the underlying data is not a valid date.
