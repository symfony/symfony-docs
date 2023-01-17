.. index::
    single: Forms; Validation groups

How to Define the Validation Groups to Use
==========================================

Validation Groups
-----------------

If your object takes advantage of :doc:`validation groups </validation/groups>`,
you will need to specify which validation group(s) your form should use. Pass
this as an option when :ref:`creating forms in controllers <creating-forms-in-controllers>`::

    $form = $this->createFormBuilder($user, [
        'validation_groups' => ['registration'],
    ])->add(/* ... */);

When :ref:`creating forms in classes <creating-forms-in-classes>`, add the
following to the ``configureOptions()`` method::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // ...
            'validation_groups' => ['registration'],
        ]);
    }

In both of these cases, *only* the ``registration`` validation group will
be used to validate the underlying object. To apply the ``registration``
group *and* all constraints that are not in a group, use::

    'validation_groups' => ['Default', 'registration']

.. note::

    You can choose any name for your validation groups, but Symfony recommends
    using "lower snake case" names (e.g. ``foo_bar``) in contrast with the
    automatic validation groups created by Symfony, which use "upper camel case"
    (e.g. ``Default``, ``SomeClassName``).
