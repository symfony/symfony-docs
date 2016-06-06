How to Dynamically Configure Validation Groups
==============================================

Sometimes you need advanced logic to determine the validation groups. If this
can't be determined by a simple callback, you can use a service. Create a
service that implements ``__invoke`` which accepts a ``FormInterface`` as a
parameter.

.. code-block:: php

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

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Symfony\Component\Form\AbstractType
    use AppBundle\Validator\ValidationGroupResolver;

    class MyClassType extends AbstractType
    {
        /**
         * @var ValidationGroupResolver
         */
        private $groupResolver;

        public function __construct(ValidationGroupResolver $groupResolver)
        {
            $this->groupResolver = $groupResolver;
        }

        // ...
        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'validation_groups' => $this->groupResolver,
            ));
        }
    }

This will result in the form validator invoking your group resolver to set the
validation groups returned when validating.
