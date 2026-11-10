``validation_groups``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array``, ``string``, ``callable``, :class:`Symfony\\Component\\Validator\\Constraints\\GroupSequence`, or ``null`` **default**: ``null``

This option is only valid on the root form. It specifies which validation groups
will be used by the validator.

If set to ``null``, the validator will use only the ``Default`` group. For the
other possible values, see the main article about
:doc:`using validation groups in Symfony forms </form/validation_groups>`.

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
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'validation_groups' => new GroupSequence(['First', 'Second']),
            ]);
        }
    }

.. seealso::

    Read the article :doc:`/validation/sequence_provider` to find out more about this.
