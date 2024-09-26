``error_mapping``
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

This option allows you to modify the target of a validation error.

Imagine you have a custom method named ``matchingCityAndZipCode()`` that validates
whether the city and zip code match. Unfortunately, there is no ``matchingCityAndZipCode``
field in your form, so all that Symfony can do is display the error on top
of the form.

With customized error mapping, you can do better: map the error to the city
field so that it displays above it::

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_mapping' => [
                'matchingCityAndZipCode' => 'city',
            ],
        ]);
    }

Here are the rules for the left and the right side of the mapping:

* The left side contains property paths;
* If the violation is generated on a property or method of a class, its
  path is the ``propertyName``;
* If the violation is generated on an entry of an ``array`` or ``ArrayAccess``
  object, the property path is ``[indexName]``;
* You can construct nested property paths by concatenating them, separating
  properties by dots. For example: ``addresses[work].matchingCityAndZipCode``;
* The right side contains the names of fields in the form.

By default, errors for any property that is not mapped will bubble up to the
parent form. You can use the dot (``.``) on the left side to map errors of all
unmapped properties to a particular field. For instance, to map all these
errors to the ``city`` field, use::

    $resolver->setDefaults([
        'error_mapping' => [
            '.' => 'city',
        ],
    ]);
