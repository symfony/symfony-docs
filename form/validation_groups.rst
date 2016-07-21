.. index::
    single: Forms; Validation groups

How to Define the Validation Groups to Use
==========================================

Validation Groups
-----------------

If your object takes advantage of :doc:`validation groups </validation/groups>`,
you'll need to specify which validation group(s) your form should use::

    $form = $this->createFormBuilder($users, array(
        'validation_groups' => array('registration'),
    ))->add(...);

If you're creating :ref:`form classes <form-creating-form-classes>` (a good
practice), then you'll need to add the following to the ``configureOptions()``
method::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('registration'),
        ));
    }

In both of these cases, *only* the ``registration`` validation group will
be used to validate the underlying object.
