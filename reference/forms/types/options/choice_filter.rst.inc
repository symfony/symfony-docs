``choice_filter``
~~~~~~~~~~~~~~~~~

**type**: ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

When using predefined choice types from Symfony core or vendor libraries (i.e.
:doc:`CountryType </reference/forms/types/country>`) this option lets you
define a callable that takes each choice as the only argument and must return
``true`` to keep it or ``false`` to discard it::

    // src/Form/Type/AddressType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\CountryType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AddressType extends AbstractType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver
                ->setDefaults([
                    // enable this type to accept a limited set of countries
                    'allowed_countries' => null,
                ])
            ;
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $allowedCountries = $options['allowed_countries'];

            $builder
                // ...
                ->add('country', CountryType::class, [
                    // if the AddressType "allowed_countries" option is passed,
                    // use it to create a filter
                    'choice_filter' => $allowedCountries ? function ($countryCode) use ($allowedCountries): bool {
                        return in_array($countryCode, $allowedCountries, true);
                    } : null,

                ])
            ;
        }

The option can be a callable or a property path when choices are objects::

    // ...
    $builder
        ->add('category', ChoiceType::class, [
            // ...
            'choice_filter' => 'isSelectable',
        ])
    ;

.. tip::

    Considering this ``AddressType`` could be an entry of a ``CollectionType``
    you should use the :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList`
    class helper to enable caching::

        // src/Form/Type/AddressType.php
        // ...
        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        'choice_filter' => $allowedCountries ? ChoiceList::filter(
            // pass the type as first argument
            $this,
            function (string $countryCode) use ($allowedCountries): bool {
                return in_array($countryCode, $allowedCountries, true);
            },
            // pass the option that makes the filter "vary" to compute a unique hash
            $allowedCountries
        ) : null,
        // ...
