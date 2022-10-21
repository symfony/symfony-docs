NotCompromisedPassword
======================

Validates that the given password has not been compromised by checking that it is
not included in any of the public data breaches tracked by `haveibeenpwned.com`_.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\NotCompromisedPassword`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\NotCompromisedPasswordValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the ``rawPassword`` property of the
``User`` class doesn't store a compromised password:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\NotCompromisedPassword]
            protected $rawPassword;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                rawPassword:
                    - NotCompromisedPassword

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="rawPassword">
                    <constraint name="NotCompromisedPassword"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('rawPassword', new Assert\NotCompromisedPassword());
            }
        }

In order to make the password validation, this constraint doesn't send the raw
password value to the ``haveibeenpwned.com`` API. Instead, it follows a secure
process known as `k-anonymity password validation`_.

In practice, the raw password is hashed using SHA-1 and only the first bytes of
the hash are sent. Then, the ``haveibeenpwned.com`` API compares those bytes
with the SHA-1 hashes of all leaked passwords and returns the list of hashes
that start with those same bytes. That's how the constraint can check if the
password has been compromised without fully disclosing it.

For example, if the password is ``test``, the entire SHA-1 hash is
``a94a8fe5ccb19ba61c4c0873d391e987982fbbd3`` but the validator only sends
``a94a8`` to the ``haveibeenpwned.com`` API.

.. seealso::

    When using this constraint inside a Symfony application, define the
    :ref:`not_compromised_password <reference-validation-not-compromised-password>`
    option to avoid making HTTP requests in the ``dev`` and ``test`` environments.

Available Options
-----------------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This password has been leaked in a data breach, it must not be used. Please use another password.``

The default message supplied when the password has been compromised.

.. include:: /reference/constraints/_payload-option.rst.inc

``skipOnError``
~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When the HTTP request made to the ``haveibeenpwned.com`` API fails for any
reason, an exception is thrown (no validation error is displayed). Set this
option to ``true`` to not throw the exception and consider the password valid.

``threshold``
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``1``

This value defines the number of times a password should have been leaked
publicly to consider it compromised. Think carefully before setting this option
to a higher value because it could decrease the security of your application.

.. _`haveibeenpwned.com`: https://haveibeenpwned.com/
.. _`k-anonymity password validation`: https://blog.cloudflare.com/validating-leaked-passwords-with-k-anonymity/
