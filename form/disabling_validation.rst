How to Disable the Validation of Submitted Data
===============================================

Sometimes it is useful to suppress the validation of a form altogether. For
these cases you can set the ``validation_groups`` option to ``false``::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }

Note that when you do that, the form will still run basic integrity checks,
for example whether an uploaded file was too large or whether non-existing
fields were submitted.

The submission of extra form fields can be controlled with the
:ref:`allow_extra_fields config option <form-option-allow-extra-fields>` and
the maximum upload file size should be handled via your PHP and web server
configuration.
