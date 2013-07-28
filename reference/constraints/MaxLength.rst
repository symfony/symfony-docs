MaxLength
=========

.. caution::

    The MaxLength constraint is deprecated since version 2.1 and will be removed
    in Symfony 2.3. Use :doc:`/reference/constraints/Length` with the ``max``
    option instead.

Validates that the length of a string is not larger than the given limit.

+----------------+-------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                   |
+----------------+-------------------------------------------------------------------------+
| Options        | - `limit`_                                                              |
|                | - `message`_                                                            |
|                | - `charset`_                                                            |
+----------------+-------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\MaxLength`          |
+----------------+-------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\MaxLengthValidator` |
+----------------+-------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Blog:
            properties:
                summary:
                    - MaxLength: 100
    
    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Blog.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Blog
        {
            /**
             * @Assert\MaxLength(100)
             */
            protected $summary;
        }
    
    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Blog">
                <property name="summary">
                    <constraint name="MaxLength">
                        <option name="limit">100</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Blog.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Blog
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('summary', new Assert\MaxLength(array(
                    'limit' => 100,
                )));
            }
        }

Options
-------

limit
~~~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "max" value. Validation will fail if the length
of the give string is **greater** than this number.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should have {{ limit }} characters or less``

The message that will be shown if the underlying string has a length that
is longer than the `limit`_ option.

charset
~~~~~~~

**type**: ``charset`` **default**: ``UTF-8``

If the PHP extension "mbstring" is installed, then the PHP function :phpfunction:`mb_strlen`
will be used to calculate the length of the string. The value of the ``charset``
option is passed as the second argument to that function.
