Email
=====

Validates that a value is a valid email address. The underlying value is
cast to a string before being validated.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `profile`_                                                        |
|                | - `strict`_                                                         |
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

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                email:
                    - Email:
                        message: The email "{{ value }}" is not a valid email.
                        checkMX: true

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="email">
                    <constraint name="Email">
                        <option name="message">The email "{{ value }}" is not a valid email.</option>
                        <option name="checkMX">true</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

.. versionadded:: 3.1
    The ``profile`` option was added in Symfony 3.1

profile
~~~~~~~

**type**: ``string`` **default**: ``basic``

This option is used to specify exactly which type of email validation should
be performed. It can be set to one of the following values:

``basic``
    Validation will occur against a simple regular expression.
``html5``
    Validation will occur against the regular expression defined in
    the `HTML5 spec`_.
``rfc``
    The `egulias/email-validator`_ library will perform an RFC-compliant
    validation in non-strict mode. If any warning messages regarding
    unusual/deprecated email address features are raised during validation,
    they will not prevent an otherwise-valid email from being marked as
    invalid.
``rfc-no-warn``
    The `egulias/email-validator`_ library will perform an RFC-compliant
    validation in strict mode. If any warning messages regarding
    unusual/deprecated email address features are raised during validation,
    the email address will be considered invalid.

strict
~~~~~~

**type**: ``boolean`` **default**: ``false``

DEPRECATED. When true, the email validator will use the strict
'rfc-no-warn' profile.

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

.. _HTML5 spec: https://www.w3.org/TR/html-markup/datatypes.html#form.data.emailaddress-def