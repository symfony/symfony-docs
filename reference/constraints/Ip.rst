Ip
==

Validates that a value is a valid IP address. By default, this will validate
the value as IPv4, but a number of different options exist to validate as
IPv6 and many other combinations.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Ip`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IpValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Ip]
            protected string $ipAddress;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                ipAddress:
                    - Ip: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="ipAddress">
                    <constraint name="Ip"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('ipAddress', new Assert\Ip());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This is not a valid IP address.``

This message is shown if the string is not a valid IP address.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

``version``
~~~~~~~~~~~

**type**: ``string`` **default**: ``4``

This determines exactly *how* the IP address is validated and can take one
of a variety of different values:

**All ranges**

``4` (`Assert\Ip::V4`)`
    Validates for IPv4 addresses
``6` (`Assert\Ip::V6`)`
    Validates for IPv6 addresses
``all` (`Assert\Ip::ALL`)`
    Validates all IP formats

**No public ranges**

``4_no_public` (`Assert\Ip::V4_NO_PUBLIC`)`
    Validates for IPv4 but without public IP ranges
``6_no_public` (`Assert\Ip::V6_NO_PUBLIC`)`
    Validates for IPv6 but without public IP ranges
``all_no_public` (`Assert\Ip::ALL_NO_PUBLIC`)`
    Validates for all IP formats but without public IP ranges

**No private ranges**

``4_no_private` (`Assert\Ip::V4_NO_PRIVATE`)`
    Validates for IPv4 but without private IP ranges
``6_no_private` (`Assert\Ip::V6_NO_PRIVATE`)`
    Validates for IPv6 but without private IP ranges
``all_no_private` (`Assert\Ip::ALL_NO_PRIVATE`)`
    Validates for all IP formats but without private IP ranges

**No reserved ranges**

``4_no_reserved` (`Assert\Ip::V4_NO_RESERVED`)`
    Validates for IPv4 but without reserved IP ranges
``6_no_reserved` (`Assert\Ip::V6_NO_RESERVED`)`
    Validates for IPv6 but without reserved IP ranges
``all_no_reserved` (`Assert\Ip::ALL_NO_RESERVED`)`
    Validates for all IP formats but without reserved IP ranges

**Only public ranges**

``4_public` (`Assert\Ip::V4_ONLY_PUBLIC`)`
    Validates for IPv4 but without private and reserved ranges
``6_public` (`Assert\Ip::V6_ONLY_PUBLIC`)`
    Validates for IPv6 but without private and reserved ranges
``all_public` (`Assert\Ip::ALL_ONLY_PUBLIC`)`
    Validates for all IP formats but without private and reserved ranges

**Only private ranges**

``4_private` (`Assert\Ip::V4_ONLY_PRIVATE`)`
    Validates for IPv4 but without public and reserved ranges
``6_private` (`Assert\Ip::V6_ONLY_PRIVATE`)`
    Validates for IPv6 but without public and reserved ranges
``all_private` (`Assert\Ip::ALL_ONLY_PRIVATE`)`
    Validates for all IP formats but without public and reserved ranges

**Only reserved ranges**

``4_reserved` (`Assert\Ip::V4_ONLY_RESERVED`)`
    Validates for IPv4 but without public and private ranges
``6_reserved` (`Assert\Ip::V6_ONLY_RESERVED`)`
    Validates for IPv6 but without public and private ranges
``all_reserved` (`Assert\Ip::ALL_ONLY_RESERVED`)`
    Validates for all IP formats but without public and private ranges
