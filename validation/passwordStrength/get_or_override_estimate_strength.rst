How to Get or Override the Default Password Strength Estimation Algorithm
=========================================================================

Within the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator` a `dedicated function`_ is used to estimate the strength of the given password. This function can be retrieved directly from the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator` class and can also be overridden.

Get the default Password strength
---------------------------------

In case of need to retrieve the actual strength of a password (e.g. compute the score and display it when the user has defined a password), the ``estimateStrength`` `dedicated function`_ of the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator` is a public static function, therefore this function can be retrieved directly from the `PasswordStrengthValidator` class.::

        use Symfony\Component\Validator\Constraints\PasswordStrengthValidator;

        $passwordEstimatedStrength = PasswordStrengthValidator::estimateStrength($password);


Override the default Password strength estimation algorithm
-----------------------------------------------------------

If you need to override the default password strength estimation algorithm, you can pass a ``Closure`` to the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator` constructor. This can be done using the :doc:`/service_container/service_closures`.

First, create a custom password strength estimation algorithm within a dedicated callable class.::

        namespace App\Validator;

        class CustomPasswordStrengthEstimator
        {
            /**
             * @param string $password
             *
             * @return PasswordStrength::STRENGTH_*
             */
            public function __invoke(string $password): int
            {
                // Your custom password strength estimation algorithm
            }
        }

Then, configure the :class:`Symfony\\Component\\Validator\\Constraints\\PasswordStrengthValidator` service to use the custom password strength estimation algorithm.::

        # config/services.yaml
        services:
            custom_password_strength_estimator:
                class: App\Validator\CustomPasswordStrengthEstimator

            Symfony\Component\Validator\Constraints\PasswordStrengthValidator:
                arguments: [!service_closure '@custom_password_strength_estimator']

.. _`dedicated function`: https://github.com/symfony/symfony/blob/85db734e06e8cb32365810958326d48084bf48ba/src/Symfony/Component/Validator/Constraints/PasswordStrengthValidator.php#L53-L90
