AssertType
==========

Validates that a value has a specific data type

    [yml]
    properties:
      age:
        - AssertType: integer
    
Options
-------

  * `type` (**default**, required): A fully qualified class name or one of the 
    PHP datatypes as determined by PHP's `is_` functions.
    
      * [`array`](http://php.net/is_array)
      * [`bool`](http://php.net/is_bool)
      * [`callable`](http://php.net/is_callable)
      * [`float`](http://php.net/is_float) 
      * [`double`](http://php.net/is_double)
      * [`int`](http://php.net/is_int) 
      * [`integer`](http://php.net/is_integer)
      * [`long`](http://php.net/is_long)
      * [`null`](http://php.net/is_null)
      * [`numeric`](http://php.net/is_numeric)
      * [`object`](http://php.net/is_object)
      * [`real`](http://php.net/is_real)
      * [`resource`](http://php.net/is_resource)
      * [`scalar`](http://php.net/is_scalar)
      * [`string`](http://php.net/is_string)
      
  * `message`: The error message in case the validation fails
