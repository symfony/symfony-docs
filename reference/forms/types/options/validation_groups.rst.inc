validation_groups
~~~~~~~~~~~~~~~~~

**type**: ``array``, ``string``, ``callable`` or ``null`` **default**: ``null``

This option is only valid on the root form and is used to specify which
groups will be used by the validator.

For ``null`` the validator will just use the ``Default`` group.

If you specify the groups as array or string they will used by the validator
as they are::

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => 'Registration',
        ));
    }

This is equivalent to passing the group as array::

    'validation_groups' => array('Registration'),

If the validation groups depend on the form's data a callable may be passed to
the option. Symfony then will pass the form when calling it::

    use Symfony\Component\Form\FormInterface;
    
    // ...
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $entity = $form->getData();
                return $entity->getValidationGroups();
            },
        ));
    }

The class that the form is used for may look like this::

    class Account
    {
        public function getValidationGroups()
        {
            return $this->isUser() ? array('User') : array('Company');
        }
    }

You can read more about this in :doc:`/form/data_based_validation`.
