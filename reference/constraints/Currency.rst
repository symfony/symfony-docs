Currency
========

.. versionadded:: 2.3
    The ``Currency`` constraint was introduced in Symfony 2.3.

Validates that a value is a valid `3-letter ISO 4217`_ currency name.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                     |
+----------------+---------------------------------------------------------------------------+
| Options        | - `message`_                                                              |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Currency`             |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CurrencyValidator`    |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``currency`` property of an ``Order`` is a valid
currency, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EcommerceBundle/Resources/config/validation.yml
        Acme\EcommerceBundle\Entity\Order:
            properties:
                currency:
                    - Currency: ~

    .. code-block:: php-annotations

        // src/Acme/EcommerceBundle/Entity/Order.php
        namespace Acme\EcommerceBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\Currency
             */
            protected $currency;
        }

    .. code-block:: xml

        <!-- src/Acme/EcommerceBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\EcommerceBundle\Entity\Order">
                <property name="currency">
                    <constraint name="Currency" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/EcommerceBundle/Entity/Order.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('currency', new Assert\Currency());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid currency.``

This is the message that will be shown if the value is not a valid currency.

.. _`3-letter ISO 4217`: http://en.wikipedia.org/wiki/ISO_4217
