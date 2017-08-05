How to Dynamically Configure Form Validation Groups
==============================================

Sometimes you need advanced logic to determine the validation groups. If they
can't be determined by a simple callback, you can use a service. Create a
service that implements ``__invoke()`` which accepts a ``FormInterface`` as a
parameter.

.. code-block:: php

    // src/AppBundle/Validation/ValidationGroupResolver.php
    namespace AppBundle\Validation;

    use Symfony\Component\Form\FormInterface;

    class ValidationGroupResolver
    {
        private $service1;

        private $service2;

        public function __construct($service1, $service2)
        {
            $this->service1 = $service1;
            $this->service2 = $service2;
        }

        /**
         * @param FormInterface $form
         * @return array
         */
        public function __invoke(FormInterface $form)
        {
            $groups = array();

            // ... determine which groups to apply and return an array

            return $groups;
        }
    }

Then in your form, inject the resolver and set it as the ``validation_groups``.

.. code-block:: php

    // src/AppBundle/Form/MyClassType.php;
    namespace AppBundle\Form;

    use AppBundle\Validator\ValidationGroupResolver;
    use Symfony\Component\Form\AbstractType
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class MyClassType extends AbstractType
    {
        private $groupResolver;

        public function __construct(ValidationGroupResolver $groupResolver)
        {
            $this->groupResolver = $groupResolver;
        }

        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'validation_groups' => $this->groupResolver,
            ));
        }
    }

This will result in the form validator invoking your group resolver to set the
validation groups returned when validating.
