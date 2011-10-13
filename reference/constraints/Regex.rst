Regex
=====

Validates that a value matches a regular expression.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                 |
+----------------+-----------------------------------------------------------------------+
| Options        | - `pattern`_                                                          |
|                | - `match`_                                                            |
|                | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Regex`            |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\RegexValidator`   |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

Suppose you have a ``description`` field and you want to verify that it begins
with a valid word character. The regular expression to test for this would
be ``/^\w+/``, indicating that you're looking for at least one or more word
characters at the beginning of your string:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                description:
                    - Regex: "/^\w+/"

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Regex("/^\w+/")
             */
            protected $description;
        }

Alternatively, you can set the `match`_ option to ``false`` in order to assert
that a given string does *not* match. In the following example, you'll assert
that the ``firstName`` field does not contain any numbers and give it a custom
message:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                firstName:
                    - Regex:
                        pattern: "/\d/"
                        match:   false
                        message: Your name cannot contain a number

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
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

Options
-------

pattern
~~~~~~~

**type**: ``string`` [:ref:`default option<validation-default-option>`]

This required option is the regular expression pattern that the input will
be matched against. By default, this validator will fail if the input string
does *not* match this regular expression (via the `preg_match`_ PHP function).
However, if `match`_ is set to false, then validation will fail if the input
string *does* match this pattern.

match
~~~~~

**type**: ``Boolean`` default: ``true``

If ``true`` (or not set), this validator will pass if the given string matches
the given `pattern`_ regular expression. However, when this option is set
to ``false``, the opposite will occur: validation will pass only if the given
string does **not** match the `pattern`_ regular expression.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not valid``

This is the message that will be shown if this validator fails.

.. _`preg_match`: http://php.net/manual/en/function.preg-match.php
