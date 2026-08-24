Cidr
====

Validates that a value is a valid `CIDR`_ (Classless Inter-Domain Routing) notation.
By default, this will validate the CIDR's IP and netmask both for version 4 and 6,
with the option of allowing only one type of IP version to be valid. It also supports
a minimum and maximum range constraint in which the value of the netmask is valid.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Cidr`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CidrValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/NetworkSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class NetworkSettings
        {
            #[Assert\Cidr]
            protected string $cidrNotation;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\NetworkSettings:
            properties:
                cidrNotation:
                    - Cidr: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\NetworkSettings">
                <property name="cidrNotation">
                    <constraint name="Cidr"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/NetworkSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class NetworkSettings
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('cidrNotation', new Assert\Cidr());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid CIDR notation.``

This message is shown if the string is not a valid CIDR notation.

``netmaskMin``
~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``0``

It's a constraint for the lowest value a valid netmask may have.

``netmaskMax``
~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``32`` for IPv4 or ``128`` for IPv6

It's a constraint for the biggest value a valid netmask may have.

``netmaskRangeViolationMessage``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``The value of the netmask should be between {{ min }} and {{ max }}.``

This message is shown if the value of the CIDR's netmask is bigger than the
``netmaskMax`` value or lower than the ``netmaskMin`` value.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ min }}``    The minimum value a CIDR netmask may have
``{{ max }}``    The maximum value a CIDR netmask may have
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``version``
~~~~~~~~~~~

**type**: ``string`` **default**: ``all``

This determines exactly *how* the CIDR notation is validated and can take one
of :ref:`IP version ranges <reference-constraint-ip-version>`.

.. note::

    The IP range checks (e.g., ``*_private``, ``*_reserved``) validate only the
    IP address, not the entire netmask. To improve validation, you can set the
    ``{{ min }}`` value for the netmask. For example, the range ``9.0.0.0/6`` is
    considered ``*_public``, but it also includes the ``10.0.0.0/8`` range, which
    is categorized as ``*_private``.

.. _`CIDR`: https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
