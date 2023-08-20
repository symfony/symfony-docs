How to Dynamically Configure Form Validation Groups
===================================================

Sometimes you need advanced logic to determine the validation groups. If they
can't be determined by a callback, you can use a service. Create a service
that implements ``__invoke()`` which accepts a ``FormInterface`` as a
parameter::

    // src/Validation/ValidationGroupResolver.php
    namespace App\Validation;

    use Symfony\Component\Form\FormInterface;

    class ValidationGroupResolver
    {
        public function __construct(
            private object $service1,
            private object $service2,
        ) {
        }

        public function __invoke(FormInterface $form): array
        {
            $groups = [];

            // ... determine which groups to apply and return an array

            return $groups;
        }
    }

Then in your form, inject the resolver and set it as the ``validation_groups``::

    // src/Form/MyClassType.php;
    namespace App\Form;

    use App\Validation\ValidationGroupResolver;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class MyClassType extends AbstractType
    {
        public function __construct(
            private ValidationGroupResolver $groupResolver,
        ) {
        }

        // ...
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'validation_groups' => $this->groupResolver,
            ]);
        }
    }

This will result in the form validator invoking your group resolver to set the
validation groups returned when validating.
