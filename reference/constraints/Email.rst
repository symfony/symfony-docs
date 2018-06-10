Email
=====

Validates that a value is a valid email address. The underlying value is
cast to a string before being validated.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `mode`_                                                           |
|                | - `message`_                                                        |
|                | - `checkMX`_                                                        |
|                | - `checkHost`_                                                      |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Email`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\EmailValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Email(
             *     message = "The email '{{ value }}' is not a valid email.",
             *     checkMX = true
             * )
             */
             protected $email;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                email:
                    - Email:
                        message: The email "{{ value }}" is not a valid email.
                        checkMX: true

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="email">
                    <constraint name="Email">
                        <option name="message">The email "{{ value }}" is not a valid email.</option>
                        <option name="checkMX">true</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('email', new Assert\Email(array(
                    'message' => 'The email "{{ value }}" is not a valid email.',
                    'checkMX' => true,
                )));
            }
        }

Options
-------

mode
~~~~

**type**: ``string`` **default**: ``loose``

This option is optional and defines the pattern the email address is validated against.
Valid values are:

* ``loose``
* ``strict``
* ``html5``

loose
.....

A simple regular expression. Allows all values with an "@" symbol in, and a "."
in the second host part of the email address.

strict
......

Uses the `egulias/email-validator`_ library to perform an RFC compliant
validation. You will need to install that library to use this mode.

html5
.....

This matches the pattern used for the `HTML5 email input element`_.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid email address.``

This message is shown if the underlying data is not a valid email address.

checkMX
~~~~~~~

**type**: ``boolean`` **default**: ``false``

If true, then the :phpfunction:`checkdnsrr` PHP function will be used to
check the validity of the MX record of the host of the given email.

checkHost
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If true, then the :phpfunction:`checkdnsrr` PHP function will be used to
check the validity of the MX *or* the A *or* the AAAA record of the host
of the given email.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _egulias/email-validator: https://packagist.org/packages/egulias/email-validator
.. _HTML5 email input element: https://www.w3.org/TR/html5/sec-forms.html#email-state-typeemail
