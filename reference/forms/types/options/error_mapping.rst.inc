error_mapping
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

This option allows you to modify the target of a validation error.

Imagine you have a custom method named ``matchingCityAndZipCode`` that validates
whether the city and zip code match. Unfortunately, there is no "matchingCityAndZipCode"
field in your form, so all that Symfony can do is display the error on top
of the form.

With customized error mapping, you can do better: map the error to the city
field so that it displays above it::

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'error_mapping' => array(
                'matchingCityAndZipCode' => 'city',
            ),
        ));
    }

Here are the rules for the left and the right side of the mapping:

* The left side contains property paths;
* If the violation is generated on a property or method of a class, its
  path is simply ``propertyName``;
* If the violation is generated on an entry of an ``array`` or ``ArrayAccess``
  object, the property path is ``[indexName]``;
* You can construct nested property paths by concatenating them, separating
  properties by dots. For example: ``addresses[work].matchingCityAndZipCode``;
* The left side of the error mapping also accepts a dot ``.``, which refers
  to the field itself. That means that any error added to the field is added
  to the given nested field instead;
* The right side contains simply the names of fields in the form.
