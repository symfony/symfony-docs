Email
=====

Validates that a value is a valid phone number.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                    |
+----------------+---------------------------------------------------------------------------+
| Options        | - `region`_                                                               |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\PhoneNumber`          |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\PhoneNumberValidator` |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                phoneNumber:
                    - PhoneNumber:
                        message: This value is not a valid phone number.
                        region: FR

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\PhoneNumber(
             *     message = "This value is not a valid phone number.",
             *     region = FR
             * )
             */
             protected $phoneNumber;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <property name="phoneNumber">
                    <constraint name="PhoneNumber">
                        <option name="message">This value is not a valid phone number.</option>
                        <option name="region">FR</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('phoneNumber', new Assert\PhoneNumber(array(
                    'message' => 'This value is not a valid phone number.',
                    'region' => FR,
                )));
            }
        }

Options
-------

.. versionadded:: 2.6
    The PhoneNumber constraint was introduced in Symfony 2.6.

region
~~~~~~

**type**: ``string`` **default**: ``null``

When null, it does not parse local phone number. If you entered a value (eg: 'FR'),
it will be able to parse and validate phone number.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid phone number.``

This message is shown if the underlying data is not a valid phone number.

.. note:: PhoneNumber constraint rely on third-party library : https://github.com/giggsey/libphonenumber-for-php. It's require and you should install it via Composer.
