UserPassword
============

.. versionadded:: 2.1
   This constraint is new in version 2.1.

This validates that an input value is equal to the current authenticated
user's password. This is useful in a form where a user can change his password,
but needs to enter his old password for security.

.. note::

    This should **not** be used to validate a login form, since this is done
    automatically by the security system.

When applied to an array (or Traversable object), this constraint allows
you to apply a collection of constraints to each element of the array.

+----------------+-------------------------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                                     |
+----------------+-------------------------------------------------------------------------------------------+
| Options        | - `message`_                                                                              |
+----------------+-------------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Security\\Core\\Validator\\Constraint\\UserPassword`          |
+----------------+-------------------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Security\\Core\\Validator\\Constraint\\UserPasswordValidator` |
+----------------+-------------------------------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have a `PasswordChange` class, that's used in a form where the
user can change his password by entering his old password and a new password.
This constraint will validate that the old password matches the user's current
password:

.. configuration-block::

    .. code-block:: yaml

        # src/UserBundle/Resources/config/validation.yml
        Acme\UserBundle\Form\Model\ChangePassword:
            properties:
                oldPassword:
                    - Symfony\Component\Security\Core\Validator\Constraint\UserPassword:
                        message: "Wrong value for your current password"

    .. code-block:: php-annotations

       // src/Acme/UserBundle/Form/Model/ChangePassword.php
       namespace Acme\UserBundle\Form\Model;
       
       use Symfony\Component\Security\Core\Validator\Constraint as SecurityAssert;

       class ChangePassword
       {
           /**
            * @SecurityAssert\UserPassword(
            *     message = "Wrong value for your current password"
            * )
            */
            protected $oldPassword;
       }

Options
-------

message
~~~~~~~

**type**: ``message`` **default**: ``This value should be the user current password``

This is the message that's displayed when the underlying string does *not*
match the current user's password.
