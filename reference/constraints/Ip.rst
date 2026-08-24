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

.. _reference-constraint-ip-version:

``version``
~~~~~~~~~~~

**type**: ``string`` **default**: ``4``

This determines exactly *how* the IP address is validated. This option defines a
lot of different possible values based on the ranges and the type of IP address
that you want to allow/deny:

====================  ===================  ===================  ==================
Ranges Allowed        IPv4 addresses only  IPv6 addresses only  Both IPv4 and IPv6
====================  ===================  ===================  ==================
All                   ``4``                ``6``                ``all``
All except private    ``4_no_priv``        ``6_no_priv``        ``all_no_priv``
All except reserved   ``4_no_res``         ``6_no_res``         ``all_no_res``
All except public     ``4_no_public``      ``6_no_public``      ``all_no_public``
Only private          ``4_private``        ``6_private``        ``all_private``
Only reserved         ``4_reserved``       ``6_reserved``       ``all_reserved``
Only public           ``4_public``         ``6_public``         ``all_public``
====================  ===================  ===================  ==================
