``validation_groups``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array``, ``string``, ``callable``, :class:`Symfony\\Component\\Validator\\Constraints\\GroupSequence` or ``null`` **default**: ``null``

This option is only valid on the root form and is used to specify which
groups will be used by the validator.

For ``null`` the validator will just use the ``Default`` group.

If you specify the groups as an array or string they will be used by the
validator as they are::

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => 'Registration',
        ]);
    }

This is equivalent to passing the group as array::

    'validation_groups' => ['Registration'],

The form's data will be :doc:`validated against all given groups </form/validation_groups>`.

If the validation groups depend on the form's data a callable may be passed to
the option. Symfony will then pass the form when calling it::

    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => function (FormInterface $form) {
                $entity = $form->getData();

                return $entity->isUser() ? ['User'] : ['Company'];
            },
        ]);
    }

.. seealso::

    You can read more about this in :doc:`/form/data_based_validation`.

.. note::

    When your form contains multiple submit buttons, you can change the
    validation group depending on :doc:`which button is used </form/button_based_validation>`
    to submit the form.

    If you need advanced logic to determine the validation groups have
    a look at :doc:`/form/validation_group_service_resolver`.

In some cases, you want to validate your groups step by step. To do this, you
can pass a :class:`Symfony\\Component\\Validator\\Constraints\\GroupSequence`
to this option. This enables you to validate against multiple groups,
like when you pass multiple groups in an array, but with the difference that
a group is only validated if the previous groups pass without errors.
Here's an example::

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Validator\Constraints\GroupSequence;
    // ...

    class MyType extends AbstractType
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'validation_groups' => new GroupSequence(['First', 'Second']),
            ]);
        }
    }

.. seealso::

    Read the article :doc:`/validation/sequence_provider` to find out more about this.
