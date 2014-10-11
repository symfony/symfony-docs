.. index::
   single: Form; Data transformers

How to transform delimited strings to arrays
============================================

It is sometimes necessary to transform a delimited string in a text field to
an array, for example to convert a text field containing ``one, two, three,
four`` to ``array('one', 'two', 'three', 'four')``.

Currently there is no such transformer in the form component but this
transformer is provided by the
[dantleech/symfony-form-array-to-delimited-string-transformer](https://packagist.org/packages/dantleech/symfony-form-array-to-delimited-string-transformer)
package.

To use it simply add the package to composer:

.. code-block:: json

    {
        ...
        "require": {
            ...
            "dantleech/symfony-form-array-to-delimited-string-transformer": "0.1.0",
        }
    }

Then follow the instructions in the library's
[README](https://github.com/dantleech/symfony-form-array-to-delimited-string-transformer)
file.

For more information on using data transformers see the  :doc:`/cookbook/form/data_transformers` cookbook article.
