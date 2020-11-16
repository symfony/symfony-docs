UserPassword
============

This validates that an input value is equal to the current authenticated
user's password. This is useful in a form where a user can change their
password, but needs to enter their old password for security.

.. note::

    This should **not** be used to validate a login form, since this is
    done automatically by the security system.

.. note::

    In order to use this constraints, you should have installed the
    symfony/security-core component with Composer.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPassword`
Validator   :class:`Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPasswordValidator`
==========  ===================================================================

Basic Usage
-----------

Suppose you have a ``ChangePassword`` class, that's used in a form where
the user can change their password by entering their old password and a
new password. This constraint will validate that the old password matches
the user's current password:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Form/Model/ChangePassword.php
        namespace App\Form\Model;

        use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

        class ChangePassword
        {
            /**
             * @SecurityAssert\UserPassword(
             *     message = "Wrong value for your current password"
             * )
             */
            protected $oldPassword;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Form\Model\ChangePassword:
            properties:
                oldPassword:
                    - Symfony\Component\Security\Core\Validator\Constraints\UserPassword:
                        message: 'Wrong value for your current password'

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Form\Model\ChangePassword">
                <property name="oldPassword">
                    <constraint
                        name="Symfony\Component\Security\Core\Validator\Constraints\UserPassword"
                    >
                        <option name="message">Wrong value for your current password</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Form/Model/ChangePassword.php
        namespace App\Form\Model;

        use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class ChangePassword
        {
            public static function loadValidatorData(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint(
                    'oldPassword',
                    new SecurityAssert\UserPassword([
                        'message' => 'Wrong value for your current password',
                    ])
                );
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``message`` **default**: ``This value should be the user current password.``

This is the message that's displayed when the underlying string does *not*
match the current user's password.

This message has no parameters.

.. include:: /reference/constraints/_payload-option.rst.inc
