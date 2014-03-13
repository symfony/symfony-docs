Type
====

Validates that a value is of a specific data type. For example, if a variable
should be an array, you can use this constraint with the ``array`` type option
to validate this.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - :ref:`type <reference-constraint-type-type>`                      |
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

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <property name="age">
                    <constraint name="Type">
                        <option name="type">integer</option>
                        <option name="message">The value {{ value }} is not a valid {{ type }}.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php
        
        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\Type(array(
                    'type'    => 'integer',
                    'message' => 'The value {{ value }} is not a valid {{ type }}.',
                )));
            }
        }

Options
-------

.. _reference-constraint-type-type:

type
~~~~

**type**: ``string`` [:ref:`default option <validation-default-option>`]

This required option is the fully qualified class name or one of the PHP datatypes
as determined by PHP's ``is_`` functions.

* :phpfunction:`array <is_array>`
* :phpfunction:`bool <is_bool>`
* :phpfunction:`callable <is_callable>`
* :phpfunction:`float <is_float>`
* :phpfunction:`double <is_double>`
* :phpfunction:`int <is_int>`
* :phpfunction:`integer <is_integer>`
* :phpfunction:`long <is_long>`
* :phpfunction:`null <is_null>`
* :phpfunction:`numeric <is_numeric>`
* :phpfunction:`object <is_object>`
* :phpfunction:`real <is_real>`
* :phpfunction:`resource <is_resource>`
* :phpfunction:`scalar <is_scalar>`
* :phpfunction:`string <is_string>`

Also, you can use ``ctype_`` functions from corresponding `built-in PHP extension <http://php.net/book.ctype.php>`_.
Consider `a list of ctype functions <http://php.net/ref.ctype.php>`_:

* :phpfunction:`alnum <ctype_alnum>`
* :phpfunction:`alpha <ctype_alpha>`
* :phpfunction:`cntrl <ctype_cntrl>`
* :phpfunction:`digit <ctype_digit>`
* :phpfunction:`graph <ctype_graph>`
* :phpfunction:`lower <ctype_lower>`
* :phpfunction:`print <ctype_print>`
* :phpfunction:`punct <ctype_punct>`
* :phpfunction:`space <ctype_space>`
* :phpfunction:`upper <ctype_upper>`
* :phpfunction:`xdigit <ctype_xdigit>`

Make sure that the proper :phpfunction:`locale <setlocale>` is set before using one of these.

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be of type {{ type }}.``

The message if the underlying data is not of the given type.
