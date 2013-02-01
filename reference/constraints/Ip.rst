Ip
==

Validates that a value is a valid IP address. By default, this will validate
the value as IPv4, but a number of different options exist to validate as
IPv6 and many other combinations.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - `version`_                                                        |
|                | - `message`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Ip`             |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IpValidator`    |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                ipAddress:
                    - Ip:

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Ip
             */
             protected $ipAddress;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="ipAddress">
                <constraint name="Ip" />
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
                $metadata->addPropertyConstraint('ipAddress', new Assert\Ip());
            }
        }

Options
-------

version
~~~~~~~

**type**: ``string`` **default**: ``4``

This determines exactly *how* the ip address is validated and can take one
of a variety of different values:

**All ranges**

* ``4`` - Validates for IPv4 addresses
* ``6`` - Validates for IPv6 addresses
* ``all`` - Validates all IP formats

**No private ranges**

* ``4_no_priv`` - Validates for IPv4 but without private IP ranges
* ``6_no_priv`` - Validates for IPv6 but without private IP ranges
* ``all_no_priv`` - Validates for all IP formats but without private IP ranges

**No reserved ranges**

* ``4_no_res`` - Validates for IPv4 but without reserved IP ranges
* ``6_no_res`` - Validates for IPv6 but without reserved IP ranges
* ``all_no_res`` - Validates for all IP formats but without reserved IP ranges

**Only public ranges**

* ``4_public`` - Validates for IPv4 but without private and reserved ranges
* ``6_public`` - Validates for IPv6 but without private and reserved ranges
* ``all_public`` - Validates for all IP formats but without private and reserved ranges

message
~~~~~~~

**type**: ``string`` **default**: ``This is not a valid IP address``

This message is shown if the string is not a valid IP address.
