.. index::
    single: Forms; Validation groups based on submitted data

How to Choose Validation Groups Based on the Submitted Data
===========================================================

If you need some advanced logic to determine the validation groups (e.g.
based on submitted data), you can set the ``validation_groups`` option
to an array callback::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array(
                'AppBundle\Entity\Client',
                'determineValidationGroups',
            ),
        ));
    }

This will call the static method ``determineValidationGroups()`` on the
``Client`` class after the form is submitted, but before validation is executed.
The Form object is passed as an argument to that method (see next example).
You can also define whole logic inline by using a ``Closure``::

    use AppBundle\Entity\Client;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if (Client::TYPE_PERSON == $data->getType()) {
                    return array('person');
                }

                return array('company');
            },
        ));
    }

Using the ``validation_groups`` option overrides the default validation
group which is being used. If you want to validate the default constraints
of the entity as well you have to adjust the option as follows::

    use AppBundle\Entity\Client;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                if (Client::TYPE_PERSON == $data->getType()) {
                    return array('Default', 'person');
                }

                return array('Default', 'company');
            },
        ));
    }

You can find more information about how the validation groups and the default constraints
work in the book section about :doc:`validation groups </validation/groups>`.
