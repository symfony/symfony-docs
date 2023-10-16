PasswordStrength
================

Validates that the given password has reached the minimum strength required by
the constraint. The strengh of the password is not evaluated with a set of
predefined rules (include a number, use lowercase and uppercase characters,
etc.) but by measuring the entropy of the password based on its length and the
number of unique characters used.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrength`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensures that the ``rawPassword`` property of the
``User`` class reaches the minimum strength required by the constraint.
By default, the minimum required score is ``2``.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\PasswordStrength]
            protected $rawPassword;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                rawPassword:
                    - PasswordStrength

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="rawPassword">
                    <constraint name="PasswordStrength"></constraint>
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
                $metadata->addPropertyConstraint('rawPassword', new Assert\PasswordStrength());
            }
        }

Available Options
-----------------

``minScore``
~~~~~~~~~~~~

**type**: ``integer`` **default**: ``PasswordStrength::STRENGTH_MEDIUM`` (``2``)

The minimum required strength of the password. Available constants are:

* ``PasswordStrength::STRENGTH_WEAK`` = ``1``
* ``PasswordStrength::STRENGTH_MEDIUM`` = ``2``
* ``PasswordStrength::STRENGTH_STRONG`` = ``3``
* ``PasswordStrength::STRENGTH_VERY_STRONG`` = ``4``

``PasswordStrength::STRENGTH_VERY_WEAK`` is available but only used internally
or by a custom password strength estimator.

.. code-block:: php-attributes

    // src/Entity/User.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class User
    {
        #[Assert\PasswordStrength([
            'minScore' => PasswordStrength::STRENGTH_VERY_STRONG, // Very strong password required
        ])]
        protected $rawPassword;
    }

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``The password strength is too low. Please use a stronger password.``

The default message supplied when the password does not reach the minimum required score.

.. code-block:: php-attributes

    // src/Entity/User.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class User
    {
        #[Assert\PasswordStrength([
            'message' => 'Your password is too easy to guess. Company\'s security policy requires to use a stronger password.'
        ])]
        protected $rawPassword;
    }
