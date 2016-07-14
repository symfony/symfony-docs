.. index::
    single: Forms; Disabling validation

How to Disable the Validation of Submitted Data
===============================================

.. versionadded:: 2.3
    The ability to set ``validation_groups`` to false was introduced in Symfony 2.3.

Sometimes it is useful to suppress the validation of a form altogether. For
these cases you can set the ``validation_groups`` option to ``false``::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => false,
        ));
    }

Note that when you do that, the form will still run basic integrity checks,
for example whether an uploaded file was too large or whether non-existing
fields were submitted. If you want to suppress validation, you can use the
:ref:`POST_SUBMIT event <cookbook-dynamic-form-modification-suppressing-form-validation>`.
