PasswordStrength
================

Validates that the given password has reached the minimum strength required by
the constraint. The strength of the password is not evaluated with a set of
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

Customizing the Password Strength Estimation
--------------------------------------------

.. versionadded:: 7.2

    The feature to customize the password strength estimation was introduced in Symfony 7.2.

By default, this constraint calculates the strength of a password based on its
length and the number of unique characters used. You can get the calculated
password strength (e.g. to display it in the user interface) using the following
static function::

    use Symfony\Component\Validator\Constraints\PasswordStrengthValidator;

    $passwordEstimatedStrength = PasswordStrengthValidator::estimateStrength($password);

If you need to override the default password strength estimation algorithm, you
can pass a ``Closure`` to the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator`
constructor (e.g. using the :doc:`service closures </service_container/service_closures>`).

First, create a custom password strength estimation algorithm within a dedicated
callable class::

    namespace App\Validator;

    class CustomPasswordStrengthEstimator
    {
        /**
         * @return PasswordStrength::STRENGTH_*
         */
        public function __invoke(string $password): int
        {
            // Your custom password strength estimation algorithm
        }
    }

Then, configure the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator`
service to use your own estimator:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            custom_password_strength_estimator:
                class: App\Validator\CustomPasswordStrengthEstimator

            Symfony\Component\Validator\Constraints\PasswordStrengthValidator:
                arguments: [!service_closure '@custom_password_strength_estimator']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="custom_password_strength_estimator" class="App\Validator\CustomPasswordStrengthEstimator"/>

                <service id="Symfony\Component\Validator\Constraints\PasswordStrengthValidator">
                    <argument type="service_closure" id="custom_password_strength_estimator"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Validator\Constraints\PasswordStrengthValidator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set('custom_password_strength_estimator', CustomPasswordStrengthEstimator::class);

            $services->set(PasswordStrengthValidator::class)
                ->args([service_closure('custom_password_strength_estimator')]);
        };
