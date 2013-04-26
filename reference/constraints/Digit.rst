Digit
=====

Validates that a value consists of numeric characters only.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Digit`            |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DigitValidator`   |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

To verify that the ``number`` property of an ``Article`` class consists of
numeric characters only, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/ShopBundle/Resources/config/validation.yml
        Acme\ShopBundle\Entity\Article:
            properties:
                number:
                    - Digit: ~

    .. code-block:: php-annotations

        // src/Acme/ShopBundle/Entity/Article.php
        namespace Acme\ShopBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Article
        {
            /**
             * @Assert\Digit()
             */
            protected $number;
        }

    .. code-block:: xml

        <!-- src/Acme/ShopBundle/Resources/config/validation.xml -->
        <class name="Acme\ShopBundle\Entity\Article">
            <property name="number">
                <constraint name="Digit" />
            </property>
        </class>

    .. code-block:: php

        // src/Acme/ShopBundle/Entity/Article.php
        namespace Acme\ShopBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Article
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('number', new Assert\Digit());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value does not consist of numeric characters only.``

This is the message that will be shown if the value does not consist of numeric characters only.
