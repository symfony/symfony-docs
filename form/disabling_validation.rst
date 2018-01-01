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
fields were submitted.

Note that to disable the extra fields check, you can use the proper
`form type option`_.
One the other hand, the uploaded file limit should be handled via your php and
web server configuration.

.. _`form type option`: http://symfony.com/doc/current/reference/forms/types/form.html#allow-extra-fields
