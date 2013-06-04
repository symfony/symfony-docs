error_mapping
~~~~~~~~~~~~~

**type**: ``array`` **default**: ``empty``

This option permits to modify the default target of errors.

Imagine a custom method named ``matchingCityAndZipCode`` validates whether the city and zip code matches.
Unfortunately, there is no "matchingCityAndZipCode" field in your form, 
so all that Symfony can do for you is display the error on top of the form. 

With customized error mapping, you can do better: make the error be mapped to the city field.

Here are to understand the left and the right side of the mapping:

 * The left side contains property paths. 
 * If the violation is generated on a property or method of a class, its path is simply propertyName. 
 * If the violation is generated on an entry of an array or ArrayAccess object, the property path is [indexName]. 
 * You can construct nested property paths by concatenating them, separating properties by dots, 
   * for example: addresses[work].matchingCityAndZipCode
 *The right side contains simply the names of fields in the form.
 * The left side of the error mapping also accepts a dot ., which refers to the field itself. 
 That means that any error added to the field is added to the given nested field instead.
