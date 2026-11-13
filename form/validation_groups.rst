Configuring Validation Groups in Forms
======================================

If the object handled in your form uses :doc:`validation groups </validation/groups>`,
you need to specify which validation group(s) the form should apply.

To define them when :ref:`creating forms in classes <creating-forms-in-classes>`,
use the ``configureOptions()`` method::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // ...
            'validation_groups' => ['registration'],
        ]);
    }

When :ref:`creating forms in controllers <creating-forms-in-controllers>`, pass
it as a form option::

    $form = $this->createFormBuilder($user, [
        'validation_groups' => ['registration'],
    ])->add(/* ... */);

In both cases, *only* the ``registration`` group will be used to validate the
object. To apply the ``registration`` group *and* all constraints not in any
other group, add the special ``Default`` group::

    [
        // ...
        'validation_groups' => ['Default', 'registration'],
    ]

.. note::

    You can use any name for your validation groups. Symfony recommends using
    "lower snake case" (e.g. ``foo_bar``), while automatically generated
    groups use "UpperCamelCase" (e.g. ``Default``, ``SomeClassName``).

.. _form-validation-groups-clicked-button:

Choosing Validation Groups Based on the Clicked Button
------------------------------------------------------

When your form has :ref:`multiple submit buttons <processing-forms-multiple-buttons>`, you
can change the validation group based on the clicked button. For example, in a
multi-step form like the following, you might want to skip validation when
returning to a previous step::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('nextStep', SubmitType::class)
        ->add('previousStep', SubmitType::class)
        ->getForm();

To do so, configure the validation groups of the ``previousStep`` button to
``false``, which is a special value that skips validation::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('previousStep', SubmitType::class, [
            'validation_groups' => false,
        ])
        ->getForm();

Now the form will skip your validation constraints when that button is clicked.
It will still validate basic integrity constraints, such as checking whether an
uploaded file was too large or whether you tried to submit text in a number field.

If you are building a full multi-step form, use
:doc:`form flows </form/form_flow>` instead of creating these buttons yourself.

Choosing Validation Groups Based on Submitted Data
--------------------------------------------------

To determine validation groups dynamically based on submitted data, use a
callback. This is called after the form is submitted, but before validation is
invoked. The callback receives the form object as its first argument::

    use App\Entity\Client;
    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form): array {
                $data = $form->getData();

                if (Client::TYPE_PERSON === $data->getType()) {
                    return ['Default', 'person'];
                }

                return ['Default', 'company'];
            },
        ]);
    }

.. note::

    Adding ``Default`` to the list of validation groups is common but not mandatory.
    See the main :doc:`article about validation groups </validation/groups>` to
    learn more about validation groups and the default constraints.

You can also pass a static class method callback::

    'validation_groups' => [Client::class, 'determineValidationGroups']

Choosing Validation Groups via a Service
----------------------------------------

If validation group logic requires services or can't fit in a closure, use a
dedicated validation group resolver service. The class of this service must
be invokable and receive the form object as its first argument::

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

            // ... determine which groups to return

            return $groups;
        }
    }

Then use the service in your form type::

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

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'validation_groups' => $this->groupResolver,
            ]);
        }
    }

Learn More
----------

For more information about how validation groups work, see
:doc:`/validation/groups`. To apply validation groups in order and stop at the
first one that fails, see :doc:`/validation/sequence_provider`.
