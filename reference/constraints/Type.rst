Type
====

Validates that a value is of a specific data type. For example, if a variable
should be an array, you can use this constraint with the ``array`` type option
to validate this.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - :ref:`type<reference-constraint-type-type>`                       |
|                | - `message`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Type`           |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\TypeValidator`  |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                age:
                    - Type:
                        type: integer
                        message: The value {{ value }} is not a valid {{ type }}.

    .. code-block:: php-annotations

       // src/Acme/BlogBundle/Entity/Author.php
       namespace Acme\BlogBundle\Entity;
       
       use Symfony\Component\Validator\Constraints as Assert;

       class Author
       {
           /**
            * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
            */
            protected $age;
       }

Options
-------

.. _reference-constraint-type-type:

type
~~~~

**type**: ``string`` [:ref:`default option<validation-default-option>`]

This required option is the fully qualified class name or one of the PHP datatypes
as determined by PHP's ``is_`` functions.

  * `array <http://php.net/is_array>`_
  * `bool <http://php.net/is_bool>`_
  * `callable <http://php.net/is_callable>`_
  * `float <http://php.net/is_float>`_ 
  * `double <http://php.net/is_double>`_
  * `int <http://php.net/is_int>`_ 
  * `integer <http://php.net/is_integer>`_
  * `long <http://php.net/is_long>`_
  * `null <http://php.net/is_null>`_
  * `numeric <http://php.net/is_numeric>`_
  * `object <http://php.net/is_object>`_
  * `real <http://php.net/is_real>`_
  * `resource <http://php.net/is_resource>`_
  * `scalar <http://php.net/is_scalar>`_
  * `string <http://php.net/is_string>`_
  
message
~~~~~~~

**type**: ``string`` **default**: ``This value should be of type {{ type }}``

The message if the underlying data is not of the given type.