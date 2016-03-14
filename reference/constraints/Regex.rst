Regex
=====

Validates that a value matches a regular expression.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                |
+----------------+-----------------------------------------------------------------------+
| Options        | - `pattern`_                                                          |
|                | - `htmlPattern`_                                                      |
|                | - `match`_                                                            |
|                | - `message`_                                                          |
|                | - `payload`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Regex`            |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\RegexValidator`   |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have a ``description`` field and you want to verify that it
begins with a valid word character. The regular expression to test for this
would be ``/^\w+/``, indicating that you're looking for at least one or
more word characters at the beginning of your string:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Regex("/^\w+/")
             */
            protected $description;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                description:
                    - Regex: '/^\w+/'

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="description">
                    <constraint name="Regex">
                        <option name="pattern">/^\w+/</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('description', new Assert\Regex(array(
                    'pattern' => '/^\w+/',
                )));
            }
        }

Alternatively, you can set the `match`_ option to ``false`` in order to
assert that a given string does *not* match. In the following example, you'll
assert that the ``firstName`` field does not contain any numbers and give
it a custom message:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Regex(
             *     pattern="/\d/",
             *     match=false,
             *     message="Your name cannot contain a number"
             * )
             */
            protected $firstName;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                firstName:
                    - Regex:
                        pattern: '/\d/'
                        match:   false
                        message: Your name cannot contain a number

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="Regex">
                        <option name="pattern">/\d/</option>
                        <option name="match">false</option>
                        <option name="message">Your name cannot contain a number</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\Regex(array(
                    'pattern' => '/\d/',
                    'match'   => false,
                    'message' => 'Your name cannot contain a number',
                )));
            }
        }

Options
-------

pattern
~~~~~~~

**type**: ``string`` [:ref:`default option <validation-default-option>`]

This required option is the regular expression pattern that the input will
be matched against. By default, this validator will fail if the input string
does *not* match this regular expression (via the :phpfunction:`preg_match`
PHP function). However, if `match`_ is set to false, then validation will
fail if the input string *does* match this pattern.

htmlPattern
~~~~~~~~~~~

**type**: ``string|boolean`` **default**: null

This option specifies the pattern to use in the HTML5 ``pattern`` attribute.
You usually don't need to specify this option because by default, the constraint
will convert the pattern given in the `pattern`_ option into an HTML5 compatible
pattern. This means that the delimiters are removed (e.g. ``/[a-z]+/`` becomes
``[a-z]+``).

However, there are some other incompatibilities between both patterns which
cannot be fixed by the constraint. For instance, the HTML5 ``pattern`` attribute
does not support flags. If you have a pattern like ``/[a-z]+/i``, you
need to specify the HTML5 compatible pattern in the ``htmlPattern`` option:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Regex(
             *     pattern     = "/^[a-z]+$/i",
             *     htmlPattern = "^[a-zA-Z]+$"
             * )
             */
            protected $name;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                name:
                    - Regex:
                        pattern: '/^[a-z]+$/i'
                        htmlPattern: '^[a-zA-Z]+$'

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="name">
                    <constraint name="Regex">
                        <option name="pattern">/^[a-z]+$/i</option>
                        <option name="htmlPattern">^[a-zA-Z]+$</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new Assert\Regex(array(
                    'pattern'     => '/^[a-z]+$/i',
                    'htmlPattern' => '^[a-zA-Z]+$',
                )));
            }
        }

Setting ``htmlPattern`` to false will disable client side validation.

match
~~~~~

**type**: ``boolean`` default: ``true``

If ``true`` (or not set), this validator will pass if the given string matches
the given `pattern`_ regular expression. However, when this option is set
to ``false``, the opposite will occur: validation will pass only if the
given string does **not** match the `pattern`_ regular expression.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not valid.``

This is the message that will be shown if this validator fails.

.. include:: /reference/constraints/_payload-option.rst.inc
