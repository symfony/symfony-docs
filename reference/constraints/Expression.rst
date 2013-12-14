Expression
==========

.. versionadded:: 2.4
    The Expression constraint was introduced in Symfony 2.4.

This constraint allows you to use an :ref:`expression <component-expression-language-examples>`
for more complex, dynamic validation. See `Basic Usage`_ for an example.
See :doc:`/reference/constraints/Callback` for a different constraint that
gives you similar flexibility.

+----------------+-----------------------------------------------------------------------------------------------+
| Applies to     | :ref:`class <validation-class-target>` or :ref:`property/method <validation-property-target>` |
+----------------+-----------------------------------------------------------------------------------------------+
| Options        | - :ref:`expression <reference-constraint-expression-option>`                                  |
|                | - `message`_                                                                                  |
+----------------+-----------------------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Expression`                               |
+----------------+-----------------------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionValidator`                      |
+----------------+-----------------------------------------------------------------------------------------------+

Basic Usage
-----------

Imagine you have a class ``BlogPost`` with ``category`` and ``isTechnicalPost``
properties::

    namespace Acme\DemoBundle\Model;

    use Symfony\Component\Validator\Constraints as Assert;

    class BlogPost
    {
        private $category;

        private $isTechnicalPost;

        // ...

        public function getCategory()
        {
            return $this->category;
        }

        public function setIsTechnicalPost($isTechnicalPost)
        {
            $this->isTechnicalPost = $isTechnicalPost;
        }

        // ...
    }

To validate the object, you have some special requirements:

* A) If ``isTechnicalPost`` is true, then ``category`` must be either ``php``
  or ``symfony``;

* B) If ``isTechnicalPost`` is false, then ``category`` can be anything.

One way to accomplish this is with the Expression constraint:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/validation.yml
        Acme\DemoBundle\Model\BlogPost:
            constraints:
                - Expression:
                    expression: "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()"
                    message: "If this is a tech post, the category should be either php or symfony!"

    .. code-block:: php-annotations

        // src/Acme/DemoBundle/Model/BlogPost.php
        namespace Acme\DemoBundle\Model\BlogPost;
        
        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @Assert\Expression(
         *     "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()",
         *     message="If this is a tech post, the category should be either php or symfony!"
         * )
         */
        class BlogPost
        {
            // ...
        }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/validation.xml -->
        <class name="Acme\DemoBundle\Model\BlogPost">
            <constraint name="Expression">
                <option name="expression">
                    this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()
                </option>
                <option name="message">
                    If this is a tech post, the category should be either php or symfony!
                </option>
            </constraint>
        </class>


    .. code-block:: php

        // src/Acme/DemoBundle/Model/BlogPost.php
        namespace Acme\DemoBundle\Model\BlogPost;
        
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class BlogPost
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Expression(array(
                    'expression' => 'this.getCategory() in ["php", "symfony"] or !this.isTechnicalPost()',
                    'message' => 'If this is a tech post, the category should be either php or symfony!',
                )));
            }

            // ...
        }

The :ref:`expression <reference-constraint-expression-option>` option is the
expression that must return true in order for validation to pass. To learn
more about the expression language syntax, see
:doc:`/components/expression_language/syntax`.

For more information about the expression and what variables are available
to you, see the :ref:`expression <reference-constraint-expression-option>`
option details below.

Available Options
-----------------

.. _reference-constraint-expression-option:

expression
~~~~~~~~~~

**type**: ``string`` [:ref:`default option <validation-default-option>`]

The expression that will be evaluated. If the expression evaluates to a false
value (using ``==``, not ``===``), validation will fail.

To learn more about the expression language syntax, see
:doc:`/components/expression_language/syntax`.

Inside of the expression, you have access to up to 2 variables:

Depending on how you use the constraint, you have access to 1 or 2 variables
in your expression:

* ``this``: The object being validated (e.g. an instance of BlogPost);
* ``value``: The value of the property being validated (only available when
  the constraint is applied directly to a property);

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not valid.``

The default message supplied when the expression evaluates to false.
